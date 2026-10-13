<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\GiftVoucher;
use App\Support\Shipping;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly OrderService $orderService,
    ) {}

    public function index()
    {
        $cart = $this->cartService->current();
        $cart->load('items.variant.book', 'items.bundle.variants');

        abort_if($cart->items->isEmpty(), 422, 'Your cart is empty.');

        $coupon = $this->appliedCoupon();
        $discount = $coupon ? min($coupon->discountFor($cart->subtotal()), $cart->subtotal()) : 0;
        $net = $cart->subtotal() - $discount;
        // Same numbers OrderService will charge, so what the customer sees is what they pay.
        $shipping = Shipping::for($net, $cart->hasPhysicalItems());
        $total = $net + $shipping;
        $hasGiftCard = $cart->hasGiftCardItems();

        return view('checkout.index', [
            'cart' => $cart,
            'addresses' => Auth::user()->addresses,
            'coupon' => $coupon,
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => $total,
            'requiresAddress' => $cart->hasPhysicalItems(),
            'allowCod' => (config('redcherry.cod_for_ebooks') || ! $cart->hasDigitalItems()) && ! $hasGiftCard,
            'walletBalance' => Auth::user()->wallet_balance,
        ]);
    }

    public function store(Request $request, RazorpayService $razorpay)
    {
        $cart = $this->cartService->current();
        $cart->load('items.variant.book', 'items.bundle.variants');

        $data = $request->validate([
            'shipping_address_id' => [\Illuminate\Validation\Rule::requiredIf($cart->hasPhysicalItems()), 'nullable', 'exists:addresses,id'],
            'payment_method' => ['nullable', 'in:razorpay,cod'], // not needed when nothing is left to pay
            'use_wallet' => ['nullable', 'boolean'],
        ], [
            'shipping_address_id.required' => 'Please add a shipping address before placing the order.',
        ]);

        if (! empty($data['shipping_address_id'])) {
            abort_unless(
                Auth::user()->addresses()->whereKey($data['shipping_address_id'])->exists(),
                403
            );
        }

        // Re-validate the applied coupon against the cart's current state
        // rather than trusting what was true when it was applied — it may
        // have since expired, hit its use limit, or the cart may have changed.
        $coupon = $this->appliedCoupon();
        if ($coupon && ! $coupon->isValidFor($cart->subtotal())) {
            Session::forget('applied_coupon_id');
            $coupon = null;
        }

        $order = $this->orderService->createFromCart(
            $cart,
            Auth::user(),
            $data['shipping_address_id'] ?? null,
            $data['payment_method'] ?? null,
            $coupon,
            (bool) ($data['use_wallet'] ?? false),
        );

        if ($order->payment_method === 'free') {
            $this->orderService->placeWithoutGateway($order);
            $this->clearCartAndPromotions($cart);

            return redirect()->route('order.confirmation', $order->order_number)
                ->with('status', $order->voucher_amount > 0 ? 'Order placed! Paid from your wallet.' : 'Order placed!');
        }

        if ($order->payment_method === 'cod') {
            $order->update(['status' => 'processing']);
            $order->payments()->create([
                'provider' => 'cod',
                'amount' => $order->amountDue(),
                'currency' => 'INR',
                'status' => 'created',
            ]);
            $this->orderService->commitCodOrder($order);
            $this->clearCartAndPromotions($cart);

            return redirect()->route('order.confirmation', $order->order_number)
                ->with('status', 'Order placed! Pay cash on delivery.');
        }

        $payment = $razorpay->createOrderFor($order);

        return view('checkout.razorpay', [
            'order' => $order,
            'payment' => $payment,
            'razorpayKey' => $razorpay->keyId(),
        ]);
    }

    public function verify(Request $request, RazorpayService $razorpay)
    {
        $data = $request->validate([
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $payment = \App\Models\Payment::where('provider_order_id', $data['razorpay_order_id'])->firstOrFail();
        $order = $payment->order;
        abort_unless($order->user_id === Auth::id(), 403);

        if (! $razorpay->verifySignature($data)) {
            $this->orderService->markFailed($order);

            return response()->json(['message' => 'Payment verification failed.'], 422);
        }

        $payment->update([
            'provider_payment_id' => $data['razorpay_payment_id'],
            'provider_signature' => $data['razorpay_signature'],
            'status' => 'captured',
        ]);

        $this->orderService->markPaid($order);
        $this->clearCartAndPromotions($this->cartService->current());

        return response()->json(['redirect' => route('order.confirmation', $order->order_number)]);
    }

    /** "Have a gift card?" at checkout: the code goes into the wallet, which checkout then uses. */
    public function redeemGiftCard(Request $request)
    {
        $data = $request->validate(['gift_code' => ['required', 'string', 'max:30']]);
        $voucher = GiftVoucher::redeemFor($data['gift_code'], Auth::user());

        if (! $voucher) {
            return back()->withErrors(['gift_code' => 'That gift card code is invalid, expired, or already used.'])->withInput();
        }

        return back()->with('status', money($voucher->value).' added to your wallet — it will be used for this order.');
    }

    private function appliedCoupon(): ?Coupon
    {
        $id = Session::get('applied_coupon_id');

        return $id ? Coupon::find($id) : null;
    }

    private function clearCartAndPromotions(\App\Models\Cart $cart): void
    {
        $cart->items()->delete();
        Session::forget('applied_coupon_id');
    }
}
