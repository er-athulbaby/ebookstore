<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $items = Wishlist::where('user_id', Auth::id())
            ->with(['book.authors', 'book.images', 'book.activeVariants'])
            ->latest()
            ->get();

        return view('account.wishlist', compact('items'));
    }

    public function toggle(Book $book)
    {
        $existing = Wishlist::where('user_id', Auth::id())->where('book_id', $book->id)->first();

        if ($existing) {
            $existing->delete();
            $added = false;
        } else {
            Wishlist::create(['user_id' => Auth::id(), 'book_id' => $book->id]);
            $added = true;
        }

        if (request()->wantsJson()) {
            return response()->json(['added' => $added]);
        }

        return back()->with('status', $added ? 'Added to your wishlist.' : 'Removed from your wishlist.');
    }
}
