@php
    $variant = $book->primaryVariant();
@endphp
<article class="book-card">
    <div class="cover-wrap">
        <a href="{{ route('books.show', $book) }}" tabindex="-1" aria-hidden="true">
            <img src="{{ $book->coverUrl() }}" alt="" loading="lazy">
        </a>
        @if($variant && $variant->discountPercent() > 0)
            <span class="discount-badge">−{{ $variant->discountPercent() }}%</span>
        @endif
        @auth
            <form method="POST" action="{{ route('wishlist.toggle', $book) }}" data-ajax-wishlist>
                @csrf
                <button type="submit" class="wishlist-btn @if(in_array($book->id, $wishlistedBookIds ?? [])) active @endif" aria-label="Save {{ $book->title }} to wishlist">
                    <i class="bi bi-heart{{ (in_array($book->id, $wishlistedBookIds ?? [])) ? '-fill' : '' }}"></i>
                </button>
            </form>
        @endauth
    </div>

    <div class="body">
        <a href="{{ route('books.show', $book) }}">
            <h3 class="title">{{ $book->title }}</h3>
        </a>
        <div class="author">{{ $book->authorNames() }}</div>

        @if($book->reviewCount() > 0)
            <div class="rating"><i class="bi bi-star-fill"></i> {{ number_format($book->averageRating(), 1) }}</div>
        @endif

        <div class="price">
            @if($variant)
                <span class="final">{{ money($variant->price) }}</span>
                @if($variant->compare_at_price)
                    <span class="mrp ms-1">{{ money($variant->compare_at_price) }}</span>
                @endif
            @else
                <span class="text-muted small">Currently unavailable</span>
            @endif
        </div>
    </div>
</article>
