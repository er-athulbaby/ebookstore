<?php

namespace App\Http\Middleware;

use App\Models\Wishlist;
use App\Services\CartService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareCartCount
{
    public function __construct(private readonly CartService $cartService) {}

    public function handle(Request $request, Closure $next): Response
    {
        View::share('cartItemCount', $this->cartService->itemCount());

        View::share('wishlistedBookIds', Auth::check()
            ? Wishlist::where('user_id', Auth::id())->pluck('book_id')->all()
            : []);

        return $next($request);
    }
}
