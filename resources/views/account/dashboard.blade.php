@extends('layouts.app')

@section('title', 'My Account — '.config('redcherry.store_name'))

@section('content')
<div class="container py-4">
    <h1 class="h4 mb-4">My Account</h1>
    <div class="row">
        <div class="col-md-3">@include('account._nav')</div>
        <div class="col-md-9">
            <p>Welcome back, <strong>{{ auth()->user()->name }}</strong>.</p>
            <h2 class="h6 mt-4">Recent Orders</h2>
            @forelse($recentOrders as $order)
                <div class="d-flex justify-content-between border-bottom py-2 small">
                    <a href="{{ route('account.orders.show', $order->order_number) }}">{{ $order->order_number }}</a>
                    <span class="badge text-bg-{{ $order->statusColor() }}">{{ ucfirst($order->status) }}</span>
                    <span>{{ money($order->total) }}</span>
                    <span class="text-muted">{{ $order->created_at->format('d M Y') }}</span>
                </div>
            @empty
                <p class="text-muted small">No orders yet. <a href="{{ route('home') }}">Start shopping</a>.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
