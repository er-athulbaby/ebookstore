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
                        <div class="col-12" role="radiogroup" aria-label="Address type">
                            @foreach(['Home' => 'bi-house', 'Work' => 'bi-briefcase', 'Other' => 'bi-geo-alt'] as $type => $icon)
                                <input type="radio" class="btn-check" name="label" id="label{{ $type }}" value="{{ $type }}" @checked($loop->first)>
                                <label class="btn btn-sm btn-outline-brand me-1" for="label{{ $type }}"><i class="bi {{ $icon }} me-1" aria-hidden="true"></i>{{ $type }}</label>
                            @endforeach
                        </div>
                        <div class="col-12"><input name="full_name" class="form-control form-control-sm" placeholder="Full Name *" required></div>
                        <div class="col-12"><input name="phone" class="form-control form-control-sm" placeholder="Phone *" required></div>
                        <div class="col-12"><input name="line1" class="form-control form-control-sm" placeholder="Address Line 1 *" required></div>
                        <div class="col-12"><input name="line2" class="form-control form-control-sm" placeholder="Address Line 2"></div>
                        <div class="col-4"><input name="postal_code" id="pinInput" class="form-control form-control-sm" placeholder="PIN *" required inputmode="numeric" maxlength="6" pattern="[0-9]{6}" autocomplete="postal-code" aria-describedby="pinHint"></div>
                        <div class="col-4"><input name="city" id="cityInput" class="form-control form-control-sm" placeholder="City *" required></div>
                        <div class="col-4"><input name="state" id="stateInput" class="form-control form-control-sm" placeholder="State *" required></div>
                        <div class="col-12 small text-muted" id="pinHint" aria-live="polite">Enter the PIN and we'll fill in the city and state.</div>
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

@push('scripts')
<script>
(function () {
    // Free India Post lookup (CORS-enabled); if it fails, the fields simply stay editable by hand.
    var pin = document.getElementById('pinInput'), city = document.getElementById('cityInput'),
        state = document.getElementById('stateInput'), hint = document.getElementById('pinHint');
    if (!pin) return;
    pin.addEventListener('input', function () {
        var code = pin.value.replace(/\D/g, '').slice(0, 6);
        if (code !== pin.value) pin.value = code;
        if (code.length !== 6) return;
        hint.textContent = 'Looking up PIN…';
        fetch('https://api.postalpincode.in/pincode/' + code)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (pin.value !== code) return; // user kept typing
                var office = data && data[0] && data[0].Status === 'Success' && data[0].PostOffice[0];
                if (!office) { hint.textContent = 'PIN not found — please type the city and state.'; return; }
                city.value = office.District;
                state.value = office.State;
                hint.textContent = 'Filled in from PIN ' + code + '. You can edit them if needed.';
            })
            .catch(function () { hint.textContent = "Couldn't look up the PIN — please type the city and state."; });
    });
})();
</script>
@endpush
