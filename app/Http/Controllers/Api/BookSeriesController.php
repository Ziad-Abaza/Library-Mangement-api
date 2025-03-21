<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookSeriesResource;
use App\Models\BookSeries;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BookSeriesController extends Controller
{
    /*
    |------------------------------------------------------
    | List all book series with optional search
    |------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {
            $query = BookSeries::query();

            // Apply search filter if provided in the request
            if ($request->filled('search')) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            $bookSeries = $query->with('books')->get(); // Return the series with associated books

            return BookSeriesResource::collection($bookSeries);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to retrieve book series.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Show a single book series by its ID
    |------------------------------------------------------
    */
    public function show($id)
    {
        try {
            $bookSeries = BookSeries::with('books')->findOrFail($id);
            return new BookSeriesResource($bookSeries);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Book series not found.'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to retrieve book series.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Store a new book series
    |------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'required|image',
            ]);

            $validated['user_id'] = Auth::id();
            $bookSeries = BookSeries::create($validated);

            if ($request->hasFile('image')) {
                $bookSeries->addMediaFromRequest('image')->toMediaCollection('book_series');
            }

            return new BookSeriesResource($bookSeries, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to create book series.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Update an existing book series
    |------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        try {
            $bookSeries = BookSeries::findOrFail($id);
            $this->authorize('update', $bookSeries);

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|nullable|string',
                'image' => 'sometimes|nullable|image',
            ]);

            $bookSeries->update($validated);

            if ($request->hasFile('image')) {
                $bookSeries->clearMediaCollection('book_series');
                $bookSeries->addMediaFromRequest('image')->toMediaCollection('book_series');
            }

            return new BookSeriesResource($bookSeries);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Book series not found.'], Response::HTTP_NOT_FOUND);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['error' => 'You do not have permission to update this book series.'], Response::HTTP_FORBIDDEN);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to update book series.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Delete an existing book series
    |------------------------------------------------------
    */
    public function destroy($id)
    {
        try {
            $bookSeries = BookSeries::findOrFail($id);
            $this->authorize('delete', $bookSeries);
            $bookSeries->delete();

            return response()->json(['message' => 'Book series deleted successfully'], Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Book series not found.'], Response::HTTP_NOT_FOUND);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['error' => 'You do not have permission to delete this book series.'], Response::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to delete book series.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
