<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'biography' => $this->biography,
            'birthdate' => substr($this->birthdate, 0, 4),
            'user_id' => $this->user_id,
            'books' => BookResource::collection($this->whenLoaded('books')),
            'books_count' => $this->whenLoaded('books', function () {
                return $this->books->count();
            }),
            'request_image' => $this->getFirstMediaUrl('author_requests'),
            'profile_image' => $this->getFirstMediaUrl('authors'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
