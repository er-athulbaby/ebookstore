@extends('layouts.app')

@section('title', 'Login — '.config('redcherry.store_name'))

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <h1 class="h4 mb-4 text-center">Login to RedCherry Books</h1>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ request('redirect_to') }}">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-brand w-100">Login</button>
            </form>
            <p class="text-center mt-3 small">New here? <a href="{{ route('register') }}?redirect_to={{ urlencode(request('redirect_to', '')) }}">Create an account</a></p>
        </div>
    </div>
</div>
@endsection
