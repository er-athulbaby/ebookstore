@extends('layouts.app')

@section('title', 'Addresses — '.config('redcherry.store_name'))

@section('content')
<div class="container py-4">
    <h1 class="h4 mb-4">My Addresses</h1>
    <div class="row">
        <div class="col-md-3">@include('account._nav')</div>
        <div class="col-md-9">
            <div class="row g-3 mb-4">
                @foreach($addresses as $address)
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            @if($address->is_default)<span class="badge bg-brand mb-1">Default</span>@endif
                            <div class="fw-semibold">{{ $address->label }}</div>
                            <div class="small">{{ $address->full_name }} &middot; {{ $address->phone }}</div>
                            <div class="small text-muted">{{ $address->formatted() }}</div>
                            <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" class="mt-2">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="border rounded p-3" style="max-width:500px">
                <h2 class="h6">Add New Address</h2>
                <form method="POST" action="{{ route('account.addresses.store') }}">
                    @csrf
                    <div class="row g-2">
                        <div class="col-6"><input name="label" class="form-control form-control-sm" placeholder="Label (e.g. Home)"></div>
                        <div class="col-6"><input name="full_name" class="form-control form-control-sm" placeholder="Full Name *" required></div>
                        <div class="col-12"><input name="phone" class="form-control form-control-sm" placeholder="Phone *" required></div>
                        <div class="col-12"><input name="line1" class="form-control form-control-sm" placeholder="Address Line 1 *" required></div>
                        <div class="col-12"><input name="line2" class="form-control form-control-sm" placeholder="Address Line 2"></div>
                        <div class="col-4"><input name="city" class="form-control form-control-sm" placeholder="City *" required></div>
                        <div class="col-4"><input name="state" class="form-control form-control-sm" placeholder="State *" required></div>
                        <div class="col-4"><input name="postal_code" class="form-control form-control-sm" placeholder="PIN *" required></div>
                        <div class="col-12 form-check mt-2">
                            <input type="checkbox" name="is_default" value="1" class="form-check-input" id="isDefault">
                            <label class="form-check-label small" for="isDefault">Set as default address</label>
                        </div>
                    </div>
                    <button class="btn btn-brand btn-sm mt-3">Save Address</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
