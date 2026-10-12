@extends('layouts.app')

@section('title', $heading.' — '.config('redcherry.store_name'))

@php
    // query(), not request(): request('author') falls back to the route-bound model on /author/{author}.
    $selCategories = array_filter((array) request()->query('category', []), 'is_string');
    $selAuthors = array_filter((array) request()->query('author', []), 'is_string');
    $priceLabels = [
        'under_200' => 'Under '.money(20000),
        '200_500' => money(20000).' – '.money(50000),
        'above_500' => 'Above '.money(50000),
    ];
    $formatLabels = ['paperback' => 'Paperback', 'hardcover' => 'Hardcover', 'ebook' => 'eBook'];

    // Chips for every active filter, each linking to the same page without that value.
    $chips = [];
    foreach ($selCategories as $slug) {
        $name = optional($filterCategories->firstWhere('slug', $slug))->name ?? $slug;
        $chips[] = [$name, request()->fullUrlWithQuery(['category' => array_values(array_diff($selCategories, [$slug])), 'page' => null])];
    }
    foreach ($selAuthors as $slug) {
        $name = optional($filterAuthors->firstWhere('slug', $slug))->name ?? $slug;
        $chips[] = [$name, request()->fullUrlWithQuery(['author' => array_values(array_diff($selAuthors, [$slug])), 'page' => null])];
    }
    if (request('language')) $chips[] = [request('language'), request()->fullUrlWithQuery(['language' => null, 'page' => null])];
    if (isset($priceLabels[request('price')])) $chips[] = [$priceLabels[request('price')], request()->fullUrlWithQuery(['price' => null, 'page' => null])];
    if (isset($formatLabels[request('format')])) $chips[] = [$formatLabels[request('format')], request()->fullUrlWithQuery(['format' => null, 'page' => null])];
    $clearUrl = url()->current().(request('q') ? '?'.http_build_query(['q' => request('q')]) : '');
@endphp

@section('content')
<div class="container py-4">
    @if(($context ?? null) === 'author' && isset($author))
        <div class="author-header mb-4">
            @if($author->photoUrl())
                <img src="{{ $author->photoUrl() }}" alt="{{ $author->name }}" class="author-header-photo" width="96" height="96">
            @else
                <span class="author-header-photo author-monogram" aria-hidden="true">{{ collect(explode(' ', $author->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('') }}</span>
            @endif
            <div>
                <h1 class="section-title h2 mb-1">{{ $author->name }}</h1>
                @if($author->bio)<p class="text-muted mb-1">{{ $author->bio }}</p>@endif
                <div class="text-muted small">{{ $books->total() }} {{ \Illuminate\Support\Str::plural('book', $books->total()) }}</div>
            </div>
        </div>
    @else
        <div class="mb-4">
            <h1 class="section-title h2 mb-1">{{ $heading }}</h1>
            @if($subheading ?? null)
                <p class="text-muted mb-0">{{ $subheading }}</p>
            @endif
            <div class="text-muted small mt-1">{{ $books->total() }} {{ \Illuminate\Support\Str::plural('book', $books->total()) }}</div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-3 mb-4">
            <button class="btn btn-outline-brand w-100 d-lg-none mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel" aria-expanded="false" aria-controls="filterPanel">
                <i class="bi bi-sliders" aria-hidden="true"></i> Filters @if(count($chips)) ({{ count($chips) }}) @endif
            </button>
            <div class="collapse d-lg-block" id="filterPanel">
                <form method="GET" id="filterForm" class="filter-panel">
                    @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
                    @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif

                    {{-- Multi-select groups fold into dropdowns; they open by themselves while a value is ticked. --}}
                    @if(($context ?? null) !== 'category' && $filterCategories->isNotEmpty())
                        <details class="filter-block filter-drop" @if($selCategories) open @endif>
                            <summary>Category @if($selCategories)<span class="filter-sel">{{ count($selCategories) }}</span>@endif</summary>
                            <div class="filter-scroll">
                                @foreach($filterCategories as $cat)
                                    <label class="filter-check">
                                        <input type="checkbox" name="category[]" value="{{ $cat->slug }}" @checked(in_array($cat->slug, $selCategories)) data-autosubmit>
                                        <span>{{ $cat->name }}</span>
                                        <span class="filter-count">{{ $cat->books_count }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </details>
                    @endif

                    @if(($context ?? null) !== 'author' && $filterAuthors->isNotEmpty())
                        <details class="filter-block filter-drop" @if($selAuthors) open @endif>
                            <summary>Author @if($selAuthors)<span class="filter-sel">{{ count($selAuthors) }}</span>@endif</summary>
                            <div class="filter-scroll">
                                @foreach($filterAuthors as $a)
                                    <label class="filter-check">
                                        <input type="checkbox" name="author[]" value="{{ $a->slug }}" @checked(in_array($a->slug, $selAuthors)) data-autosubmit>
                                        <span>{{ $a->name }}</span>
                                        <span class="filter-count">{{ $a->books_count }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </details>
                    @endif

                    @if($filterLanguages->count() > 1)
                        <div class="filter-block">
                            <label class="filter-label" for="filterLanguage">Language</label>
                            <select name="language" id="filterLanguage" class="form-select form-select-sm" data-autosubmit>
                                <option value="">All languages</option>
                                @foreach($filterLanguages as $lang)
                                    <option value="{{ $lang }}" @selected(request('language') === $lang)>{{ $lang }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="filter-block">
                        <label class="filter-label" for="filterPrice">Price</label>
                        <select name="price" id="filterPrice" class="form-select form-select-sm" data-autosubmit>
                            <option value="">Any price</option>
                            @foreach($priceLabels as $value => $label)
                                <option value="{{ $value }}" @selected(request('price') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-block">
                        <label class="filter-label" for="filterFormat">Format</label>
                        <select name="format" id="filterFormat" class="form-select form-select-sm" data-autosubmit>
                            <option value="">All formats</option>
                            @foreach($formatLabels as $value => $label)
                                <option value="{{ $value }}" @selected(request('format') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <noscript><button type="submit" class="btn btn-brand btn-sm w-100">Apply filters</button></noscript>
                </form>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="filter-chips">
                    @foreach($chips as [$label, $url])
                        <a href="{{ $url }}" class="filter-chip" aria-label="Remove filter {{ $label }}">{{ $label }} <i class="bi bi-x" aria-hidden="true"></i></a>
                    @endforeach
                    @if(count($chips) > 1)
                        <a href="{{ $clearUrl }}" class="filter-clear">Clear all</a>
                    @endif
                </div>
                <form method="GET" id="sortForm" class="ms-auto">
                    @foreach(request()->except(['sort', 'page']) as $key => $value)
                        @foreach((array) $value as $v)
                            @if(is_string($v) && $v !== '')
                                <input type="hidden" name="{{ is_array($value) ? $key.'[]' : $key }}" value="{{ $v }}">
                            @endif
                        @endforeach
                    @endforeach
                    <select name="sort" class="form-select form-select-sm" style="width:auto" data-autosubmit aria-label="Sort books">
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
                    <i class="bi bi-book fs-1" aria-hidden="true"></i>
                    <p class="mt-2 mb-2">No books match these filters.</p>
                    @if(count($chips)) <a href="{{ $clearUrl }}" class="btn btn-outline-brand btn-sm">Clear filters</a> @endif
                </div>
            @else
                <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-4">
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

@push('scripts')
<script>
    // Apply a filter as soon as it changes; the <noscript> button covers no-JS visitors.
    document.querySelectorAll('[data-autosubmit]').forEach(function (el) {
        el.addEventListener('change', function () { el.form.submit(); });
    });
</script>
@endpush
