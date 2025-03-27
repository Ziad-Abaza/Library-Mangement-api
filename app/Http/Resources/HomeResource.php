<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'latestBooks' => $this->formatBooks($this->resource['latestBooks']),
            'popularBooks' => $this->formatBooks($this->resource['popularBooks']),
            'topRatedBooks' => $this->formatBooks($this->resource['topRatedBooks']),
            'authors' => $this->formatAuthors($this->resource['authors']),
            'searchBooks' => $this->formatBooks(collect($this->resource['searchBooks']->items())), // تحويل Paginator إلى Collection
        ];
    }

    /**
     * Format books data.
     */
    private function formatBooks($books)
    {
        return $books->map(function ($book) {
            return [
                'id' => $book->id,
                'title' => $book->title,
                'description' => $book->description,
                'cover_image' => $book->cover_image_url,
                'published_at' => $book->published_at ?? null,
                'views_count' => $book->views_count ?? null,
                'average_rating' => $book->comments->avg('rating') ? number_format($book->comments->avg('rating'), 1) : null,
                'author' => $book->author ? [
                    'id' => $book->author->id,
                    'name' => $book->author->name,
                    'profile_image' => $book->author->author_image ?? null,
                ] : null,
                'category' => $book->category ? [
                    'id' => $book->category->id,
                    'name' => $book->category->name,
                ] : null,
            ];
        });
    }

    /**
     * Format authors data.
     */
    private function formatAuthors($authors)
    {
        return $authors->map(function ($author) {
            return [
                'id' => $author->id,
                'name' => $author->name,
                'biography' => $author->biography,
                'birthdate' => $author->birthdate,
                'profile_image' => $author->profile_image ?? null,
            ];
        });
    }
}
