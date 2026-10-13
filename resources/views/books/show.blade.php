@extends('layouts.app')

@section('title', $book->title.' | '.$book->authorNames().' | '.config('redcherry.store_name'))
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags((string) $book->description), 155))

@section('content')
<div class="container py-4">

    <nav class="small text-muted mb-3">
        <a href="{{ route('home') }}">Home</a> /
        <a href="{{ route('catalog.category', $book->category) }}">{{ $book->category->name }}</a> /
        {{ $book->title }}
    </nav>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="book-gallery">
                <img src="{{ $book->coverUrl() }}" alt="{{ $book->title }}">
            </div>
        </div>

        <div class="col-md-8">
            @if($book->is_top100)
                <span class="badge bg-brand mb-2">RedCherry TOP 100</span>
            @endif
            <h1 class="h3 mb-1">{{ $book->title }}</h1>
            <div class="text-muted mb-2">By
                @foreach($book->authors as $author)
                    @if($author->is_active)
                        <a href="{{ route('catalog.author', $author) }}" class="book-author-line">@if($author->photoUrl())<img src="{{ $author->photoUrl() }}" alt="" width="32" height="32">@endif{{ $author->name }}</a>{{ !$loop->last ? ',' : '' }}
                    @else
                        {{ $author->name }}{{ !$loop->last ? ',' : '' }}
                    @endif
                @endforeach
            </div>

            @if($book->reviewCount() > 0)
                <div class="mb-2">
                    <span class="text-warning">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star{{ $i <= round($book->averageRating()) ? '-fill' : '' }}"></i>
                        @endfor
                    </span>
                    <span class="text-muted small">({{ $book->reviewCount() }} review{{ $book->reviewCount() === 1 ? '' : 's' }})</span>
                </div>
            @else
                <div class="text-muted small mb-2">No reviews yet — be the first</div>
            @endif

            <form method="POST" action="{{ route('cart.add') }}" data-ajax-cart>
                @csrf
                <div class="mb-1 fw-semibold small">FORMAT</div>
                <div class="variant-picker mb-3">
                    @foreach($book->activeVariants as $variant)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="book_variant_id" id="variant{{ $variant->id }}"
                                   value="{{ $variant->id }}" data-variant-radio data-price="{{ money($variant->price) }}"
                                   @checked($loop->first) @disabled(!$variant->inStock())>
                            <label class="form-check-label d-flex justify-content-between" for="variant{{ $variant->id }}">
                                <span>{{ $variant->typeLabel() }} @if(!$variant->inStock()) <span class="text-danger small">(Out of stock)</span> @endif</span>
                                <span>
                                    {{ money($variant->price) }}
                                    @if($variant->compare_at_price)
                                        <span class="text-muted text-decoration-line-through small ms-1">{{ money($variant->compare_at_price) }}</span>
                                    @endif
                                </span>
                            </label>
                        </div>
                    @endforeach
                </div>

                @if($book->activeVariants->isEmpty())
                    <div class="alert alert-warning">This title isn't currently available for purchase.</div>
                @else
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="fs-4 fw-bold text-brand" data-selected-price>{{ money($book->activeVariants->first()->price) }}</div>
                        <div class="text-muted small">Inclusive of all taxes</div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <div class="input-group" style="width:130px">
                            <span class="input-group-text">Qty</span>
                            <input type="number" name="quantity" value="1" min="1" max="20" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-brand px-4"><i class="bi bi-bag-plus me-1"></i> Add to Cart</button>
                        @auth
                            <button type="submit" formaction="{{ route('wishlist.toggle', $book) }}" formmethod="POST" class="btn btn-outline-secondary">
                                <i class="bi bi-heart{{ in_array($book->id, $wishlistedBookIds ?? []) ? '-fill' : '' }}"></i>
                            </button>
                        @endauth
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Details & summary --}}
    <div class="row mt-5">
        <div class="col-lg-8">
            <h2 class="h5 border-bottom pb-2 mb-3">Book Details & Summary</h2>
            <div class="row small mb-4">
                <div class="col-6 col-md-4 mb-2"><span class="text-muted">Author</span><br>{{ $book->authorNames() }}</div>
                <div class="col-6 col-md-4 mb-2"><span class="text-muted">Category</span><br>{{ $book->category->name }}</div>
                @if($book->isbn)<div class="col-6 col-md-4 mb-2"><span class="text-muted">ISBN</span><br>{{ $book->isbn }}</div>@endif
                @if($book->edition)<div class="col-6 col-md-4 mb-2"><span class="text-muted">Edition</span><br>{{ $book->edition }}</div>@endif
                @if($book->pages)<div class="col-6 col-md-4 mb-2"><span class="text-muted">Pages</span><br>{{ $book->pages }}</div>@endif
                <div class="col-6 col-md-4 mb-2"><span class="text-muted">Language</span><br>{{ $book->language }}</div>
                <div class="col-6 col-md-4 mb-2"><span class="text-muted">Publisher</span><br>{{ $book->publisher }}</div>
                @if($book->published_at)<div class="col-6 col-md-4 mb-2"><span class="text-muted">Published</span><br>{{ $book->published_at->format('d-m-Y') }}</div>@endif
            </div>
            <p>{{ $book->description }}</p>
        </div>
    </div>

    {{-- Related --}}
    @if($related->isNotEmpty())
    <div class="mt-5">
        <div class="section-eyebrow">You Might Also Like</div>
        <h2 class="section-title h4 mb-3">Related Books</h2>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
            @foreach($related as $relatedBook)
                <div class="col"><x-book-card :book="$relatedBook" /></div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Reviews --}}
    <div class="mt-5 row g-4">
        <div class="col-lg-4">
            <h2 class="h5">Customer Reviews</h2>
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="fs-3 fw-bold">{{ $book->reviewCount() > 0 ? number_format($book->averageRating(), 1) : '—' }}</span>
                <span class="text-muted">{{ $book->reviewCount() > 0 ? 'Based on '.$book->reviewCount().' reviews' : 'Be the first to review' }}</span>
            </div>
            @foreach($book->ratingBreakdown() as $star => $data)
                <div class="d-flex align-items-center gap-2 small mb-1">
                    <span style="width:36px">{{ $star }} <i class="bi bi-star-fill text-warning"></i></span>
                    <div class="star-bar flex-grow-1"><span style="width:{{ $data['percent'] }}%"></span></div>
                    <span class="text-muted" style="width:24px">{{ $data['count'] }}</span>
                </div>
            @endforeach
        </div>

        <div class="col-lg-8">
            @forelse($reviews as $review)
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $review->title ?: $review->user->name }}</strong>
                        <span class="text-warning small">
                            @for($i = 1; $i <= 5; $i++)<i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>@endfor
                        </span>
                    </div>
                    <div class="text-muted small mb-1">{{ $review->user->name }} &middot; {{ $review->created_at->format('d M Y') }}</div>
                    <p class="mb-0">{{ $review->body }}</p>
                </div>
            @empty
                <p class="text-muted">No reviews yet. Be the first to review this book!</p>
            @endforelse

            @auth
                @if(!$userReview)
                <h3 class="h6 mt-4">Write a Review</h3>
                <form method="POST" action="{{ route('reviews.store', $book) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">Your Rating *</label>
                        <select name="rating" class="form-select form-select-sm" style="width:auto" required>
                            @for($i = 5; $i >= 1; $i--)<option value="{{ $i }}">{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>@endfor
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Review Title</label>
                        <input type="text" name="title" class="form-control form-control-sm" maxlength="150">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Your Review *</label>
                        <textarea name="body" class="form-control form-control-sm" rows="3" required></textarea>
                    </div>
                    <button class="btn btn-brand btn-sm">Submit Review</button>
                </form>
                @else
                    <div class="alert alert-info small mt-3">You've already reviewed this book — thanks!</div>
                @endif
            @else
                <a href="{{ route('login') }}?redirect_to={{ urlencode(route('books.show', $book, false)) }}" class="small">Login to submit a review.</a>
            @endauth
        </div>
    </div>
</div>
@endsection
