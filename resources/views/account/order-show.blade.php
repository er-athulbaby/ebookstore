@extends('layouts.app')

@section('title', $order->order_number.' — '.config('redcherry.store_name'))

@section('content')
<div class="container py-4">
    <h1 class="h4 mb-4">Order {{ $order->order_number }}</h1>
    <div class="row">
        <div class="col-md-3">@include('account._nav')</div>
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge text-bg-{{ $order->statusColor() }} fs-6">{{ ucfirst($order->status) }}</span>
                <span class="text-muted small">Placed {{ $order->placed_at?->format('d M Y, h:i A') }}</span>
            </div>

            <div class="border rounded p-3 mb-3">
                @foreach($order->items as $item)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2 small">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $item->isGiftCard() ? asset('assets/img/gift-card-placeholder.svg') : ($item->variant?->book?->coverUrl() ?? asset('assets/img/book-placeholder.svg')) }}" style="width:40px;height:55px;object-fit:cover" class="rounded border">
                            <div>
                                {{ $item->book_title }}<br>
                                <span class="text-muted">{{ $item->isGiftCard() ? 'Gift Card' : ucfirst($item->variant_type) }} &times;{{ $item->quantity }}</span>
                                @if($item->isGiftCard() && $item->giftVoucher)
                                    <br><span class="text-muted">Code: </span><code>{{ $item->giftVoucher->code }}</code>
                                @endif
                            </div>
                        </div>
                        <div>{{ money($item->line_total) }}</div>
                    </div>
                @endforeach
                <div class="d-flex justify-content-between small pt-2"><span>Subtotal</span><span>{{ money($order->subtotal) }}</span></div>
                @if($order->discount_amount > 0)
                    <div class="d-flex justify-content-between small text-success"><span>Discount</span><span>-{{ money($order->discount_amount) }}</span></div>
                @endif
                <div class="d-flex justify-content-between small"><span>Shipping</span><span>{{ $order->shipping_amount > 0 ? money($order->shipping_amount) : 'Free' }}</span></div>
                <div class="d-flex justify-content-between fw-bold pt-1"><span>Total</span><span>{{ money($order->total) }}</span></div>
                @if($order->voucher_amount > 0)
                    <div class="d-flex justify-content-between small text-success"><span>Paid from wallet</span><span>-{{ money($order->voucher_amount) }}</span></div>
                    <div class="d-flex justify-content-between small fw-semibold"><span>{{ $order->payment_method === 'cod' ? 'To pay on delivery' : 'Paid by '.($order->payment_method === 'free' ? 'wallet' : 'card / UPI') }}</span><span>{{ money($order->amountDue()) }}</span></div>
                @endif
            </div>

            @if($order->shippingAddress)
                <div class="border rounded p-3 mb-3">
                    <h2 class="h6">Shipping Address</h2>
                    <p class="small mb-0">{{ $order->shippingAddress->full_name }} ({{ $order->shippingAddress->phone }})<br>{{ $order->shippingAddress->formatted() }}</p>
                </div>
            @endif

            <div class="border rounded p-3">
                <h2 class="h6">Payment</h2>
                <p class="small mb-0">
                    Method: {{ match($order->payment_method) { 'cod' => 'Cash on Delivery', 'wallet' => 'Wallet', default => 'Razorpay' } }}<br>
                    Status: {{ ucfirst($order->payment_status) }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
