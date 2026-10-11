@extends('layouts.app')

@section('title', config('redcherry.store_name').' — Physical Books & eBooks, Delivered Across India')

@section('content')
@php
    $categories = $categories->where('books_count', '>', 0); // hide empty shelves

    // Hero shelf: books with real cover art first, then the rest (presentation only).
    $heroBooks = $newReleases->concat($bestsellers)->concat($top100)->unique('id')
        ->sortByDesc(fn ($b) => $b->images->isNotEmpty())->take(5)->values();

    $categoryIcon = function (string $name): string {
        $n = strtolower($name);
        return match (true) {
            str_contains($n, 'child') => 'bi-balloon',
            str_contains($n, 'non') => 'bi-journal-text',
            str_contains($n, 'fiction') => 'bi-book',
            str_contains($n, 'business') || str_contains($n, 'finance') => 'bi-graph-up-arrow',
            str_contains($n, 'self') => 'bi-lightbulb',
            str_contains($n, 'history') => 'bi-hourglass-split',
            str_contains($n, 'poet') => 'bi-feather',
            default => 'bi-bookmark',
        };
    };
@endphp

    {{-- Hero --}}
    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-copy">
                <h1 class="hero-title">Discover your next great read</h1>
                <p class="hero-text">Browse our curated catalog of paperbacks, hardcovers and instant-download eBooks from RedCherry Books.</p>
                <div class="hero-actions">
                    <a href="{{ route('search') }}" class="btn btn-brand btn-lg">Shop Books</a>
                    <a href="{{ route('catalog.top100') }}" class="link-arrow">Explore Top 100</a>
                </div>
            </div>

            @if($heroBooks->isNotEmpty())
                <div class="hero-shelf" aria-label="Featured books">
                    @foreach($heroBooks as $book)
                        <a href="{{ route('books.show', $book) }}" class="hero-book hero-book-{{ $loop->iteration }}" data-hero-book title="{{ $book->title }}">
                            <img src="{{ $book->coverUrl() }}" alt="{{ $book->title }} by {{ $book->authorNames() }}" decoding="async">
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- New Releases --}}
    <section class="home-section">
        <div class="container">
            <div class="section-head">
                <div>
                    <h2 class="section-title">New Releases</h2>
                    <p class="section-sub">The latest additions to our catalog.</p>
                </div>
                <a href="{{ route('catalog.new-releases') }}" class="link-arrow">View All New Releases</a>
            </div>
            <div class="book-rail">
                @foreach($newReleases as $book)
                    <x-book-card :book="$book" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- Promotional banners (managed in /admin → Banners) --}}
    @if($banners->isNotEmpty())
        <section class="promo-section">
            <div class="container">
                <div id="heroCarousel" class="carousel slide promo-carousel" data-bs-ride="carousel">
                    @if($banners->count() > 1)
                        <div class="carousel-indicators">
                            @foreach($banners as $banner)
                                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $loop->index }}" @if($loop->first) class="active" aria-current="true" @endif aria-label="Slide {{ $loop->iteration }}"></button>
                            @endforeach
                        </div>
                    @endif
                    <div class="carousel-inner">
                        @foreach($banners as $banner)
                            <div class="carousel-item @if($loop->first) active @endif">
                                @if($banner->link_url)<a href="{{ $banner->link_url }}">@endif
                                    <img src="{{ $banner->url() }}" class="d-block w-100 hero-banner-img" alt="{{ $banner->title ?? config('redcherry.store_name') }}">
                                @if($banner->link_url)</a>@endif
                            </div>
                        @endforeach
                    </div>
                    @if($banners->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- Best Sellers --}}
    @if($bestsellers->isNotEmpty())
    <section class="home-section section-tinted">
        <div class="container">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Best Sellers</h2>
                    <p class="section-sub">The titles readers are choosing most.</p>
                </div>
                <a href="{{ route('catalog.bestsellers') }}" class="link-arrow">View All Best Sellers</a>
            </div>
            <div class="book-rail">
                @foreach($bestsellers as $book)
                    <x-book-card :book="$book" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Categories --}}
    @if($categories->isNotEmpty())
    <section class="home-section">
        <div class="container">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Top Categories</h2>
                </div>
            </div>
            <div class="category-grid">
                @foreach($categories as $category)
                    <a href="{{ route('catalog.category', $category) }}" class="category-tile">
                        <span class="category-icon"><i class="bi {{ $categoryIcon($category->name) }}"></i></span>
                        <span class="category-name">{{ $category->name }}</span>
                        <span class="category-count">{{ $category->books_count }}+ books</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Authors --}}
    @if($authors->isNotEmpty())
    <section class="home-section">
        <div class="container">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Browse By Authors</h2>
                </div>
            </div>
            <div class="author-grid">
                @foreach($authors as $author)
                    <a href="{{ route('catalog.author', $author) }}" class="author-tile">
                        @if($author->photoUrl())
                            <img src="{{ $author->photoUrl() }}" alt="" class="author-monogram" width="44" height="44" loading="lazy">
                        @else
                            <span class="author-monogram" aria-hidden="true">{{ collect(explode(' ', $author->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('') }}</span>
                        @endif
                        <span class="author-meta">
                            <span class="author-name">{{ $author->name }}</span>
                            <span class="author-count">{{ $author->books_count }} {{ \Illuminate\Support\Str::plural('book', $author->books_count) }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Top 100 --}}
    @if($top100->isNotEmpty())
    <section class="home-section top100-band">
        <div class="container">
            <div class="section-head">
                <div>
                    <h2 class="section-title">RedCherry Top 100</h2>
                    <p class="section-sub">The definitive reading list — books every shelf deserves.</p>
                </div>
                <a href="{{ route('catalog.top100') }}" class="btn btn-light">View All Top 100</a>
            </div>
            <div class="book-rail">
                @foreach($top100 as $book)
                    <x-book-card :book="$book" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <section class="stats-strip">
        <div class="container stats-grid">
            <div><div class="stat-num">50K+</div><div class="stat-label">Books Available</div></div>
            <div><div class="stat-num">2M+</div><div class="stat-label">Happy Readers</div></div>
            <div><div class="stat-num">1200+</div><div class="stat-label">Authors Listed</div></div>
            <div><div class="stat-num">4.8★</div><div class="stat-label">Average Rating</div></div>
        </div>
    </section>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script>
(function () {
    // One orchestrated moment: the cover fan settles in on load. Nothing else animates on its own.
    if (typeof gsap === 'undefined' || matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var books = gsap.utils.toArray('[data-hero-book]');
    if (!books.length) return;
    gsap.from(books, {
        y: 48, autoAlpha: 0, duration: 1, ease: 'power3.out', delay: .1,
        stagger: { each: .09, from: 'center' },
        clearProps: 'transform,opacity,visibility'
    });
})();
</script>
@endpush
