@extends('layouts.app')

@section('title', 'Your Cart — '.config('redcherry.store_name'))

@section('content')
<div class="container py-4">
    <h1 class="h3 mb-4">Your Cart</h1>

    @if($cart->items->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-bag-x fs-1 text-muted"></i>
            <p class="text-muted mt-2">Your cart is empty.</p>
            <a href="{{ route('home') }}" class="btn btn-brand">Continue Shopping</a>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-8">
                @foreach($cart->items as $item)
                    <div class="d-flex gap-3 border-bottom pb-3 mb-3">
                        @if($item->isBundle() || $item->isGiftCard())
                            <img src="{{ $item->coverUrl() }}" alt="" style="width:70px;height:95px;object-fit:cover" class="rounded border">
                        @else
                            <a href="{{ route('books.show', $item->variant->book) }}">
                                <img src="{{ $item->coverUrl() }}" alt="" style="width:70px;height:95px;object-fit:cover" class="rounded border">
                            </a>
                        @endif
                        <div class="flex-grow-1">
                            @if($item->isBundle())
                                <span class="fw-semibold d-block">{{ $item->displayTitle() }} <span class="badge text-bg-secondary">Bundle</span></span>
                            @elseif($item->isGiftCard())
                                <span class="fw-semibold d-block">{{ $item->displayTitle() }} <span class="badge text-bg-secondary">Gift Card</span></span>
                            @else
                                <a href="{{ route('books.show', $item->variant->book) }}" class="fw-semibold text-decoration-none d-block">{{ $item->displayTitle() }}</a>
                            @endif
                            <div class="text-muted small">{{ $item->displaySubtitle() }}</div>
                            <div class="text-brand fw-semibold">{{ money($item->unitPrice()) }}</div>

                            <div class="d-flex align-items-center gap-3 mt-2">
                                @unless($item->isEbook())
                                    <form method="POST" action="{{ route('cart.update', $item) }}" class="d-flex align-items-center">
                                        @csrf @method('PATCH')
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="20"
                                               class="form-control form-control-sm" style="width:70px" data-qty-input>
                                    </form>
                                @else
                                    <span class="small text-muted">Qty: 1 (eBook license)</span>
                                @endunless
                                <form method="POST" action="{{ route('cart.remove', $item) }}">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-link btn-sm text-danger p-0">Remove</button>
                                </form>
                            </div>
                        </div>
                        <div class="fw-semibold">{{ money($item->lineTotal()) }}</div>
                    </div>
                @endforeach

                <a href="{{ route('home') }}" class="small"><i class="bi bi-arrow-left"></i> Continue shopping</a>
            </div>

            <div class="col-lg-4">
                <div class="border rounded p-3">
                    <h2 class="h6">Order Summary</h2>

                    @if($coupon)
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Coupon "{{ $coupon->code }}"</span>
                            <form method="POST" action="{{ route('cart.coupon.remove') }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-link btn-sm text-danger p-0">Remove</button>
                            </form>
                        </div>
                    @else
                        <form method="POST" action="{{ route('cart.coupon.apply') }}" class="mb-2">
                            @csrf
                            <div class="input-group input-group-sm">
                                <input type="text" name="code" class="form-control" placeholder="Coupon code">
                                <button class="btn btn-outline-brand">Apply</button>
                            </div>
                        </form>
                    @endif

                    <div class="d-flex justify-content-between small mb-1">
                        <span>Subtotal</span><span>{{ money($cart->subtotal()) }}</span>
                    </div>
                    @if($discount > 0)
                        <div class="d-flex justify-content-between small mb-1 text-success">
                            <span>Discount</span><span>-{{ money($discount) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between small mb-2 text-muted">
                        <span>Shipping</span><span>Calculated at checkout</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold mb-3">
                        <span>Total</span><span>{{ money(max(0, $cart->subtotal() - $discount)) }}</span>
                    </div>

                    @auth
                        <a href="{{ route('checkout.index') }}" class="btn btn-brand w-100">Proceed to Checkout</a>
                    @else
                        <a href="{{ route('login') }}?redirect_to={{ urlencode(route('checkout.index', [], false)) }}" class="btn btn-brand w-100">Login to Checkout</a>
                    @endauth
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
