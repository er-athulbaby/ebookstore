<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class CatalogController extends Controller
{
    private const PER_PAGE = 24;

    public function category(Request $request, Category $category)
    {
        $childIds = $category->children()->pluck('id');
        $categoryIds = $childIds->push($category->id);

        $query = Book::published()->whereIn('category_id', $categoryIds);
        return $this->listing($request, $query, [
            'heading' => $category->name,
            'subheading' => $category->description,
            'context' => 'category',
        ]);
    }

    public function author(Request $request, Author $author)
    {
        abort_unless($author->is_active, 404);
        $query = Book::published()->whereHas('authors', fn (Builder $q) => $q->where('authors.id', $author->id));
        return $this->listing($request, $query, [
            'heading' => $author->name,
            'subheading' => 'Books by '.$author->name,
            'context' => 'author',
            'author' => $author,
        ]);
    }

    public function top100(Request $request)
    {
        $query = Book::published()->where('is_top100', true);
        return $this->listing($request, $query, [
            'heading' => 'RedCherry Top 100',
            'subheading' => 'The definitive reading list — books every shelf deserves',
        ]);
    }

    public function bestsellers(Request $request)
    {
        $query = Book::published()->where('is_bestseller', true);
        return $this->listing($request, $query, [
            'heading' => 'Best Sellers',
            'subheading' => "Books our readers can't put down",
        ]);
    }

    public function newReleases(Request $request)
    {
        $query = Book::published()->orderByDesc('published_at');
        return $this->listing($request, $query, [
            'heading' => 'New Releases',
            'subheading' => 'The freshest titles hot off the press',
        ], defaultSort: false);
    }

    public function search(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        $query = Book::published();

        if ($term !== '') {
            $query->where(function (Builder $q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('isbn', 'like', "%{$term}%")
                    ->orWhereHas('authors', fn (Builder $a) => $a->where('name', 'like', "%{$term}%"));
            });
        }

        return $this->listing($request, $query, [
            'heading' => $term !== '' ? 'Search results for "'.$term.'"' : 'All books',
            'subheading' => null,
        ]);
    }

    private function listing(Request $request, Builder $query, array $data, bool $defaultSort = true)
    {
        $books = $this->applyFilters($request, $query, $defaultSort)->paginate(self::PER_PAGE)->withQueryString();

        // Filter options only list values that actually have published books.
        $published = fn (Builder $q) => $q->published();

        return view('catalog.index', $data + [
            'books' => $books,
            'context' => null,
            'filterCategories' => Category::whereHas('books', $published)
                ->withCount(['books' => $published])->orderBy('sort_order')->orderBy('name')->get(),
            'filterAuthors' => Author::active()->whereHas('books', $published)
                ->withCount(['books' => $published])->orderBy('name')->get(),
            'filterLanguages' => Book::published()->distinct()->orderBy('language')->pluck('language'),
        ]);
    }

    private function applyFilters(Request $request, Builder $query, bool $defaultSort = true): Builder
    {
        // Multi-select filters arrive as arrays (category[]=…); a single value still works.
        $categories = array_filter((array) $request->query('category', []), 'is_string');
        if ($categories) {
            $query->whereHas('category', fn (Builder $c) => $c->whereIn('slug', $categories));
        }

        $authors = array_filter((array) $request->query('author', []), 'is_string');
        if ($authors) {
            $query->whereHas('authors', fn (Builder $a) => $a->whereIn('slug', $authors));
        }

        if ($language = $request->query('language')) {
            $query->where('language', $language);
        }

        if ($format = $request->query('format')) {
            $query->whereHas('variants', fn (Builder $v) => $v->where('type', $format)->where('is_active', true));
        }

        if ($price = $request->query('price')) {
            $query->whereHas('variants', function (Builder $v) use ($price) {
                $v->where('is_active', true);
                match ($price) {
                    'under_200' => $v->where('price', '<', 20000),
                    '200_500' => $v->whereBetween('price', [20000, 50000]),
                    'above_500' => $v->where('price', '>', 50000),
                    default => null,
                };
            });
        }

        $query->with(['authors', 'images', 'activeVariants'])
            ->withCount(['approvedReviews'])
            ->withAvg('approvedReviews', 'rating');

        match ($request->query('sort')) {
            'newest' => $query->orderByDesc('published_at'),
            'price_asc' => $query->orderBy(
                \App\Models\BookVariant::select('price')->whereColumn('book_id', 'books.id')->orderBy('price')->limit(1)
            ),
            'price_desc' => $query->orderByDesc(
                \App\Models\BookVariant::select('price')->whereColumn('book_id', 'books.id')->orderByDesc('price')->limit(1)
            ),
            'best_sellers' => $query->orderByDesc('is_bestseller'),
            'top_rated' => $query->orderByDesc('approved_reviews_avg_rating'),
            default => $defaultSort ? $query->orderByDesc('is_featured')->orderByDesc('id') : null,
        };

        return $query;
    }
}
