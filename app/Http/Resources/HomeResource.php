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
            'latestBooks' => $this->resource['latestBooks']->map(function ($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'description' => $book->description,
                    'cover_image' => $book->cover_image_url,
                    'published_at' => $book->published_at,
                    'author' => $book->author,
                    'category' => $book->category,
                    'average_rating' => $book->comments_avg_rating ? number_format($book->comments_avg_rating, 1) : null, // استخدم القيمة المحسوبة
                ];
            }),
            'popularBooks' => $this->resource['popularBooks']->map(function ($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'description' => $book->description,
                    'cover_image' => $book->cover_image_url,
                    'views_count' => $book->views_count,
                    'author' => $book->author,
                    'category' => $book->category,
                    'average_rating' => $book->comments_avg_rating ? number_format($book->comments_avg_rating, 1) : null, // استخدم القيمة المحسوبة
                ];
            }),
            'topRatedBooks' => $this->resource['topRatedBooks']->map(function ($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'description' => $book->description,
                    'cover_image' => $book->cover_image_url,
                    'average_rating' => $book->comments_avg_rating ? number_format($book->comments_avg_rating, 1) : null, // استخدم القيمة المحسوبة
                    'author' => $book->author,
                    'category' => $book->category,
                ];
            }),
            'authors' => $this->resource['authors']->map(function ($author) {
                return [
                    'id' => $author->id,
                    'name' => $author->name,
                    'biography' => $author->biography,
                    'birthdate' => $author->birthdate,
                    'profile_image' => $author->profile_image,
                ];
            }),
            'searchBooks' => $this->resource['searchBooks'],
        ];
    }
}
