@extends('layouts.app')

@section('title', $heading.' — '.config('redcherry.store_name'))

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <div class="section-eyebrow">Catalog</div>
        <h1 class="section-title h2 mb-1">{{ $heading }}</h1>
        @if($subheading ?? null)
            <p class="text-muted mb-0">{{ $subheading }}</p>
        @endif
        <div class="text-muted small mt-1">{{ $books->total() }} books</div>
    </div>

    <div class="row">
        <div class="col-lg-3 mb-4">
            <form method="GET" id="filterForm">
                <div class="filter-block">
                    <h6>Language</h6>
                    <select name="language" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Languages</option>
                        @foreach(['Malayalam','English','Hindi'] as $lang)
                            <option value="{{ $lang }}" @selected(request('language') === $lang)>{{ $lang }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-block">
                    <h6>Price Range</h6>
                    <select name="price" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                        <option value="">Any Price</option>
                        <option value="under_200" @selected(request('price') === 'under_200')>Under {{ money(20000) }}</option>
                        <option value="200_500" @selected(request('price') === '200_500')>{{ money(20000) }} – {{ money(50000) }}</option>
                        <option value="above_500" @selected(request('price') === 'above_500')>Above {{ money(50000) }}</option>
                    </select>
                </div>
                <div class="filter-block">
                    <h6>Format</h6>
                    <select name="format" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Formats</option>
                        <option value="paperback" @selected(request('format') === 'paperback')>Paperback</option>
                        <option value="hardcover" @selected(request('format') === 'hardcover')>Hardcover</option>
                        <option value="ebook" @selected(request('format') === 'ebook')>eBook</option>
                    </select>
                </div>
                @if(request()->hasAny(['language', 'price', 'format']))
                    <a href="{{ url()->current() }}" class="small">Clear all filters</a>
                @endif
                <input type="hidden" name="sort" value="{{ request('sort') }}">
                @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
            </form>
        </div>

        <div class="col-lg-9">
            <div class="d-flex justify-content-end mb-3">
                <form method="GET" id="sortForm">
                    @foreach(['language','price','format','q'] as $key)
                        @if(request($key)) <input type="hidden" name="{{ $key }}" value="{{ request($key) }}"> @endif
                    @endforeach
                    <select name="sort" class="form-select form-select-sm" style="width:auto" onchange="document.getElementById('sortForm').submit()">
                        <option value="relevance" @selected(!request('sort') || request('sort') === 'relevance')>Sort by: Relevance</option>
                        <option value="newest" @selected(request('sort') === 'newest')>Newest First</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: Low to High</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: High to Low</option>
                        <option value="best_sellers" @selected(request('sort') === 'best_sellers')>Best Sellers</option>
                        <option value="top_rated" @selected(request('sort') === 'top_rated')>Top Rated</option>
                    </select>
                </form>
            </div>

            @if($books->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-book fs-1"></i>
                    <p class="mt-2">No books match these filters yet.</p>
                </div>
            @else
                <div class="row row-cols-2 row-cols-md-3 g-3">
                    @foreach($books as $book)
                        <div class="col"><x-book-card :book="$book" /></div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $books->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
