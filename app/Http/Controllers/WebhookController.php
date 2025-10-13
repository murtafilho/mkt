<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\MercadoPagoConfig;

class WebhookController extends Controller
{
    public function __construct()
    {
        // Configure Mercado Pago SDK
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
    }

    /**
     * Handle Mercado Pago webhook notifications.
     */
    public function mercadopago(Request $request): JsonResponse
    {
        try {
            // Get signature headers
            $xSignature = $request->header('x-signature');
            $xRequestId = $request->header('x-request-id');

            Log::info('Mercado Pago webhook received', [
                'x-signature' => $xSignature,
                'x-request-id' => $xRequestId,
                'payload' => $request->all(),
            ]);

            // Validate webhook signature (if configured)
            if (config('services.mercadopago.webhook_secret')) {
                if (! $this->validateWebhookSignature($request)) {
                    Log::warning('Invalid webhook signature', [
                        'x-signature' => $xSignature,
                        'x-request-id' => $xRequestId,
                    ]);

                    return response()->json(['status' => 'invalid_signature'], 401);
                }
            }

            // Get notification data
            $data = $request->all();
            $action = $data['action'] ?? null;
            $type = $data['type'] ?? null;

            // Handle payment notification - dispatch to queue
            if ($type === 'payment') {
                $paymentId = $data['data']['id'] ?? null;

                if ($paymentId) {
                    // Dispatch job to queue for async processing
                    \App\Jobs\ProcessMercadoPagoWebhook::dispatch(
                        (string) $paymentId,
                        $type,
                        $action ?? 'unknown'
                    );

                    Log::info('Webhook job dispatched to queue', [
                        'payment_id' => $paymentId,
                        'type' => $type,
                        'action' => $action,
                    ]);
                }
            }

            // Always return 200 OK to Mercado Pago
            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error('Webhook dispatch error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Still return 200 to prevent retries on unrecoverable errors
            return response()->json(['status' => 'error'], 200);
        }
    }

    /**
     * Validate Mercado Pago webhook signature.
     */
    private function validateWebhookSignature(Request $request): bool
    {
        try {
            $xSignature = $request->header('x-signature');
            $xRequestId = $request->header('x-request-id');

            if (! $xSignature || ! $xRequestId) {
                return false;
            }

            // Parse signature header (format: "ts=1234567890,v1=hash")
            $signatureParts = [];
            foreach (explode(',', $xSignature) as $part) {
                [$key, $value] = explode('=', $part, 2);
                $signatureParts[$key] = $value;
            }

            $timestamp = $signatureParts['ts'] ?? null;
            $hash = $signatureParts['v1'] ?? null;

            if (! $timestamp || ! $hash) {
                return false;
            }

            // Build manifest (data_id + request_id + timestamp)
            $dataId = $request->input('data.id');
            $manifest = "{$dataId}{$xRequestId}{$timestamp}";

            // Calculate expected signature
            $secret = config('services.mercadopago.webhook_secret');
            $expectedHash = hash_hmac('sha256', $manifest, $secret);

            // Compare signatures (timing-safe)
            return hash_equals($expectedHash, $hash);
        } catch (\Exception $e) {
            Log::error('Signature validation error', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
