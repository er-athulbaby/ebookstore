<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Banner;
use App\Models\Book;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $banners = Banner::where('is_active', true)->orderBy('sort_order')->get();

        $eagerLoad = ['authors', 'images', 'activeVariants'];

        $newReleases = Book::published()
            ->with($eagerLoad)->withCount('approvedReviews')->withAvg('approvedReviews', 'rating')
            ->orderByDesc('published_at')
            ->take(8)
            ->get();

        $bestsellers = Book::published()
            ->where('is_bestseller', true)
            ->with($eagerLoad)->withCount('approvedReviews')->withAvg('approvedReviews', 'rating')
            ->take(8)
            ->get();

        $top100 = Book::published()
            ->where('is_top100', true)
            ->with($eagerLoad)->withCount('approvedReviews')->withAvg('approvedReviews', 'rating')
            ->take(8)
            ->get();

        $heroBooks = Book::published()->where('is_featured', true)
            ->with($eagerLoad)->latest('updated_at')->take(5)->get();

        $categories = Category::whereNull('parent_id')
            ->withCount(['books' => fn ($q) => $q->published()])
            ->orderBy('sort_order')
            ->take(6)
            ->get();

        $authors = Author::active()->whereHas('books', fn ($q) => $q->published())
            ->withCount(['books' => fn ($q) => $q->published()])
            ->orderByDesc('books_count')
            ->take(20)
            ->get();

        return view('home.index', compact('heroBooks', 'banners', 'newReleases', 'bestsellers', 'top100', 'categories', 'authors'));
    }
}
