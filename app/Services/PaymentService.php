<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

class PaymentService
{
    public function __construct()
    {
        // Configure Mercado Pago SDK
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
    }

    /**
     * Create Mercado Pago payment preference for Checkout Bricks.
     */
    public function createPreference(Order $order): array
    {
        try {
            /** @var \App\Models\User $user */
            $user = $order->user;
            /** @var \App\Models\OrderAddress|null $address */
            $address = $order->address;

            // Prepare items array
            $items = $order->items->map(function ($item) {
                return [
                    'id' => $item->sku,
                    'title' => $item->product_name,
                    'description' => $item->product->description ?? '',
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'currency_id' => 'BRL',
                ];
            })->toArray();

            // Add shipping as separate item if applicable
            if ($order->shipping_fee > 0) {
                $items[] = [
                    'title' => 'Frete',
                    'quantity' => 1,
                    'unit_price' => (float) $order->shipping_fee,
                    'currency_id' => 'BRL',
                ];
            }

            // Prepare preference data
            $preferenceData = [
                'items' => $items,
                'payer' => [
                    'name' => $user->getFirstName(),
                    'surname' => $user->getLastName(),
                    'email' => $user->email,
                ],
                'external_reference' => (string) $order->id,
                'notification_url' => config('services.mercadopago.webhook_url'),
                'statement_descriptor' => config('app.name'),
            ];

            // Add payer phone if available
            if ($user->phone) {
                $preferenceData['payer']['phone'] = [
                    'area_code' => $user->getAreaCode(),
                    'number' => $user->getPhoneNumber(),
                ];
            }

            // Add payer identification if available
            if ($user->cpf_cnpj) {
                $preferenceData['payer']['identification'] = [
                    'type' => strlen($user->cpf_cnpj) === 11 ? 'CPF' : 'CNPJ',
                    'number' => $user->cpf_cnpj,
                ];
            }

            // Add payer address if available
            if ($address) {
                $preferenceData['payer']['address'] = [
                    'zip_code' => $address->postal_code,
                    'street_name' => $address->street,
                    'street_number' => (string) $address->number,
                ];

                // Note: city, state, neighborhood are optional in Preference API
                // The SDK has strict typing issues with these fields
            }

            // Create preference using SDK
            $client = new PreferenceClient;
            $preference = $client->create($preferenceData);

            // Store payment record
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'mercadopago',
                'amount' => $order->total,
                'status' => 'pending',
                'external_payment_id' => $preference->id,
                'metadata' => $preference,
            ]);

            return [
                'preference_id' => $preference->id,
                'init_point' => $preference->init_point,
                'sandbox_init_point' => $preference->sandbox_init_point ?? null,
            ];
        } catch (MPApiException $e) {
            Log::error('Mercado Pago API error', [
                'order_id' => $order->id,
                'status_code' => $e->getApiResponse()->getStatusCode(),
                'content' => $e->getApiResponse()->getContent(),
            ]);

            throw new \Exception('Erro ao criar preferência de pagamento: '.$e->getMessage());
        } catch (\Exception $e) {
            Log::error('Payment preference creation error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Process payment from Payment Brick (PIX, credit card, debit card, boleto).
     *
     * @param  array  $paymentData  Payment form data from Payment Brick
     * @param  string  $paymentMethod  Payment method selected (e.g., 'bank_transfer', 'credit_card')
     * @return array{payment_id: string, status: string, qr_code?: string, qr_code_base64?: string, ticket_url?: string}
     */
    public function createPayment(Order $order, array $paymentData, string $paymentMethod): array
    {
        try {
            Log::info('PaymentService::createPayment - Starting', [
                'order_id' => $order->id,
                'payment_method' => $paymentMethod,
                'payment_data_keys' => array_keys($paymentData),
            ]);

            /** @var \App\Models\User $user */
            $user = $order->user;

            // Prepare payment request for Payment API
            $paymentRequest = [
                'transaction_amount' => (float) $order->total,
                'description' => "Pedido #{$order->id} - ".config('app.name'),
                'payment_method_id' => $paymentData['payment_method_id'] ?? null,
                'payer' => [
                    'email' => $paymentData['payer']['email'] ?? $user->email,
                    'first_name' => $user->getFirstName(),
                    'last_name' => $user->getLastName(),
                ],
                'external_reference' => (string) $order->id,
                // notification_url removido temporariamente (localhost não é acessível pelo MP)
                // 'notification_url' => config('services.mercadopago.webhook_url'),
            ];

            // Add identification (CPF/CNPJ) - OBRIGATÓRIO para PIX
            if ($user->cpf_cnpj) {
                // Remove formatting (keep only numbers)
                $cleanCpfCnpj = preg_replace('/\D/', '', $user->cpf_cnpj);

                $paymentRequest['payer']['identification'] = [
                    'type' => strlen($cleanCpfCnpj) === 11 ? 'CPF' : 'CNPJ',
                    'number' => $cleanCpfCnpj,
                ];

                // Add entity_type for better MP compatibility
                $paymentRequest['payer']['entity_type'] = 'individual';
            } else {
                // Se não tiver CPF/CNPJ, gerar erro explicativo
                throw new \Exception('CPF/CNPJ do usuário é obrigatório para pagamentos PIX. Por favor, atualize seu perfil.');
            }

            Log::info('PaymentService::createPayment - Payment request prepared', [
                'payment_request' => $paymentRequest,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_cpf_cnpj' => $user->cpf_cnpj,
            ]);

            // Add token for credit/debit card payments
            if (isset($paymentData['token'])) {
                $paymentRequest['token'] = $paymentData['token'];
            }

            // Add issuer for credit card
            if (isset($paymentData['issuer_id'])) {
                $paymentRequest['issuer_id'] = $paymentData['issuer_id'];
            }

            // Add installments for credit card
            if (isset($paymentData['installments'])) {
                $paymentRequest['installments'] = (int) $paymentData['installments'];
            }

            // Create payment using SDK
            Log::info('PaymentService::createPayment - Creating payment via SDK');
            $client = new \MercadoPago\Client\Payment\PaymentClient;
            $payment = $client->create($paymentRequest);

            Log::info('PaymentService::createPayment - Payment created successfully', [
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'status_detail' => $payment->status_detail ?? null,
            ]);

            // Store payment record
            $paymentRecord = Payment::create([
                'order_id' => $order->id,
                'payment_method' => $paymentMethod,
                'amount' => $order->total,
                'status' => $payment->status,
                'external_payment_id' => (string) $payment->id,
                'metadata' => $payment,
            ]);

            Log::info('PaymentService::createPayment - Payment record stored', [
                'payment_record_id' => $paymentRecord->id,
            ]);

            // Prepare response based on payment method
            $response = [
                'payment_id' => (string) $payment->id,
                'status' => $payment->status,
                'status_detail' => $payment->status_detail ?? null,
            ];

            // PIX: Include QR Code data
            if ($paymentMethod === 'bank_transfer' && isset($payment->point_of_interaction->transaction_data)) {
                $response['qr_code'] = $payment->point_of_interaction->transaction_data->qr_code ?? null;
                $response['qr_code_base64'] = $payment->point_of_interaction->transaction_data->qr_code_base64 ?? null;
                $response['ticket_url'] = $payment->point_of_interaction->transaction_data->ticket_url ?? null;
            }

            // Boleto: Include ticket URL
            if ($paymentMethod === 'ticket' && isset($payment->transaction_details->external_resource_url)) {
                $response['ticket_url'] = $payment->transaction_details->external_resource_url;
            }

            // Credit/Debit Card: Include redirect URL if 3DS required
            if (in_array($paymentMethod, ['credit_card', 'debit_card']) && isset($payment->point_of_interaction->redirect_url)) {
                $response['redirect_url'] = $payment->point_of_interaction->redirect_url;
            }

            return $response;
        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            $errorContent = $e->getApiResponse()->getContent();

            Log::error('Mercado Pago Payment API error', [
                'order_id' => $order->id,
                'status_code' => $e->getApiResponse()->getStatusCode(),
                'content' => $errorContent,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Extract meaningful error message from MP API response
            $errorMessage = 'Erro ao processar pagamento';
            if (isset($errorContent['message'])) {
                $errorMessage .= ': '.$errorContent['message'];
            } else {
                $errorMessage .= ': '.$e->getMessage();
            }

            throw new \Exception($errorMessage);
        } catch (\Exception $e) {
            Log::error('Payment processing error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \Exception('Erro ao processar pagamento: '.$e->getMessage());
        }
    }

    /**
     * Get payment status for order.
     */
    public function getPaymentStatus(Order $order): ?Payment
    {
        return Payment::where('order_id', $order->id)->first();
    }
}
