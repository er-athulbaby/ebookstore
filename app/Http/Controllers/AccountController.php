<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\EbookLicense;
use App\Models\GiftVoucher;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $recentOrders = $user->orders()->latest()->take(5)->get();

        return view('account.dashboard', compact('recentOrders'));
    }

    public function orders()
    {
        $orders = Auth::user()->orders()->with('items')->latest()->paginate(10);

        return view('account.orders', compact('orders'));
    }

    public function orderShow(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        $order->load('items.variant.book.images', 'items.giftVoucher', 'shippingAddress');

        return view('account.order-show', compact('order'));
    }

    public function addresses()
    {
        $addresses = Auth::user()->addresses;

        return view('account.addresses', compact('addresses'));
    }

    public function storeAddress(Request $request)
    {
        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'full_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:20'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:12'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $data['label'] = $data['label'] ?: 'Home';

        $user = Auth::user();

        if ($request->boolean('is_default') || $user->addresses()->doesntExist()) {
            $user->addresses()->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        $user->addresses()->create($data);

        return back()->with('status', 'Address saved.');
    }

    public function destroyAddress(Address $address)
    {
        abort_unless($address->user_id === Auth::id(), 403);
        $address->delete();

        return back()->with('status', 'Address removed.');
    }

    public function downloads()
    {
        $licenses = EbookLicense::where('user_id', Auth::id())
            ->with('variant.book.images')
            ->latest()
            ->get();

        return view('account.downloads', compact('licenses'));
    }

    public function wallet()
    {
        $redeemed = Auth::user()->redeemedGiftVouchers()->latest()->get();

        return view('account.wallet', compact('redeemed'));
    }

    public function redeemVoucher(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'string']]);

        $voucher = GiftVoucher::whereRaw('lower(code) = ?', [strtolower($data['code'])])->first();

        if (! $voucher || ! $voucher->isValid()) {
            return back()->withErrors(['code' => 'That gift code is invalid, expired, or already used.']);
        }

        DB::transaction(function () use ($voucher) {
            Auth::user()->increment('wallet_balance', $voucher->value);
            $voucher->update(['is_redeemed' => true, 'redeemed_by_user_id' => Auth::id()]);
        });

        return back()->with('status', money($voucher->value).' added to your wallet.');
    }
}
