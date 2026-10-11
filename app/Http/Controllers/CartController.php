<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\BookVariant;
use App\Models\Coupon;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function index()
    {
        $cart = $this->cartService->current();
        $cart->load(['items.variant.book.images', 'items.bundle.variants.book']);

        $coupon = $this->appliedCoupon();

        return view('cart.index', [
            'cart' => $cart,
            'coupon' => $coupon,
            'discount' => $coupon ? $coupon->discountFor($cart->subtotal()) : 0,
        ]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'book_variant_id' => ['required', 'exists:book_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $variant = BookVariant::findOrFail($data['book_variant_id']);
        abort_unless($variant->is_active && $variant->inStock(), 422, 'This item is currently unavailable.');

        $quantity = $data['quantity'] ?? 1;

        // Ebooks are a one-time license per account — clamp quantity to 1
        // rather than letting the cart accumulate duplicate digital copies.
        if ($variant->isEbook()) {
            $quantity = 1;
        }

        $cart = $this->cartService->current();
        $item = $cart->items()->firstOrNew(['book_variant_id' => $variant->id]);
        $item->quantity = $variant->isEbook() ? 1 : ($item->exists ? $item->quantity + $quantity : $quantity);
        $item->save();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Added to cart', 'cartItemCount' => $this->cartService->itemCount()]);
        }

        return back()->with('status', 'Added "'.$variant->book->title.'" to your cart.');
    }

    public function addBundle(Bundle $bundle)
    {
        abort_unless($bundle->isAvailable(), 422, 'This bundle is currently unavailable.');

        $cart = $this->cartService->current();
        $item = $cart->items()->firstOrNew(['bundle_id' => $bundle->id]);
        $item->quantity = $item->exists ? $item->quantity + 1 : 1;
        $item->save();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Added to cart', 'cartItemCount' => $this->cartService->itemCount()]);
        }

        return back()->with('status', 'Added "'.$bundle->name.'" to your cart.');
    }

    public function addGiftCard(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', Rule::in(config('redcherry.gift_card_denominations'))],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $valuePaise = $data['amount'] * 100;
        $quantity = $data['quantity'] ?? 1;

        $cart = $this->cartService->current();
        $item = $cart->items()->firstOrNew(['gift_card_value' => $valuePaise]);
        $item->quantity = $item->exists ? $item->quantity + $quantity : $quantity;
        $item->save();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Added to cart', 'cartItemCount' => $this->cartService->itemCount()]);
        }

        return back()->with('status', 'Added a '.money($valuePaise).' gift card to your cart.');
    }

    public function update(Request $request, \App\Models\CartItem $cartItem)
    {
        $this->authorizeItem($cartItem);

        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:20']]);

        if ($cartItem->isEbook()) {
            $data['quantity'] = 1;
        }

        $cartItem->update($data);

        return back();
    }

    public function remove(\App\Models\CartItem $cartItem)
    {
        $this->authorizeItem($cartItem);
        $cartItem->delete();

        return back()->with('status', 'Item removed from cart.');
    }

    public function applyCoupon(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'string']]);

        $coupon = Coupon::whereRaw('lower(code) = ?', [strtolower($data['code'])])->first();
        $cart = $this->cartService->current();

        if (! $coupon || ! $coupon->isValidFor($cart->subtotal())) {
            return back()->withErrors(['code' => 'That coupon code is invalid or not applicable to your order.']);
        }

        Session::put('applied_coupon_id', $coupon->id);

        return back()->with('status', 'Coupon "'.$coupon->code.'" applied.');
    }

    public function removeCoupon()
    {
        Session::forget('applied_coupon_id');

        return back();
    }

    private function appliedCoupon(): ?Coupon
    {
        $id = Session::get('applied_coupon_id');

        return $id ? Coupon::find($id) : null;
    }

    private function authorizeItem(\App\Models\CartItem $cartItem): void
    {
        $cart = $this->cartService->current();
        abort_unless($cartItem->cart_id === $cart->id, 403);
    }
}
