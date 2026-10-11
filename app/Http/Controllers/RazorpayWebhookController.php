<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\OrderService;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Belt-and-braces confirmation path: the client-side verify() call in
 * CheckoutController normally fulfils the order, but if the customer closes
 * the tab right after paying, this webhook (configured in the Razorpay
 * dashboard to point at /webhooks/razorpay) still marks it paid.
 * OrderService::markPaid() is idempotent, so it's safe for both to fire.
 */
class RazorpayWebhookController extends Controller
{
    public function __invoke(Request $request, RazorpayService $razorpay, OrderService $orderService)
    {
        $signature = $request->header('X-Razorpay-Signature', '');
        $payload = $request->getContent();

        if (! $razorpay->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Rejected Razorpay webhook with invalid signature.');

            return response('Invalid signature', 400);
        }

        $event = $request->input('event');
        $entity = $request->input('payload.payment.entity', []);
        $providerOrderId = $entity['order_id'] ?? null;

        if (! $providerOrderId) {
            return response('ok');
        }

        $payment = Payment::where('provider_order_id', $providerOrderId)->first();

        if (! $payment) {
            return response('ok');
        }

        if ($event === 'payment.captured') {
            $payment->update([
                'provider_payment_id' => $entity['id'] ?? $payment->provider_payment_id,
                'status' => 'captured',
                'raw_payload' => $entity,
            ]);
            $orderService->markPaid($payment->order);
        } elseif ($event === 'payment.failed') {
            $payment->update(['status' => 'failed', 'raw_payload' => $entity]);
            $orderService->markFailed($payment->order);
        }

        return response('ok');
    }
}
