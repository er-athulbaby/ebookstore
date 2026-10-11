<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayService
{
    private Api $api;

    public function __construct()
    {
        $this->api = new Api(Config::get('services.razorpay.key'), Config::get('services.razorpay.secret'));
    }

    public function createOrderFor(Order $order): Payment
    {
        $razorpayOrder = $this->api->order->create([
            'receipt' => $order->order_number,
            'amount' => $order->total, // already in paise
            'currency' => 'INR',
            'notes' => ['order_number' => $order->order_number, 'order_id' => (string) $order->id],
        ]);

        return $order->payments()->create([
            'provider' => 'razorpay',
            'provider_order_id' => $razorpayOrder['id'],
            'amount' => $order->total,
            'currency' => 'INR',
            'status' => 'created',
            'raw_payload' => $razorpayOrder->toArray(),
        ]);
    }

    public function verifySignature(array $attributes): bool
    {
        try {
            $this->api->utility->verifyPaymentSignature($attributes);

            return true;
        } catch (SignatureVerificationError $e) {
            Log::warning('Razorpay signature verification failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = Config::get('services.razorpay.webhook_secret');

        if (! $secret) {
            return false;
        }

        try {
            $this->api->utility->verifyWebhookSignature($payload, $signature, $secret);

            return true;
        } catch (SignatureVerificationError $e) {
            Log::warning('Razorpay webhook signature verification failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function keyId(): string
    {
        return (string) Config::get('services.razorpay.key');
    }
}
