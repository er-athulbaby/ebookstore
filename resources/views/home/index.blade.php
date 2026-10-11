@extends('layouts.app')

@section('title', config('redcherry.store_name').' — Physical Books & eBooks, Delivered Across India')

@section('content')

    {{-- Hero --}}
    @if($banners->isNotEmpty())
        <div id="heroCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
            <div class="carousel-indicators">
                @foreach($banners as $banner)
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $loop->index }}" @if($loop->first) class="active" aria-current="true" @endif aria-label="Slide {{ $loop->iteration }}"></button>
                @endforeach
            </div>
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
    @else
        <div class="bg-brand-soft py-5 mb-4">
            <div class="container text-center py-4">
                <div class="section-eyebrow">The Publishing Company Since 2014</div>
                <h1 class="section-title display-5 mt-3">Books worth shelving — in print or on your screen</h1>
                <p class="text-muted col-lg-6 mx-auto mt-3">Browse our curated catalog of paperbacks, hardcovers and instant-download eBooks from RedCherry Books.</p>
                <a href="{{ route('catalog.top100') }}" class="btn btn-brand btn-lg mt-2">Explore Top 100</a>
            </div>
        </div>
    @endif

    <div class="container">

        {{-- New Releases --}}
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-end mb-3">
                <div>
                    <div class="section-eyebrow">Just Arrived</div>
                    <h2 class="section-title h3 mb-0">New Releases</h2>
                </div>
                <a href="{{ route('catalog.new-releases') }}" class="btn btn-outline-brand btn-sm">View All New Releases</a>
            </div>
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
                @foreach($newReleases as $book)
                    <div class="col"><x-book-card :book="$book" /></div>
                @endforeach
            </div>
        </section>

        {{-- Best Sellers --}}
        @if($bestsellers->isNotEmpty())
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-end mb-3">
                <div>
                    <div class="section-eyebrow">Most Loved</div>
                    <h2 class="section-title h3 mb-0">Best Sellers</h2>
                </div>
                <a href="{{ route('catalog.bestsellers') }}" class="btn btn-outline-brand btn-sm">View All Best Sellers</a>
            </div>
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
                @foreach($bestsellers as $book)
                    <div class="col"><x-book-card :book="$book" /></div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Categories --}}
        <section class="mb-5">
            <div class="section-eyebrow">Browse by Genre</div>
            <h2 class="section-title h3 mb-3">Top Categories</h2>
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
                @foreach($categories as $category)
                    <a href="{{ route('catalog.category', $category) }}" class="text-decoration-none">
                        <div class="border rounded p-3 text-center h-100">
                            <div class="fw-semibold">{{ $category->name }}</div>
                            <div class="text-muted small">{{ $category->books_count }}+ books</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Authors --}}
        @if($authors->isNotEmpty())
        <section class="mb-5">
            <div class="section-eyebrow">Browse By</div>
            <h2 class="section-title h3 mb-3">Browse By Authors</h2>
            <div class="d-flex flex-wrap gap-2">
                @foreach($authors as $author)
                    <a href="{{ route('catalog.author', $author) }}" class="btn btn-sm btn-outline-secondary">{{ $author->name }}</a>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Top 100 --}}
        @if($top100->isNotEmpty())
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-end mb-3">
                <div>
                    <div class="section-eyebrow">Curated List</div>
                    <h2 class="section-title h3 mb-0">RedCherry Top 100</h2>
                </div>
                <a href="{{ route('catalog.top100') }}" class="btn btn-outline-brand btn-sm">View All Top 100</a>
            </div>
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
                @foreach($top100 as $book)
                    <div class="col"><x-book-card :book="$book" /></div>
                @endforeach
            </div>
        </section>
        @endif
    </div>

    <div class="stats-strip py-4">
        <div class="container">
            <div class="row text-center g-3">
                <div class="col-6 col-md-3"><div class="stat-num">50K+</div><div class="stat-label">Books Available</div></div>
                <div class="col-6 col-md-3"><div class="stat-num">2M+</div><div class="stat-label">Happy Readers</div></div>
                <div class="col-6 col-md-3"><div class="stat-num">1200+</div><div class="stat-label">Authors Listed</div></div>
                <div class="col-6 col-md-3"><div class="stat-num">4.8★</div><div class="stat-label">Average Rating</div></div>
            </div>
        </div>
    </div>

@endsection

