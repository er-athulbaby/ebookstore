@extends('layouts.app')

@section('title', 'Checkout — '.config('redcherry.store_name'))

@section('content')
<div class="container py-4">
    <h1 class="h3 mb-4">Checkout</h1>

    <form method="POST" action="{{ route('checkout.store') }}">
        @csrf
        <div class="row g-4">
            <div class="col-lg-7">
                @if($requiresAddress)
                    <div class="border rounded p-3 mb-3">
                        <h2 class="h6">Shipping Address</h2>
                        @if($addresses->isEmpty())
                            <p class="text-muted small">You don't have a saved address yet.</p>
                        @else
                            @foreach($addresses as $address)
                                <div class="form-check border rounded p-2 mb-2">
                                    <input class="form-check-input" type="radio" name="shipping_address_id"
                                           id="addr{{ $address->id }}" value="{{ $address->id }}"
                                           @checked($loop->first || $address->is_default)>
                                    <label class="form-check-label small" for="addr{{ $address->id }}">
                                        <strong>{{ $address->full_name }}</strong> ({{ $address->phone }})<br>
                                        {{ $address->formatted() }}
                                    </label>
                                </div>
                            @endforeach
                        @endif
                        <a href="{{ route('account.addresses') }}" target="_blank" class="small">+ Add a new address</a>
                    </div>
                @endif

                <div class="border rounded p-3">
                    <h2 class="h6">Payment Method</h2>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment_method" value="razorpay" id="payRazorpay" checked>
                        <label class="form-check-label" for="payRazorpay">
                            <i class="bi bi-credit-card me-1"></i> Cards / UPI / Netbanking (Razorpay)
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment_method" value="wallet" id="payWallet" @disabled(!$allowWallet)>
                        <label class="form-check-label" for="payWallet">
                            <i class="bi bi-wallet2 me-1"></i> Wallet (balance: {{ money($walletBalance) }})
                            @if(!$allowWallet)<span class="text-muted small">(insufficient balance)</span>@endif
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" value="cod" id="payCod" @disabled(!$allowCod)>
                        <label class="form-check-label" for="payCod">
                            <i class="bi bi-cash me-1"></i> Cash on Delivery
                            @if(!$allowCod)<span class="text-muted small">(not available — your order includes an eBook or gift card)</span>@endif
                        </label>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="border rounded p-3">
                    <h2 class="h6">Order Summary</h2>
                    @foreach($cart->items as $item)
                        <div class="d-flex justify-content-between small mb-2">
                            <span>{{ $item->displayTitle() }} <span class="text-muted">({{ $item->displaySubtitle() }}) &times;{{ $item->quantity }}</span></span>
                            <span>{{ money($item->lineTotal()) }}</span>
                        </div>
                    @endforeach
                    <hr>
                    <div class="d-flex justify-content-between small mb-1"><span>Subtotal</span><span>{{ money($cart->subtotal()) }}</span></div>
                    @if($discount > 0)
                        <div class="d-flex justify-content-between small mb-1 text-success"><span>Discount</span><span>-{{ money($discount) }}</span></div>
                    @endif
                    <div class="d-flex justify-content-between small mb-1 text-muted"><span>Shipping</span><span>Calculated on next step</span></div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold mb-3"><span>Total (before shipping)</span><span>{{ money(max(0, $cart->subtotal() - $discount)) }}</span></div>

                    <button type="submit" class="btn btn-brand w-100">Place Order</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
