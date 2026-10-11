@extends('layouts.app')

@section('title', 'My Wallet — '.config('redcherry.store_name'))

@section('content')
<div class="container py-4">
    <h1 class="h4 mb-4">My Wallet</h1>
    <div class="row">
        <div class="col-md-3">@include('account._nav')</div>
        <div class="col-md-9">
            <div class="border rounded p-4 mb-3 text-center bg-brand-soft">
                <div class="text-muted small text-uppercase">Wallet Balance</div>
                <div class="display-6 fw-bold text-brand">{{ money(auth()->user()->wallet_balance) }}</div>
            </div>

            <div class="border rounded p-3 mb-3">
                <h2 class="h6">Redeem a Gift Code</h2>
                <p class="text-muted small">Have a gift card code? Enter it here to add its value to your wallet.</p>
                <form method="POST" action="{{ route('account.wallet.redeem') }}" class="d-flex gap-2">
                    @csrf
                    <input type="text" name="code" class="form-control" placeholder="e.g. GIFT-AB12CD34" required>
                    <button class="btn btn-brand">Redeem</button>
                </form>
            </div>

            <div class="border rounded p-3 mb-3">
                <p class="mb-0 small">Don't have a code yet? <a href="{{ route('gift-cards.index') }}">Buy a gift card</a> — we'll email you one instantly, and it works the same way here.</p>
            </div>

            @if($redeemed->isNotEmpty())
                <div class="border rounded p-3">
                    <h2 class="h6">Redeemed Codes</h2>
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Code</th><th>Value</th><th>Redeemed</th></tr></thead>
                        <tbody>
                            @foreach($redeemed as $voucher)
                                <tr>
                                    <td><code>{{ $voucher->code }}</code></td>
                                    <td>{{ money($voucher->value) }}</td>
                                    <td>{{ $voucher->updated_at->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
