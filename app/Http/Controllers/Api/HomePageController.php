<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Models\Author;
use App\Http\Resources\HomeResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HomePageController extends Controller
{
    /**
     * Handle the incoming request to fetch data for the homepage.
     */
    public function __invoke(Request $request)
    {
        $latestBooks = $this->getBooks(orderBy: 'created_at', orderDirection: 'desc', limit: 6);
        $popularBooks = $this->getBooks(orderBy: 'views_count', orderDirection: 'desc', limit: 6);
        $topRatedBooks = $this->getTopRatedBooks(limit: 6);
        $authors = $this->getAuthors(limit: 5);
        $searchBooks = $this->getSearchBooks($request);

        $data = compact('latestBooks', 'popularBooks', 'topRatedBooks', 'authors', 'searchBooks');

        return response()->json(new HomeResource($data));
    }

    /**
     * Get books with basic details.
     */
    private function getBooks($orderBy, $orderDirection = 'desc', $limit = 6)
    {
        return Book::with(['category', 'author', 'comments'])
            ->where('status', 'approved')
            ->orderBy($orderBy, $orderDirection)
            ->take($limit)
            ->get()
            ->map(fn($book) => $this->formatBook($book));
    }

    /**
     * Get top-rated books.
     */
    private function getTopRatedBooks($limit = 6)
    {
        return Book::with(['category', 'author', 'comments'])
            ->where('status', 'approved')
            ->withCount(['comments as average_rating' => fn($query) => $query->select(DB::raw('coalesce(avg(rating), 0)'))])
            ->orderByDesc('average_rating')
            ->take($limit)
            ->get()
            ->map(fn($book) => $this->formatBook($book));
    }

    /**
     * Get authors with images.
     */
    private function getAuthors($limit = 5)
    {
        return Author::take($limit)
            ->get()
            ->map(fn($author) => $this->formatAuthor($author));
    }

    /**
     * Get search results for books.
     */
    private function getSearchBooks(Request $request)
    {
        return Book::with(['category', 'author', 'comments'])
            ->where('status', 'approved')
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%")
                        ->orWhereHas('category', fn($q) => $q->where('name', 'like', "%$search%"))
                        ->orWhereHas('author', fn($q) => $q->where('name', 'like', "%$search%"));
                });
            })
            ->paginate(6);
    }

    /**
     * Format book details.
     */
    private function formatBook($book)
    {
        $book->cover_image_url = $book->getFirstMediaUrl('cover_image');
        if ($book->author) {
            $book->author->author_image = $book->author->getFirstMediaUrl('authors');
            unset($book->author->media);
        }
        return $book;
    }

    /**
     * Format author details.
     */
    private function formatAuthor($author)
    {
        $author->profile_image = $author->getFirstMediaUrl('authors');
        unset($author->media);
        return $author;
    }
}
