<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\EbookLicense;
use App\Models\GiftVoucher;
use App\Models\Order;
use App\Models\User;
use App\Notifications\GiftVoucherIssued;
use App\Notifications\OrderConfirmed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class OrderService
{
    /**
     * Turn a cart into a pending order. Physical stock is NOT decremented
     * here — only once payment is confirmed (markPaid) — so an abandoned
     * checkout never locks up inventory.
     */
    public function createFromCart(Cart $cart, User $user, ?int $shippingAddressId, string $paymentMethod, ?Coupon $coupon): Order
    {
        $cart->loadMissing('items.variant.book', 'items.bundle.variants');

        abort_if($cart->items->isEmpty(), 422, 'Your cart is empty.');

        foreach ($cart->items as $item) {
            abort_unless($item->isAvailable(), 422,
                '"'.$item->displayTitle().'" is no longer available in the quantity requested.');
        }

        $hasPhysical = $cart->hasPhysicalItems();
        $hasDigital = $cart->hasDigitalItems();
        $hasGiftCard = $cart->hasGiftCardItems();

        abort_if($paymentMethod === 'cod' && ($hasGiftCard || ($hasDigital && ! config('redcherry.cod_for_ebooks'))), 422,
            'Cash on Delivery isn\'t available for eBooks or gift card purchases — these are delivered instantly and require online payment or wallet.');
        abort_if($hasPhysical && ! $shippingAddressId, 422, 'A shipping address is required for physical items.');

        $subtotal = $cart->subtotal();
        $discount = $coupon ? min($coupon->discountFor($subtotal), $subtotal) : 0;
        $shipping = $this->shippingFor($subtotal - $discount, $hasPhysical);
        $total = max(0, $subtotal - $discount) + $shipping;

        abort_if($paymentMethod === 'wallet' && $user->wallet_balance < $total, 422,
            'Your wallet balance isn\'t enough to cover this order.');

        return DB::transaction(function () use ($cart, $user, $shippingAddressId, $paymentMethod, $coupon, $subtotal, $discount, $shipping, $total, $hasPhysical, $hasDigital, $hasGiftCard) {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'status' => 'pending',
                'fulfillment_type' => $hasPhysical && ($hasDigital || $hasGiftCard) ? 'mixed' : (($hasDigital || $hasGiftCard) ? 'digital' : 'physical'),
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'shipping_amount' => $shipping,
                'total' => $total,
                'coupon_id' => $coupon?->id,
                'shipping_address_id' => $hasPhysical ? $shippingAddressId : null,
                'contact_email' => $user->email,
                'contact_phone' => $user->phone,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'placed_at' => now(),
            ]);

            foreach ($cart->items as $item) {
                if ($item->isGiftCard()) {
                    // One row per unit, not one row with quantity=N: each gift card
                    // gets its own distinct code, and an order_item can only link to
                    // a single generated voucher (see fulfillItems below).
                    for ($i = 0; $i < $item->quantity; $i++) {
                        $order->items()->create([
                            'gift_card_value' => $item->gift_card_value,
                            'book_title' => $item->displayTitle(),
                            'variant_type' => 'gift_card',
                            'unit_price' => $item->gift_card_value,
                            'quantity' => 1,
                            'line_total' => $item->gift_card_value,
                        ]);
                    }

                    continue;
                }

                $order->items()->create($item->isBundle() ? [
                    'bundle_id' => $item->bundle_id,
                    'book_title' => $item->bundle->name,
                    'variant_type' => 'bundle',
                    'unit_price' => $item->bundle->price,
                    'quantity' => $item->quantity,
                    'line_total' => $item->lineTotal(),
                ] : [
                    'book_variant_id' => $item->book_variant_id,
                    'book_title' => $item->variant->book->title,
                    'variant_type' => $item->variant->type,
                    'unit_price' => $item->variant->price,
                    'quantity' => $item->quantity,
                    'line_total' => $item->lineTotal(),
                ]);
            }

            return $order;
        });
    }

    /**
     * Idempotent: safe to call from both the client-side verify callback and
     * the Razorpay webhook without double-fulfilling (double stock decrement,
     * duplicate ebook licenses) if both fire for the same order. Also used
     * for wallet payments, which are confirmed synchronously at checkout.
     */
    public function markPaid(Order $order): void
    {
        if ($order->isPaid()) {
            return;
        }

        $issuedVouchers = [];

        DB::transaction(function () use ($order, &$issuedVouchers) {
            $order->refresh();

            if ($order->isPaid()) {
                return;
            }

            $order->update([
                'status' => $order->fulfillment_type === 'digital' ? 'delivered' : 'paid',
                'payment_status' => 'paid',
            ]);

            $issuedVouchers = $this->fulfillItems($order);
            $this->redeemPromotions($order);
        });

        $order->user->notify(new OrderConfirmed($order));

        foreach ($issuedVouchers as $voucher) {
            $order->user->notify(new GiftVoucherIssued($voucher));
        }
    }

    /**
     * COD orders never go through markPaid() (payment isn't collected until
     * delivery), but placing one is still a firm commitment — so stock and
     * one-time coupon usage must be committed right away here, not left
     * waiting on a "paid" event that will never come for this order.
     * (COD carts can never contain a gift card purchase, so no voucher
     * issuance path is needed here.)
     */
    public function commitCodOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $this->fulfillItems($order);
            $this->redeemPromotions($order);
        });
    }

    /**
     * Debits the wallet and records the payment atomically — if the payment
     * row fails to write for any reason, the debit must roll back with it,
     * not leave the customer's balance gone with no order to show for it.
     * Row-locked and re-validated here (not just trusted from the earlier
     * createFromCart check) so two simultaneous wallet checkouts can't both
     * succeed past the same balance.
     */
    public function payFromWallet(Order $order, User $user): void
    {
        DB::transaction(function () use ($order, $user) {
            $locked = User::whereKey($user->id)->lockForUpdate()->first();
            abort_if($locked->wallet_balance < $order->total, 422, 'Your wallet balance isn\'t enough to cover this order.');
            $locked->decrement('wallet_balance', $order->total);

            $order->payments()->create([
                'provider' => 'wallet',
                'amount' => $order->total,
                'currency' => 'INR',
                'status' => 'captured',
            ]);
        });

        $this->markPaid($order);
    }

    public function markFailed(Order $order): void
    {
        if ($order->payment_status !== 'paid') {
            $order->update(['payment_status' => 'failed']);
        }
    }

    /** @return GiftVoucher[] newly issued vouchers, for post-commit email delivery */
    private function fulfillItems(Order $order): array
    {
        $issuedVouchers = [];

        foreach ($order->items()->with('variant', 'bundle.variants')->get() as $item) {
            if ($item->isGiftCard()) {
                $voucher = GiftVoucher::create([
                    'code' => GiftVoucher::generateCode(),
                    'value' => $item->gift_card_value,
                ]);
                $item->update(['gift_voucher_id' => $voucher->id]);
                $issuedVouchers[] = $voucher;

                continue;
            }

            if ($item->isBundle()) {
                // ponytail: no row lock here, same known small-store tradeoff as the
                // single-variant path below — a bundle's constituent stock can go
                // negative under concurrent checkouts on the last copy.
                foreach ($item->bundle->variants as $variant) {
                    if (! is_null($variant->stock_quantity)) {
                        $variant->decrement('stock_quantity', $item->quantity);
                    }
                }

                continue;
            }

            if ($item->isEbook()) {
                EbookLicense::firstOrCreate(
                    ['user_id' => $order->user_id, 'book_variant_id' => $item->book_variant_id],
                    [
                        'order_item_id' => $item->id,
                        'download_limit' => Config::get('redcherry.ebook_download_limit', 5),
                    ]
                );

                continue;
            }

            // ponytail: no row lock here, so two orders racing on the last copy
            // can both succeed and take stock negative — fine at small-bookstore
            // volume (Filament just shows negative stock to reorder/backorder);
            // if concurrent checkouts on the same title get common, swap this for
            // a conditional `WHERE stock_quantity >= X` update and reject on 0 rows.
            $variant = $item->variant;
            if ($variant && ! is_null($variant->stock_quantity)) {
                $variant->decrement('stock_quantity', $item->quantity);
            }
        }

        return $issuedVouchers;
    }

    private function redeemPromotions(Order $order): void
    {
        if ($order->coupon_id) {
            $order->coupon()->increment('used_count');
        }
    }

    private function shippingFor(int $netAmount, bool $hasPhysical): int
    {
        if (! $hasPhysical) {
            return 0;
        }

        $thresholdPaise = Config::get('redcherry.free_delivery_threshold', 799) * 100;
        $feePaise = Config::get('redcherry.flat_shipping_fee', 60) * 100;

        return $netAmount >= $thresholdPaise ? 0 : $feePaise;
    }
}
