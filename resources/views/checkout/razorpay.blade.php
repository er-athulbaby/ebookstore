@extends('layouts.app')

@section('title', 'Complete Payment — '.config('redcherry.store_name'))

@section('content')
<div class="container py-5 text-center">
    <div class="spinner-border text-brand mb-3" role="status"></div>
    <h1 class="h5">Redirecting you to secure payment…</h1>
    <p class="text-muted">Order {{ $order->order_number }} &middot; {{ money($order->total) }}</p>
    <p class="small"><a href="#" onclick="document.getElementById('rzp-trigger').click(); return false;">Payment window didn't open? Click here.</a></p>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    var options = {
        key: @json($razorpayKey),
        amount: @json($order->total),
        currency: "INR",
        name: @json(config('redcherry.store_name')),
        description: @json('Order '.$order->order_number),
        order_id: @json($payment->provider_order_id),
        prefill: {
            name: @json(auth()->user()->name),
            email: @json(auth()->user()->email),
            contact: @json(auth()->user()->phone),
        },
        theme: { color: "#ed1c24" },
        handler: function (response) {
            fetch(@json(route('checkout.razorpay.verify')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'Accept': 'application/json',
                },
                body: JSON.stringify(response),
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        alert(data.message || 'Payment verification failed. Please contact support with your order number.');
                    }
                });
        },
        modal: {
            ondismiss: function () {
                window.location.href = @json(route('checkout.index'));
            },
        },
    };
    var rzp = new Razorpay(options);
    document.addEventListener('DOMContentLoaded', function () {
        rzp.open();
    });
</script>
<button id="rzp-trigger" hidden onclick="rzp.open()"></button>
@endsection
