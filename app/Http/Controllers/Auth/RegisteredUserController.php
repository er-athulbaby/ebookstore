<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request, CartService $cartService): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);
        $cartService->mergeGuestCartInto($user);

        return redirect()->to($this->safeRedirect($request))
            ->with('status', 'Welcome to RedCherry Books, '.$user->name.'!');
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
