<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\AuthorRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\AuthorResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\Cache;
class AuthorController extends Controller
{

    /*
    |------------------------------------------------------
    | Constructor to handle authorization based on environment
    |------------------------------------------------------
    */
    public function __construct()
    {
        $environment = env('DEV_ENVIRONMENT', false);
        if ($environment) {
            Auth::loginUsingId(1); // Auto-login for development
        } else {
            // Apply resource authorization for production
            $this->authorizeResource(AuthorRequest::class, 'authorRequest', ['except' => ['index', 'show']]);
        }
    }

    /*
|******************************************************************************************************
| > Handles creating and processing Author requests
|******************************************************************************************************
*/

    /*
    |------------------------------------------------------
    | Submit a new author creation request
    |------------------------------------------------------
    */
    public function requestAuthor(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:authors',
            'biography' => 'nullable|string',
            'birthdate' => 'nullable|date',
            'image' => 'nullable|image',
        ]);

        try {
            $user = Auth::user();

            if ($user->hasRole('admin') || $user->hasRole('superAdmin')) {
                $author = Author::create([
                    'name' => $validated['name'],
                    'biography' => $validated['biography'] ?? null,
                    'birthdate' => $validated['birthdate'] ?? null,
                    'user_id' => $user->id,
                ]);

                if ($request->hasFile('image')) {
                    $author->addMedia($request->file('image'))->toMediaCollection('authors');
                } else {
                    $author->addMedia(app()->environment('APP_URL') . '/assets/images/static/person.png')->toMediaCollection('authors');
                }

                return response()->json(['message' => 'Author created successfully'], Response::HTTP_CREATED);
            }

            $authorRequest = AuthorRequest::create([
                'name' => $validated['name'],
                'biography' => $validated['biography'] ?? null,
                'birthdate' => $validated['birthdate'] ?? null,
                'user_id' => $user->id,
                'status' => 'pending',
            ]);

            if ($request->hasFile('image')) {
                $authorRequest->addMedia($request->file('image'))->toMediaCollection('author_requests');
            } else {
                $authorRequest->addMedia(app()->environment('APP_URL') . '/assets/images/static/person.png')->toMediaCollection('author_requests');
            }

            Cache::forget('authors');
            Cache::forget('authorRequests');
            Cache::flush();

            return response()->json(['message' => 'Author request submitted successfully'], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to process author request'], Response::HTTP_BAD_REQUEST);
        }
    }

    /*
    |------------------------------------------------------
    | List all pending author requests
    |------------------------------------------------------
    */
    public function listRequests()
    {
        try {
            $requests = AuthorRequest::where('status', 'pending')
                ->with(['user', 'media'])
                ->get();

            return response()->json(AuthorResource::collection($requests), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve author requests'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Handle approval or rejection of an author request
    |------------------------------------------------------
    */
    public function handleRequest($id, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        try {
            $authorRequest = AuthorRequest::findOrFail($id);

            if ($validated['status'] === 'approved') {
                // Approve request and create a new author
                $author = Author::create([
                    'name' => $authorRequest->name,
                    'biography' => $authorRequest->biography,
                    'birthdate' => $authorRequest->birthdate,
                    'user_id' => Auth::id(),
                ]);

                // Copy media to the new author
                if ($authorRequest->hasMedia('author_requests')) {
                    $media = $authorRequest->getFirstMedia('author_requests');
                    $author->addMedia($media->getPath())
                           ->toMediaCollection('authors');
                }


                $authorRequest->delete();
                // delete cache
                Cache::forget('authors');
                Cache::forget('authorRequests');
                Cache::flush();

                return response()->json(['message' => 'Author request approved and author created'], Response::HTTP_OK);
            } else {
                // Reject the request
                $authorRequest->update(['status' => 'rejected']);

                // delete cache
                Cache::forget('authorRequests');

                return response()->json(['message' => 'Author request rejected'], Response::HTTP_OK);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Author request not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to handle author request'], Response::HTTP_BAD_REQUEST);
        }
    }

    /*
    |------------------------------------------------------
    | Submit an update request for an author
    |------------------------------------------------------
    */
    public function updateAuthorRequest(Request $request, $id)
    {
        try {
            // Validate the incoming request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'biography' => 'nullable|string',
                'birthdate' => 'nullable|date',
                'image' => 'nullable|image',
            ]);

            // Check if the author exists
            $author = Author::findOrFail($id);

            $user = Auth::user();

            if ($user->hasRole('admin') || $user->hasRole('superAdmin')) {
                // Directly update the author if the user has admin privileges
                $author->update($validated);

                // Handle image upload if provided
                if ($request->hasFile('image')) {
                    $author->clearMediaCollection('authors'); // Remove the old image
                    $author->addMedia($request->file('image'))->toMediaCollection('authors');
                }

                return response()->json(['message' => 'Author updated successfully'], Response::HTTP_OK);
            }

            // If the user is not an admin, create an update request
            $authorRequest = AuthorRequest::create(array_merge($validated, [
                'user_id' => $user->id,
                'status' => 'pending',
                'author_id' => $id,
            ]));

            if ($request->hasFile('image')) {
                $authorRequest->addMedia($request->file('image'))->toMediaCollection('author_requests');
            }

            // Clear cache after updating
            Cache::forget('authors');
            Cache::forget('author_' . $id);
            Cache::flush();

            return response()->json(['message' => 'Author update request submitted successfully'], Response::HTTP_CREATED);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Invalid data', 'details' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while processing the request'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /*
    |------------------------------------------------------
    | Handle approval or rejection of an update request
    |------------------------------------------------------
    */
    public function handleUpdateRequest($id, Request $request)
    {
        try {
            $authorRequest = AuthorRequest::findOrFail($id);
            $this->authorize('handleRequest', $authorRequest);

            $validated = $request->validate([
                'status' => 'required|in:approved,rejected',
            ]);

            if ($validated['status'] === 'approved') {
                // Apply changes to the existing author
                $author = Author::findOrFail($authorRequest->author_id);

                $author->update([
                    'name' => $authorRequest->name,
                    'biography' => $authorRequest->biography,
                    'birthdate' => $authorRequest->birthdate,
                    'user_id' => Auth::id(),
                ]);

                if ($authorRequest->hasMedia('author_requests')) {
                    $media = $authorRequest->getFirstMedia('author_requests');
                    $media->copy($author, 'authors');
                }

                $authorRequest->delete();

                Cache::forget('authors');
                Cache::forget('authorRequests');
                Cache::forget('author_' . $id);
                Cache::flush();

                return response()->json(['message' => 'Author update approved and changes applied'], Response::HTTP_OK);
            } else {
                $authorRequest->update(['status' => 'rejected']);
                return response()->json(['message' => 'Author update request rejected'], Response::HTTP_OK);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to handle update request'], Response::HTTP_BAD_REQUEST);
        }
    }

/*
|******************************************************************************************************
| > Handles Functions Resources For Authors
|******************************************************************************************************
*/

    /*
    |------------------------------------------------------
    | Retrieve and filter authors with caching
    |------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {
            $cacheKey = 'authors_' . md5(json_encode($request->all())); // Generate a cache key based on request data
            $authors = Cache::remember($cacheKey, 60, function () use ($request) {
                $query = Author::with(['media']); // Include related media data

                // Apply search filter
                if ($request->has('search')) {
                    $search = $request->input('search');
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                        ->orWhere('biography', 'like', "%{$search}%");
                    });
                }

                // Apply status filter
                if ($request->has('status')) {
                    $status = $request->input('status');
                    $query->whereHas('authorRequests', function ($q) use ($status) {
                        $q->where('status', $status);
                    });
                }

                // Apply birthdate filter
                if ($request->has('birthdate')) {
                    $birthdate = $request->input('birthdate');
                    $query->where('birthdate', $birthdate);
                }

                return $query->get(); // Fetch the filtered data
            });

            return AuthorResource::collection($authors); // Return authors in a resource format
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve authors', 'details' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Show specific author details with caching
    |------------------------------------------------------
    */
    public function show($id)
    {
        try {
            $author = Cache::remember("author_{$id}", 60, function () use ($id) {
                return Author::findOrFail($id); // Retrieve author by ID or fail
            });
            return new AuthorResource($author); // Return author data as a resource
        } catch (\Exception $e) {
            return response()->json(['error' => 'Author not found', 'details' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    /*
    |------------------------------------------------------
    | Retrieve books for a specific author
    |------------------------------------------------------
    */
    public function booksByAuthor($id)
    {
        try {
            $author = Author::findOrFail($id); // Find the author or fail

            $books = $author->books()->where('status', 'approved')->get(); // Fetch approved books

            if ($books->isEmpty()) {
                return response()->json(['error' => 'No approved books found for this author'], Response::HTTP_NOT_FOUND);
            }

            // Add average rating to each book
            $booksWithRatings = $books->map(function ($book) {
                // Calculate average rating
                $averageRating = $book->comments()->avg('rating');
                $formattedRating = $averageRating ? number_format($averageRating, 1) : null;

                // Add average rating to the book object
                $book->average_rating = $formattedRating;

                return $book;
            });

            return response()->json($booksWithRatings, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Author not found', 'details' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    /*
    |------------------------------------------------------
    | Delete an author and associated media
    |------------------------------------------------------
    */
    public function delete($id)
    {
        try {
            $author = Author::findOrFail($id); // Find the author or fail

            if ($author->hasMedia('authors')) {
                $author->clearMediaCollection('authors'); // Clear associated media
            }

            $author->delete(); // Delete the author record

            // delete cache
            Cache::forget('authors');
            Cache::forget('authorRequests');
            Cache::forget("author_{$id}");
            Cache::flush();

            return response()->json(['message' => 'Author deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete author', 'details' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
