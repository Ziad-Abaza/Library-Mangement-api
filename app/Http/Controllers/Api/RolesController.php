<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\RoleResource;
use Illuminate\Support\Facades\Auth;

class RolesController extends Controller
{
    /*
    |------------------------------------------------------
    | Index method to list roles
    |------------------------------------------------------
    */
    public function index()
    {
        try {
            $this->authorize('viewAny', Role::class);
            $query = request()->input('search');
            $roles = Role::when($query, function ($queryBuilder) use ($query) {
                return $queryBuilder->where('name', 'like', '%' . $query . '%')
                    ->orWhere('description', 'like', '%' . $query . '%');
            })->get();

            return RoleResource::collection($roles);
        } catch (\Exception $e) {
            // Return error if fetching roles fails
            return response()->json(['message' => 'Failed to retrieve roles', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Store method to create a new role
    |------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {
            $this->authorize('create', Role::class);
            // Validate input fields for role creation
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:roles,name',
                'description' => 'nullable|string|max:255',
                'role_level' => 'required|integer|min:1|max:5',
                'permission_ids' => 'required|array',
                'permission_ids.*' => 'exists:permissions,id',
            ]);

            // Create new role and sync permissions
            $role = Role::create($validated);
            if ($request->has('permission_ids')) {
                $role->permissions()->sync($request->permission_ids);
            }

            return new RoleResource($role);
        } catch (\Exception $e) {
            // Return error if role creation fails
            return response()->json(['message' => 'Failed to create role', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Show method to retrieve a single role
    |------------------------------------------------------
    */
    public function show($id)
    {
        try {
            // Fetch role with permissions by ID
            $role = Role::with('permissions')->findOrFail($id);
            $this->authorize('view', $role);

            return new RoleResource($role);
        } catch (ModelNotFoundException $e) {
            // Return error if role not found
            return response()->json(['message' => 'Role not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            // Return error if fetching role fails
            return response()->json(['message' => 'Failed to retrieve role', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Update method to modify an existing role
    |------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        try {
            // Find role by ID and validate input fields
            $role = Role::findOrFail($id);
            $this->authorize('update', $role);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:roles,name,' . $id,
                'description' => 'nullable|string|max:255',
                'role_level' => 'required|integer|min:1|max:5',
                'permission_ids' => 'nullable|array',
                'permission_ids.*' => 'exists:permissions,id'
            ]);

            // Update role and sync permissions
            $role->update($validated);
            if ($request->has('permission_ids')) {
                $role->permissions()->sync($request->permission_ids);
            }

            return new RoleResource($role);
        } catch (ModelNotFoundException $e) {
            // Return error if role not found
            return response()->json(['message' => 'Role not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            // Return error if updating role fails
            return response()->json(['message' => 'Failed to update role', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Destroy method to delete a role
    |------------------------------------------------------
    */
    public function destroy($id)
    {
        try {
            // Find and delete role by ID
            $role = Role::findOrFail($id);
            $this->authorize('delete', $role);
            
            $role->permissions()->detach(); // Detach associated permissions
            $role->delete(); // Delete role

            return response()->json(['message' => 'Role deleted successfully'], Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            // Return error if role not found
            return response()->json(['message' => 'Role not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            // Return error if deleting role fails
            return response()->json(['message' => 'Failed to delete role', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
