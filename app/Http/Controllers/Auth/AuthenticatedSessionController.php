<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request, CartService $cartService): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();
        $cartService->mergeGuestCartInto(Auth::user());

        return redirect()->to($this->safeRedirect($request));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /** Only ever redirect to a same-site relative path — never trust an absolute/external URL from the query string. */
    private function safeRedirect(Request $request): string
    {
        $target = $request->input('redirect_to');

        if (is_string($target) && str_starts_with($target, '/') && ! str_starts_with($target, '//')) {
            return $target;
        }

        return route('home');
    }
}
