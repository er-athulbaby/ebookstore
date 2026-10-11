<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * Resolves the "current" cart for the request: a user's persistent cart when
 * logged in, or a session-scoped guest cart otherwise. Guest carts are
 * merged into the user's cart the moment they log in (see mergeGuestCartInto).
 */
class CartService
{
    private const SESSION_KEY = 'guest_cart_token';

    public function current(): Cart
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(['user_id' => Auth::id()]);
        }

        $token = Session::get(self::SESSION_KEY);

        if (! $token) {
            $token = (string) Str::uuid();
            Session::put(self::SESSION_KEY, $token);
        }

        return Cart::firstOrCreate(['session_id' => $token, 'user_id' => null]);
    }

    public function mergeGuestCartInto(User $user): void
    {
        $token = Session::get(self::SESSION_KEY);

        if (! $token) {
            return;
        }

        $guestCart = Cart::where('session_id', $token)->whereNull('user_id')->first();

        if (! $guestCart) {
            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

        foreach ($guestCart->items as $item) {
            $existing = $userCart->items()->where('book_variant_id', $item->book_variant_id)->first();

            if ($existing) {
                $existing->increment('quantity', $item->quantity);
            } else {
                $userCart->items()->create([
                    'book_variant_id' => $item->book_variant_id,
                    'quantity' => $item->quantity,
                ]);
            }
        }

        $guestCart->delete();
        Session::forget(self::SESSION_KEY);
    }

    public function itemCount(): int
    {
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
        } else {
            $token = Session::get(self::SESSION_KEY);
            $cart = $token ? Cart::where('session_id', $token)->first() : null;
        }

        return $cart ? (int) $cart->items()->sum('quantity') : 0;
    }
}
