@extends('layouts.app')

@section('title', 'Wishlist — '.config('redcherry.store_name'))

@section('content')
<div class="container py-4">
    <h1 class="h4 mb-4">My Wishlist</h1>
    <div class="row">
        <div class="col-md-3">@include('account._nav')</div>
        <div class="col-md-9">
            @if($items->isEmpty())
                <p class="text-muted">Your wishlist is empty. <a href="{{ route('home') }}">Browse books</a>.</p>
            @else
                <div class="row row-cols-2 row-cols-md-3 g-3">
                    @foreach($items as $item)
                        <div class="col"><x-book-card :book="$item->book" /></div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
