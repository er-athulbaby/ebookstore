@extends('layouts.app')

@section('title', 'Gift Cards — '.config('redcherry.store_name'))

@section('content')
<div class="container py-4">
    <div class="section-eyebrow">Give the Gift of Reading</div>
    <h1 class="section-title h3 mb-2">Gift Cards</h1>
    <p class="text-muted mb-4" style="max-width:640px">
        Buy a gift card and we'll email you a code instantly. Keep it for yourself — enter it on your
        <a href="{{ route('account.wallet') }}">Wallet</a> page to top up your balance — or share it with someone else to redeem.
    </p>

    <div class="row row-cols-2 row-cols-md-4 g-3" style="max-width:800px">
        @foreach($denominations as $amount)
            <div class="col">
                <div class="book-card h-100 d-flex flex-column text-center p-3">
                    <img src="{{ asset('assets/img/gift-card-placeholder.svg') }}" alt="Gift card" class="rounded mb-2" style="width:100%;aspect-ratio:1;object-fit:cover">
                    <div class="fw-bold h5 mb-2">₹{{ number_format($amount) }}</div>
                    <form method="POST" action="{{ route('cart.gift-card.add') }}" class="mt-auto">
                        @csrf
                        <input type="hidden" name="amount" value="{{ $amount }}">
                        <button type="submit" class="btn btn-brand btn-sm w-100">Add to Cart</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
