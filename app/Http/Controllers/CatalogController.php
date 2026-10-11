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
        $books = $this->applyFilters($request, $query)->paginate(self::PER_PAGE)->withQueryString();

        return view('catalog.index', [
            'books' => $books,
            'heading' => $category->name,
            'subheading' => $category->description,
            'total' => $query->toBase()->getCountForPagination() ?? $books->total(),
        ]);
    }

    public function author(Request $request, Author $author)
    {
        $query = Book::published()->whereHas('authors', fn (Builder $q) => $q->where('authors.id', $author->id));
        $books = $this->applyFilters($request, $query)->paginate(self::PER_PAGE)->withQueryString();

        return view('catalog.index', [
            'books' => $books,
            'heading' => $author->name,
            'subheading' => 'Books by '.$author->name,
        ]);
    }

    public function top100(Request $request)
    {
        $query = Book::published()->where('is_top100', true);
        $books = $this->applyFilters($request, $query)->paginate(self::PER_PAGE)->withQueryString();

        return view('catalog.index', [
            'books' => $books,
            'heading' => 'RedCherry Top 100',
            'subheading' => 'The definitive reading list — books every shelf deserves',
        ]);
    }

    public function bestsellers(Request $request)
    {
        $query = Book::published()->where('is_bestseller', true);
        $books = $this->applyFilters($request, $query)->paginate(self::PER_PAGE)->withQueryString();

        return view('catalog.index', [
            'books' => $books,
            'heading' => 'Best Sellers',
            'subheading' => "Books our readers can't put down",
        ]);
    }

    public function newReleases(Request $request)
    {
        $query = Book::published()->orderByDesc('published_at');
        $books = $this->applyFilters($request, $query, defaultSort: false)->paginate(self::PER_PAGE)->withQueryString();

        return view('catalog.index', [
            'books' => $books,
            'heading' => 'New Releases',
            'subheading' => 'The freshest titles hot off the press',
        ]);
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

        $books = $this->applyFilters($request, $query)->paginate(self::PER_PAGE)->withQueryString();

        return view('catalog.index', [
            'books' => $books,
            'heading' => $term !== '' ? 'Search results for "'.$term.'"' : 'Search',
            'subheading' => null,
        ]);
    }

    private function applyFilters(Request $request, Builder $query, bool $defaultSort = true): Builder
    {
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
