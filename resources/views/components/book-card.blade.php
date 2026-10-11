@php
    $variant = $book->primaryVariant();
@endphp
<div class="book-card">
    <div class="cover-wrap">
        <a href="{{ route('books.show', $book) }}">
            <img src="{{ $book->coverUrl() }}" alt="{{ $book->title }}" loading="lazy">
        </a>
        @if($variant && $variant->discountPercent() > 0)
            <span class="discount-badge">{{ $variant->discountPercent() }}% off</span>
        @endif
        @auth
            <form method="POST" action="{{ route('wishlist.toggle', $book) }}" data-ajax-wishlist>
                @csrf
                <button type="submit" class="wishlist-btn @if(in_array($book->id, $wishlistedBookIds ?? [])) active @endif" title="Save to wishlist">
                    <i class="bi bi-heart{{ (in_array($book->id, $wishlistedBookIds ?? [])) ? '-fill' : '' }}"></i>
                </button>
            </form>
        @endauth
    </div>

    <div class="p-2">
        <a href="{{ route('books.show', $book) }}" class="text-decoration-none">
            <div class="title">{{ $book->title }}</div>
        </a>
        <div class="author">{{ $book->authorNames() }}</div>

        @if($book->reviewCount() > 0)
            <div class="rating">
                <i class="bi bi-star-fill"></i> {{ number_format($book->averageRating(), 1) }}
            </div>
        @endif

        <div class="price mt-1">
            @if($variant)
                <span class="final">{{ money($variant->price) }}</span>
                @if($variant->compare_at_price)
                    <span class="mrp ms-1">{{ money($variant->compare_at_price) }}</span>
                @endif
            @else
                <span class="text-muted small">Currently unavailable</span>
            @endif
        </div>

        <a href="{{ route('books.show', $book) }}" class="btn btn-outline-brand btn-sm w-100 mt-2">View Details</a>
    </div>
</div>
