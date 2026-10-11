<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Book $book)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $book->reviews()->updateOrCreate(
            ['user_id' => Auth::id()],
            [...$data, 'is_approved' => false]
        );

        return back()->with('status', 'Thanks! Your review has been submitted and will appear once it\'s approved.');
    }
}
