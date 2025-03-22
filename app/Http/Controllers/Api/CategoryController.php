<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryGroup;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CategoryGroupResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Exception;

class CategoryController extends Controller
{

    /*
    |---------------------------------------
    | Get list of categories with search filter and pagination
    |---------------------------------------
    */
    public function index(Request $request)
    {
        try {

                $query = Category::with('categoryGroup');

                // Apply search filter if provided
                if ($request->filled('search')) {
                    $query->where(function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%')
                            ->orWhereHas('categoryGroup', function ($q) use ($request) {
                                $q->where('name', 'like', '%' . $request->search . '%');
                            });
                    });
                }

                // Filter by group ID if provided
                if ($request->filled('group_id')) {
                    $query->where('category_group_id', $request->group_id);
                }

            $categories = $query->get();

            return CategoryResource::collection($categories);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error fetching categories', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /*
    |------------------------------------------------------
    | Get a single category by ID
    |------------------------------------------------------
    */
    public function show($id)
    {
        try {
            // Fetch category and its associated category group
            $category = Category::with('categoryGroup')->findOrFail($id);

            // Return category in API resource format
            return new CategoryResource($category);
        } catch (Exception $e) {
            // Return error response if exception occurs
            return response()->json(['error' => 'Error fetching category', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Create a new category
    |------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {
            // Authorization check
            $this->authorize('create', Category::class);

            // Validate the request data
            $validated = $request->validate([
                'name' => 'required|string|max:255', // Category name is required and should be a string
                'description' => 'nullable|string|max:255', // Description is optional but must be a string
                'category_group_id' => 'required|exists:category_groups,id', // Ensure valid category group ID
            ]);

            // Create the new category and store in database
            $category = Category::create($validated);
            // Return newly created category in API resource format
            return new CategoryResource($category);
        } catch (Exception $e) {
            // Return error response if exception occurs
            return response()->json(['error' => 'Error creating category', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Update an existing category
    |------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        try {
            // Find the category by ID, or throw an exception if not found
            $category = Category::findOrFail($id);

            // Authorization check
            $this->authorize('update', $category);

            // Validate the request data
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255', // Category name is required and should be a string
                'description' => 'nullable|string|max:255', // Description is optional but must be a string
                'category_group_id' => 'required|exists:category_groups,id', // Ensure valid category group ID
            ]);

            // Update the category in the database
            $category->update($validated);

            // Return the updated category in API resource format
            return new CategoryResource($category);
        } catch (Exception $e) {
            // Return error response if exception occurs
            return response()->json(['error' => 'Error updating category ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Delete a category by ID
    |------------------------------------------------------
    */
    public function destroy($id)
    {
        try {
            // Find the category by ID, or throw an exception if not found
            $category = Category::findOrFail($id);

            // Authorization check
            $this->authorize('delete', $category);

            // Delete the category from the database
            $category->delete();
            // Return success response
            return response()->json(['message' => 'Category deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Return error response if exception occurs
            return response()->json(['error' => 'Error deleting category', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /*
    |------------------------------------------------------
    | Method to fetch category groups with optional search functionality.
    |------------------------------------------------------
    */
    public function categoryGroups(Request $request)
    {
        try {

                $query = CategoryGroup::with('categories');

                // If 'search' parameter is present, filter by name.
                if ($request->filled('search')) {
                    $query->where('name', 'like', '%' . $request->search . '%');
                }

            $categoryGroups = $query->get();

            // Return the fetched category groups as a resource collection.
            return CategoryGroupResource::collection($categoryGroups);
        } catch (Exception $e) {
            // Handle any exceptions and return an error response.
            return response()->json(['error' => 'Error fetching category groups', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Method to fetch a single category group by ID.
    |------------------------------------------------------
    */
    public function showCategoryGroup($id)
    {
        try {
            // Fetch the category group with the given ID, along with its associated categories.
            $categoryGroup = CategoryGroup::with('categories')->findOrFail($id);
            // Return the category group as a resource.
            return new CategoryGroupResource($categoryGroup);
        } catch (Exception $e) {
            // Handle any exceptions and return an error response.
            return response()->json(['error' => 'Error fetching category group', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Method to create a new category group.
    |------------------------------------------------------
    */
    public function storeCategoryGroup(Request $request)
    {
        try {
            // Check if environment is not development, then authorize the user.
            $this->authorize('create', CategoryGroup::class);

            // Validate the incoming request data.
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            // Create a new category group with validated data.
            $categoryGroup = CategoryGroup::create($validated);

            // Return the created category group as a resource.
            return new CategoryGroupResource($categoryGroup);
        } catch (Exception $e) {
            // Handle any exceptions and return an error response.
            return response()->json(['error' => 'Error creating category group', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Method to update an existing category group.
    |------------------------------------------------------
    */
    public function updateCategoryGroup(Request $request, $id)
    {
        try {
            // Fetch the category group with the given ID.
            $categoryGroup = CategoryGroup::findOrFail($id);

            // Check if environment is not development, then authorize the user.
            $this->authorize( 'update', CategoryGroup::class);

            // Validate the incoming request data.
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            // Update the category group with the validated data.
            $categoryGroup->update($validated);

            // Return the updated category group as a resource.
            return new CategoryGroupResource($categoryGroup);
        } catch (Exception $e) {
            // Handle any exceptions and return an error response.
            return response()->json(['error' => 'Error updating category group', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Method to delete an existing category group.
    |------------------------------------------------------
    */
    public function destroyCategoryGroup($id)
    {
        try {
            // Fetch the category group with the given ID.
            $categoryGroup = CategoryGroup::findOrFail($id);

            // Check if environment is not development, then authorize the user.
            $this->authorize('delete', CategoryGroup::class);

            // Delete the category group.
            $categoryGroup->delete();

            // Return a success response indicating the category group was deleted.
            return response()->json(['message' => 'Category group deleted successfully'], Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            // Handle any exceptions and return an error response.
            return response()->json(['error' => 'Error deleting category group', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
