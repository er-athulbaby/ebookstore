@extends('layouts.app')

@section('title', 'Gift Cards — '.config('redcherry.store_name'))

@section('content')
<div class="container py-4">
    <h1 class="section-title h2 mb-2">Gift Cards</h1>
    <p class="text-muted mb-4" style="max-width:640px">
        Send a gift card by email. The recipient gets a unique code to add to their wallet and spend on any books.
        Valid for one year.
    </p>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
        @foreach($designs as $design)
            <div class="col">
                <div class="gift-card-tile h-100">
                    <img src="{{ $design->imageUrl() }}" alt="" class="gift-card-art" loading="lazy">
                    <div class="p-3 d-flex flex-column flex-grow-1">
                        <div class="fw-semibold">{{ $design->name }}</div>
                        <div class="h4 text-brand mb-3">{{ money($design->value) }}</div>
                        <button type="button" class="btn btn-brand btn-sm mt-auto" data-bs-toggle="modal" data-bs-target="#sendGift"
                                data-design-id="{{ $design->id }}" data-design-label="{{ $design->name }} — {{ money($design->value) }}">
                            <i class="bi bi-gift me-1" aria-hidden="true"></i> Send as Gift
                        </button>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="col">
            <div class="gift-card-tile h-100">
                <div class="gift-card-art gift-card-custom" aria-hidden="true"><i class="bi bi-pencil-square"></i></div>
                <div class="p-3 d-flex flex-column flex-grow-1">
                    <div class="fw-semibold">Custom amount</div>
                    <div class="small text-muted mb-3">Any amount from {{ money($customMin * 100) }} to {{ money($customMax * 100) }}</div>
                    <button type="button" class="btn btn-outline-brand btn-sm mt-auto" data-bs-toggle="modal" data-bs-target="#sendGift"
                            data-design-id="" data-design-label="Custom amount">
                        <i class="bi bi-gift me-1" aria-hidden="true"></i> Send as Gift
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sendGift" tabindex="-1" aria-labelledby="sendGiftTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('cart.gift-card.add') }}" class="modal-content">
            @csrf
            <input type="hidden" name="design_id" value="{{ old('design_id') }}" data-design-input>
            <div class="modal-header">
                <h2 class="modal-title h5" id="sendGiftTitle">Send as Gift: <span data-design-label>{{ old('design_id') ? '' : 'Custom amount' }}</span></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($errors->any())
                    <div class="alert alert-danger small">{{ $errors->first() }}</div>
                @endif
                <div class="mb-3" data-amount-row>
                    <label for="giftAmount" class="form-label small fw-semibold">Amount (₹)</label>
                    <input type="number" name="amount" id="giftAmount" class="form-control" min="{{ $customMin }}" max="{{ $customMax }}" step="1" value="{{ old('amount') }}">
                </div>
                <div class="row g-2">
                    <div class="col-sm-6">
                        <label for="recipientName" class="form-label small fw-semibold">Recipient's name</label>
                        <input name="recipient_name" id="recipientName" class="form-control" maxlength="100" value="{{ old('recipient_name') }}">
                    </div>
                    <div class="col-sm-6">
                        <label for="recipientEmail" class="form-label small fw-semibold">Recipient's email</label>
                        <input type="email" name="recipient_email" id="recipientEmail" class="form-control" maxlength="150" value="{{ old('recipient_email') }}">
                    </div>
                </div>
                <div class="form-text mb-3">Leave the email blank to receive the code yourself.</div>
                <div class="mb-3">
                    <label for="giftMessage" class="form-label small fw-semibold">Message <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea name="message" id="giftMessage" class="form-control" rows="3" maxlength="300" placeholder="Happy birthday! Enjoy a few good books.">{{ old('message') }}</textarea>
                </div>
                <div>
                    <label for="senderName" class="form-label small fw-semibold">From</label>
                    <input name="sender_name" id="senderName" class="form-control" maxlength="100" value="{{ old('sender_name', auth()->user()?->name) }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-brand w-100">Add to Cart</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var modal = document.getElementById('sendGift');
    var designInput = modal.querySelector('[data-design-input]');
    var amountRow = modal.querySelector('[data-amount-row]');
    var amount = document.getElementById('giftAmount');
    function setDesign(id, label) {
        designInput.value = id;
        modal.querySelector('[data-design-label]').textContent = label;
        amountRow.hidden = !!id;      // designs have a fixed value
        amount.required = !id;
    }
    modal.addEventListener('show.bs.modal', function (e) {
        if (e.relatedTarget) setDesign(e.relatedTarget.dataset.designId, e.relatedTarget.dataset.designLabel);
    });
    @if($errors->any())
        // Re-open with the visitor's answers after a validation error.
        setDesign(designInput.value, designInput.value ? 'your gift card' : 'Custom amount');
        bootstrap.Modal.getOrCreateInstance(modal).show();
    @endif
})();
</script>
@endpush
