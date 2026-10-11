@php
    $variant = $book->primaryVariant();
    $wishlisted = in_array($book->id, $wishlistedBookIds ?? []);
@endphp
<article class="book-card">
    <div class="cover-wrap">
        <img src="{{ $book->coverUrl() }}" alt="" loading="lazy">
        @if($variant && $variant->discountPercent() > 0)
            <span class="discount-badge">{{ $variant->discountPercent() }}% off</span>
        @endif
        @auth
            <form method="POST" action="{{ route('wishlist.toggle', $book) }}" data-ajax-wishlist>
                @csrf
                <button type="submit" class="wishlist-btn @if($wishlisted) active @endif" aria-pressed="{{ $wishlisted ? 'true' : 'false' }}" aria-label="Save {{ $book->title }} to wishlist" title="Save to wishlist">
                    <i class="bi bi-heart{{ $wishlisted ? '-fill' : '' }}" aria-hidden="true"></i>
                </button>
            </form>
        @endauth
    </div>

    <div class="card-body-text">
        {{-- The title link is stretched over the whole card, so the cover and text are one target. --}}
        <a href="{{ route('books.show', $book) }}" class="title-link">
            <h3 class="title">{{ $book->title }}</h3>
        </a>
        <div class="author">{{ $book->authorNames() }}</div>

        @if($book->reviewCount() > 0)
            <div class="rating"><i class="bi bi-star-fill" aria-hidden="true"></i> {{ number_format($book->averageRating(), 1) }}</div>
        @endif

        <div class="price">
            @if($variant)
                <span class="final">{{ money($variant->price) }}</span>
                @if($variant->compare_at_price)
                    <span class="mrp"><span class="visually-hidden">Was </span>{{ money($variant->compare_at_price) }}</span>
                @endif
            @else
                <span class="unavailable">Currently unavailable</span>
            @endif
        </div>
    </div>
</article>
