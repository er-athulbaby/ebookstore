<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function confirmation(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $order->load('items.variant.book.images', 'items.giftVoucher');

        return view('orders.confirmation', compact('order'));
    }
}
