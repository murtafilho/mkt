<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

class ProcessMercadoPagoWebhook implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $paymentId,
        public string $type,
        public string $action
    ) {
        $this->onQueue('webhooks');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::channel('stack')->info('Processing Mercado Pago webhook', [
                'payment_id' => $this->paymentId,
                'type' => $this->type,
                'action' => $this->action,
                'attempt' => $this->attempts(),
            ]);

            // Configure Mercado Pago SDK
            MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));

            // Get payment details from Mercado Pago API
            $client = new PaymentClient;
            $mpPayment = $client->get((int) $this->paymentId);

            // Get order from external_reference
            $orderId = $mpPayment->external_reference;

            if (! $orderId) {
                Log::channel('stack')->warning('No external reference found in payment', [
                    'payment_id' => $this->paymentId,
                ]);

                return;
            }

            $order = Order::find($orderId);

            if (! $order) {
                Log::channel('stack')->error('Order not found for webhook', [
                    'order_id' => $orderId,
                    'payment_id' => $this->paymentId,
                ]);

                return;
            }

            // Process payment update
            $this->updatePayment($order, $mpPayment);

            Log::channel('stack')->info('Webhook processed successfully', [
                'payment_id' => $this->paymentId,
                'order_id' => $order->id,
                'status' => $mpPayment->status,
            ]);
        } catch (\Exception $e) {
            Log::channel('stack')->error('Webhook processing failed', [
                'payment_id' => $this->paymentId,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Update or create payment record.
     */
    private function updatePayment(Order $order, object $mpPayment): void
    {
        DB::transaction(function () use ($order, $mpPayment) {
            // Get or create payment record
            $payment = Payment::where('order_id', $order->id)
                ->where('external_payment_id', $this->paymentId)
                ->first();

            if (! $payment) {
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => $mpPayment->payment_method_id ?? 'unknown',
                    'amount' => $mpPayment->transaction_amount,
                    'status' => 'pending',
                    'external_payment_id' => $this->paymentId,
                    'metadata' => $mpPayment,
                ]);
            }

            // Map Mercado Pago status
            $status = $this->mapPaymentStatus($mpPayment->status);

            // Update payment with detailed information
            $payment->update([
                'status' => $status,
                'payment_method' => $mpPayment->payment_method_id ?? 'unknown',
                'paid_at' => $status === 'approved' ? now() : null,
                'metadata' => $mpPayment,
            ]);

            // Update order based on payment status
            if ($status === 'approved' && $order->status === 'pending') {
                $this->handleApprovedPayment($order);
            } elseif (in_array($status, ['rejected', 'cancelled']) && $order->status === 'pending') {
                $this->handleRejectedPayment($order);
            }
        });
    }

    /**
     * Handle approved payment.
     */
    private function handleApprovedPayment(Order $order): void
    {
        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        /** @var \App\Models\User $customer */
        $customer = $order->user;

        /** @var \App\Models\Seller $seller */
        $seller = $order->seller;

        /** @var \App\Models\User $sellerUser */
        $sellerUser = $seller->user;

        // Send confirmation email to customer
        \Illuminate\Support\Facades\Mail::to($customer->email)
            ->send(new \App\Mail\OrderConfirmed($order));

        // Send notification email to seller
        \Illuminate\Support\Facades\Mail::to($sellerUser->email)
            ->send(new \App\Mail\NewOrderNotification($order));

        Log::channel('stack')->info('Order payment approved, emails dispatched', [
            'order_id' => $order->id,
            'payment_id' => $this->paymentId,
            'customer_email' => $customer->email,
            'seller_email' => $sellerUser->email,
        ]);
    }

    /**
     * Handle rejected payment.
     */
    private function handleRejectedPayment(Order $order): void
    {
        $order->update(['status' => 'cancelled']);

        // Restore stock
        foreach ($order->items as $item) {
            /** @var \App\Models\OrderItem $item */
            /** @phpstan-ignore if.alwaysTrue */
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        // TODO: Dispatch PaymentFailed email job

        Log::channel('stack')->info('Order payment rejected, stock restored', [
            'order_id' => $order->id,
            'payment_id' => $this->paymentId,
        ]);
    }

    /**
     * Map Mercado Pago payment status to our status.
     */
    private function mapPaymentStatus(string $mpStatus): string
    {
        return match ($mpStatus) {
            'approved' => 'approved',
            'pending', 'in_process', 'in_mediation', 'authorized' => 'pending',
            'rejected', 'cancelled' => 'rejected',
            'refunded', 'charged_back' => 'refunded',
            default => 'pending',
        };
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('stack')->error('Webhook job failed permanently', [
            'payment_id' => $this->paymentId,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
        ]);

        // TODO: Alert admin via notification
    }
}
