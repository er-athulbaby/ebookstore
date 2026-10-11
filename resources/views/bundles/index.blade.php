@extends('layouts.app')

@section('title', 'Bundle Offers — '.config('redcherry.store_name'))

@section('content')
<div class="container py-4">
    <div class="section-eyebrow">Save More</div>
    <h1 class="section-title h3 mb-4">Bundle Offers</h1>

    @if($bundles->isEmpty())
        <p class="text-muted">No bundle offers right now — check back soon.</p>
    @else
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach($bundles as $bundle)
                <div class="col">
                    <div class="book-card h-100 d-flex flex-column">
                        <div class="cover-wrap">
                            <img src="{{ $bundle->coverUrl() }}" alt="{{ $bundle->name }}" loading="lazy">
                            @if($bundle->savings() > 0)
                                <span class="discount-badge">Save {{ money($bundle->savings()) }}</span>
                            @endif
                        </div>
                        <div class="p-2 d-flex flex-column flex-grow-1">
                            <div class="title">{{ $bundle->name }}</div>
                            <ul class="text-muted small mb-2 ps-3">
                                @foreach($bundle->variants as $variant)
                                    <li>{{ $variant->book->title }} ({{ $variant->typeLabel() }})</li>
                                @endforeach
                            </ul>
                            @if($bundle->description)
                                <p class="small text-muted">{{ $bundle->description }}</p>
                            @endif
                            <div class="price mt-auto">
                                <span class="final">{{ money($bundle->price) }}</span>
                                @if($bundle->savings() > 0)
                                    <span class="mrp ms-1">{{ money($bundle->compareAtPrice()) }}</span>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('cart.bundle.add', $bundle) }}" class="mt-2">
                                @csrf
                                <button type="submit" class="btn btn-brand btn-sm w-100">Add Bundle to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
