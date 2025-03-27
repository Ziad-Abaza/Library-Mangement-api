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
    /*
    |------------------------------------------------------
    | Handle the incoming request to fetch data for the homepage
    |------------------------------------------------------
    */
    public function __invoke(Request $request)
    {
        // Fetch the latest approved books, ordering by the most recent published date
        $latestBooks = Book::with(['category', 'author'])
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get()
            ->map(function ($book) {
                $book->cover_image_url = $book->getFirstMediaUrl('cover_image');

                if ($book->author) {
                    $book->author->author_image = $book->author->getFirstMediaUrl('authors');
                unset($author->media);
            }

                return $book;
            });

        // Fetch the most popular books based on views count
        $popularBooks = Book::with(['category', 'author'])
            ->where('status', 'approved')
            ->orderBy('views_count', 'desc')
            ->take(6)
            ->get()
            ->map(function ($book) {
                $book->cover_image_url = $book->getFirstMediaUrl('cover_image');

                if ($book->author) {
                    $book->author->author_image = $book->author->getFirstMediaUrl('authors');
                unset($author->media);
            }

                return $book;
            });

        // Fetch the top-rated books based on average comment rating
        $topRatedBooks = Book::with(['category', 'author'])
            ->where('status', 'approved')
            ->withCount(['comments as average_rating' => function ($query) {
                $query->select(DB::raw('coalesce(avg(rating), 0)'));
            }])
            ->orderByDesc('average_rating')
            ->take(6)
            ->get()
            ->map(function ($book) {
                $book->cover_image_url = $book->getFirstMediaUrl('cover_image');

                if ($book->author) {
                    $book->author->author_image = $book->author->getFirstMediaUrl('authors');
                unset($author->media);
            }

                return $book;
            });

        // Fetch a list of authors with their image URL
        $authors = Author::take(5)->get()->map(function ($author) {
            $author->author_image = $author->getFirstMediaUrl('author'); // Add author image URL
            return $author;
        });

        // Search for books based on user input and paginate the results
        $searchBooks = Book::with(['category', 'author'])
            ->where('status', 'approved')
            ->when($request->input('search'), function ($query, $search) {
                // Apply filters based on search input: title, description, category, or author
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%')
                        ->orWhereHas('category', function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('author', function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->paginate(6); // Paginate the results

        // Return the compiled data as an array
        $data = [
            'latestBooks' => $latestBooks,
            'popularBooks' => $popularBooks,
            'topRatedBooks' => $topRatedBooks,
            'authors' => $authors,
            'searchBooks' => $searchBooks,
        ];

        // Return the data as a JSON response wrapped in a HomeResource
        return response()->json(new HomeResource($data));
    }
}
