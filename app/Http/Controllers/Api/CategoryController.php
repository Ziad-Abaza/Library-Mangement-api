<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\CategoryGroup;
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
            // Retrieve categories directly from the database
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
            return response()->json(['error' => 'Error fetching categories', 'message' => $e->getMessage()], 500);
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

            // Authorization check for non-development environment
                $this->authorize('view', $category); // Ensure user has permission to view the category


            return new CategoryResource($category);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error fetching category', 'message' => $e->getMessage()], 500);
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
            // Authorization check for non-development environment
                $this->authorize('create', Category::class); // Ensure user has permission to create a category


            // Validate the request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
                'category_group_id' => 'required|exists:category_groups,id',
            ]);

            // Create the new category and store in the database
            $category = Category::create($validated);

            return new CategoryResource($category);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error creating category', 'message' => $e->getMessage()], 500);
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

            // Authorization check for non-development environment
                $this->authorize('update', $category); // Ensure user has permission to update the category

            // Validate the request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
                'category_group_id' => 'required|exists:category_groups,id',
            ]);

            // Update the category in the database
            $category->update($validated);

            return new CategoryResource($category);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error updating category', 'message' => $e->getMessage()], 500);
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

            // Authorization check for non-development environment
                $this->authorize('delete', $category); // Ensure user has permission to delete the category

            // Delete the category from the database
            $category->delete();

            return response()->json(['message' => 'Category deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error deleting category', 'message' => $e->getMessage()], 500);
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
            // Retrieve category groups directly from the database
            $query = CategoryGroup::with('categories');

            // If 'search' parameter is present, filter by name
            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $categoryGroups = $query->get();

            return CategoryGroupResource::collection($categoryGroups);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error fetching category groups', 'message' => $e->getMessage()], 500);
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
            // Fetch the category group with the given ID, along with its associated categories
            $categoryGroup = CategoryGroup::with('categories')->findOrFail($id);

            return new CategoryGroupResource($categoryGroup);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error fetching category group', 'message' => $e->getMessage()], 500);
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
            // Authorization check for non-development environment
                $this->authorize('create', CategoryGroup::class);

            // Validate the incoming request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            // Create a new category group with validated data
            $categoryGroup = CategoryGroup::create($validated);

            return new CategoryGroupResource($categoryGroup);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error creating category group', 'message' => $e->getMessage()], 500);
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
            // Fetch the category group with the given ID
            $categoryGroup = CategoryGroup::findOrFail($id);

                $this->authorize('update', $categoryGroup);

            // Validate the incoming request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            // Update the category group with the validated data
            $categoryGroup->update($validated);

            return new CategoryGroupResource($categoryGroup);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error updating category group', 'message' => $e->getMessage()], 500);
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
            // Fetch the category group with the given ID
            $categoryGroup = CategoryGroup::findOrFail($id);

            // Authorization check for non-development environment
            $this->authorize('delete', $categoryGroup);
            // Delete the category group
            $categoryGroup->delete();

            return response()->json(['message' => 'Category group deleted successfully'], 204);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error deleting category group', 'message' => $e->getMessage()], 500);
        }
    }
}
