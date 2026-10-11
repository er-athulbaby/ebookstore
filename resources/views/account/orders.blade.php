@extends('layouts.app')

@section('title', 'My Orders — '.config('redcherry.store_name'))

@section('content')
<div class="container py-4">
    <h1 class="h4 mb-4">My Orders</h1>
    <div class="row">
        <div class="col-md-3">@include('account._nav')</div>
        <div class="col-md-9">
            @forelse($orders as $order)
                <div class="border rounded p-3 mb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <a href="{{ route('account.orders.show', $order->order_number) }}" class="fw-semibold">{{ $order->order_number }}</a>
                        <div class="text-muted small">{{ $order->created_at->format('d M Y') }} &middot; {{ $order->items->count() }} item(s)</div>
                    </div>
                    <span class="badge text-bg-{{ $order->statusColor() }}">{{ ucfirst($order->status) }}</span>
                    <div class="fw-semibold">{{ money($order->total) }}</div>
                </div>
            @empty
                <p class="text-muted">No orders yet. <a href="{{ route('home') }}">Start shopping</a>.</p>
            @endforelse
            <div class="mt-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
</div>
@endsection
