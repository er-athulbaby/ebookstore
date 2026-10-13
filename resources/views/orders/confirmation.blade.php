@extends('layouts.app')

@section('title', 'Order Confirmed — '.config('redcherry.store_name'))

@section('content')
<div class="container py-5">
    <div class="text-center mb-4">
        <i class="bi bi-check-circle-fill text-success" style="font-size:3rem"></i>
        <h1 class="h4 mt-3">Order Confirmed!</h1>
        <p class="text-muted">Order <strong>{{ $order->order_number }}</strong> &middot; {{ money($order->total) }}</p>
        @if($order->payment_method === 'cod')
            <p class="small">You'll pay {{ money($order->amountDue()) }} in cash when your order is delivered.</p>
        @endif
        @if($order->items->contains(fn ($item) => $item->isEbook()))
            <a href="{{ route('account.downloads') }}" class="btn btn-brand btn-sm mt-2">Go to My Downloads</a>
        @endif
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="border rounded p-3">
                @foreach($order->items as $item)
                    <div class="d-flex justify-content-between small mb-2">
                        <span>
                            {{ $item->book_title }} <span class="text-muted">({{ $item->isGiftCard() ? 'Gift Card' : ucfirst($item->variant_type) }}) &times;{{ $item->quantity }}</span>
                            @if($item->isGiftCard() && $item->giftVoucher)
                                <br><span class="text-muted">Code: </span><code>{{ $item->giftVoucher->code }}</code>
                            @endif
                        </span>
                        <span>{{ money($item->line_total) }}</span>
                    </div>
                @endforeach
                <hr>
                <div class="d-flex justify-content-between small mb-1"><span>Subtotal</span><span>{{ money($order->subtotal) }}</span></div>
                @if($order->discount_amount > 0)
                    <div class="d-flex justify-content-between small mb-1 text-success"><span>Discount</span><span>-{{ money($order->discount_amount) }}</span></div>
                @endif
                <div class="d-flex justify-content-between small mb-1"><span>Shipping</span><span>{{ $order->shipping_amount > 0 ? money($order->shipping_amount) : 'Free' }}</span></div>
                <hr>
                <div class="d-flex justify-content-between fw-bold"><span>Total</span><span>{{ money($order->total) }}</span></div>
                @if($order->voucher_amount > 0)
                    <div class="d-flex justify-content-between small text-success"><span>Paid from wallet</span><span>-{{ money($order->voucher_amount) }}</span></div>
                    <div class="d-flex justify-content-between small fw-semibold"><span>{{ $order->payment_method === 'cod' ? 'To pay on delivery' : 'Paid by '.($order->payment_method === 'free' ? 'wallet' : 'card / UPI') }}</span><span>{{ money($order->amountDue()) }}</span></div>
                @endif
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('account.orders.show', $order->order_number) }}" class="btn btn-outline-brand">View Order Details</a>
                <a href="{{ route('home') }}" class="btn btn-brand">Continue Shopping</a>
            </div>
        </div>
    </div>
</div>
@endsection
