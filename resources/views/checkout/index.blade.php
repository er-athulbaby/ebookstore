@extends('layouts.app')

@section('title', 'Checkout — '.config('redcherry.store_name'))

@php
    // Wallet is applied first; the payment method only covers what is left (mirrors OrderService).
    $walletUsable = min($walletBalance, $total);
    $dueWithWallet = $total - $walletUsable;
@endphp

@section('content')
<div class="container py-4">
    <h1 class="h3 mb-4">Checkout</h1>

    <div class="row g-4">
        <div class="col-lg-7">
            <form method="POST" action="{{ route('checkout.store') }}" id="checkoutForm">
                @csrf
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

                @if($walletBalance > 0)
                    <div class="border rounded p-3 mb-3">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="use_wallet" value="1" id="useWallet" checked data-wallet-toggle>
                            <label class="form-check-label" for="useWallet">
                                <i class="bi bi-wallet2 me-1" aria-hidden="true"></i> Use wallet balance
                                <span class="text-muted small">({{ money($walletBalance) }} available)</span>
                            </label>
                        </div>
                    </div>
                @endif

                <div class="border rounded p-3" data-payment-methods>
                    <h2 class="h6">Payment Method</h2>
                    <p class="small text-muted mb-2" data-pay-rest-note @if($walletBalance <= 0) hidden @endif>For the remaining {{ money($dueWithWallet) }}:</p>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment_method" value="razorpay" id="payRazorpay" checked>
                        <label class="form-check-label" for="payRazorpay">
                            <i class="bi bi-credit-card me-1" aria-hidden="true"></i> Cards / UPI / Netbanking (Razorpay)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" value="cod" id="payCod" @disabled(!$allowCod)>
                        <label class="form-check-label" for="payCod">
                            <i class="bi bi-cash me-1" aria-hidden="true"></i> Cash on Delivery
                            @if(!$allowCod)<span class="text-muted small">(not available — your order includes an eBook or gift card)</span>@endif
                        </label>
                    </div>
                </div>
                <div class="border rounded p-3 small text-success" data-nothing-to-pay hidden>
                    <i class="bi bi-check-circle me-1" aria-hidden="true"></i> Nothing left to pay — your order will be placed straight away.
                </div>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="border rounded p-3 mb-3">
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
                @if($requiresAddress)
                    <div class="d-flex justify-content-between small mb-1"><span>Shipping</span><span>{{ $shipping > 0 ? money($shipping) : 'Free' }}</span></div>
                @endif
                <div class="d-flex justify-content-between small mb-1 fw-semibold"><span>Total</span><span>{{ money($total) }}</span></div>
                @if($walletBalance > 0)
                    <div class="d-flex justify-content-between small mb-1 text-success" data-wallet-line><span>Wallet</span><span>-{{ money($walletUsable) }}</span></div>
                @endif
                <hr>
                <div class="d-flex justify-content-between fw-bold mb-3"><span>To pay</span><span data-due>{{ money($walletBalance > 0 ? $dueWithWallet : $total) }}</span></div>

                <button type="submit" form="checkoutForm" class="btn btn-brand w-100">Place Order</button>
            </div>

            <div class="border rounded p-3">
                <form method="POST" action="{{ route('checkout.gift-card') }}">
                    @csrf
                    <label for="giftCode" class="h6 d-block mb-2">Have a gift card?</label>
                    <div class="input-group input-group-sm">
                        <input id="giftCode" name="gift_code" value="{{ old('gift_code') }}" class="form-control text-uppercase" placeholder="GIFT-XXXX-XXXX" autocomplete="off" required
                               @error('gift_code') aria-invalid="true" aria-describedby="giftCodeError" @enderror>
                        <button class="btn btn-outline-brand">Apply</button>
                    </div>
                    @error('gift_code')<div id="giftCodeError" class="small text-danger mt-1">{{ $message }}</div>@enderror
                    <div class="small text-muted mt-1">The amount is added to your wallet and used for this order.</div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Mirror of the server's maths so the summary updates when the wallet box is ticked/unticked.
    var toggle = document.querySelector('[data-wallet-toggle]');
    var total = {{ $total }}, walletUsable = {{ $walletUsable }};
    var fmt = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', minimumFractionDigits: 0, maximumFractionDigits: 2 });
    function render() {
        var useWallet = toggle ? toggle.checked : false;
        var due = total - (useWallet ? walletUsable : 0);
        document.querySelector('[data-due]').textContent = fmt.format(due / 100);
        var line = document.querySelector('[data-wallet-line]');
        if (line) line.hidden = !useWallet;
        var note = document.querySelector('[data-pay-rest-note]');
        if (note) note.hidden = !useWallet;
        document.querySelector('[data-payment-methods]').hidden = due === 0;
        document.querySelector('[data-nothing-to-pay]').hidden = due !== 0;
    }
    if (toggle) toggle.addEventListener('change', render);
    render();
})();
</script>
@endpush
