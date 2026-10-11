<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    public function show(Book $book)
    {
        abort_unless($book->status === 'published', 404);

        $book->load(['authors', 'images', 'activeVariants', 'category']);
        $book->increment('view_count');

        $related = Book::published()
            ->where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->with(['authors', 'images', 'activeVariants'])
            ->inRandomOrder()
            ->take(10)
            ->get();

        $reviews = $book->approvedReviews()->with('user')->latest()->take(20)->get();

        $userReview = Auth::check()
            ? $book->reviews()->where('user_id', Auth::id())->first()
            : null;

        $inWishlist = Auth::check()
            ? $book->id && \App\Models\Wishlist::where('user_id', Auth::id())->where('book_id', $book->id)->exists()
            : false;

        return view('books.show', compact('book', 'related', 'reviews', 'userReview', 'inWishlist'));
    }
}
