<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Notifications\RoleChangedNotification;

class UserController extends Controller
{
    /**
     * Constructor.
     *
     * For development, auto-login as user with ID 1.
     * In production, we are not using policy-based authorization.
     */
    public function __construct()
    {
        $environment = env('DEV_ENVIRONMENT', false);
        if ($environment) {
            Auth::loginUsingId(1); // Auto-login for development
        }
    }

    /**
     * Check if the current authenticated user has the required permission
     * for a given action and ensure that the target user is not superAdmin.
     *
     * @param User|null $targetUser The user being acted upon (if applicable)
     * @param string    $permission The required permission (e.g., 'create-user', 'edit-user', 'delete-user')
     *
     * @return void
     */
    private function authorizeAction(?User $targetUser, string $permission)
    {
        $authUser = Auth::user();

        // Ensure the authenticated user exists and has the required permission.
        if (!$authUser || !$authUser->hasPermission($permission)) {
            abort(Response::HTTP_FORBIDDEN, 'Unauthorized action.');
        }

        // If there is a target user, disallow any action on a superAdmin.
        if ($targetUser && $targetUser->hasRole('superAdmin')) {
            abort(Response::HTTP_FORBIDDEN, 'Action not allowed on superAdmin.');
        }
    }

    /**
     * Retrieve and cache a list of users based on the search query.
     */
    public function index(Request $request)
    {
        try {
            $cacheKey = 'users_' . md5($request->get('search', ''));
            $users = Cache::remember($cacheKey, 20 * 60, function () use ($request) {
                $query = User::with(['roles', 'media']);
                if ($request->has('search')) {
                    $search = $request->get('search');
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%");
                    });
                }
                return $query->get();
            });
            return UserResource::collection($users);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve users.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show details of a specific user.
     */
    public function show(User $user)
    {
        try {
            return new UserResource($user);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve the user.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a new user with optional roles and image.
     *
     * Note: Cannot assign superAdmin role during creation.
     */
    public function store(Request $request)
    {
        // Authorize creation using the 'create-user' permission.
        $this->authorizeAction(null, 'create-user');

        try {
            $validated = $request->validate([
                'name'      => 'required|string|max:255',
                'email'     => 'required|email|max:255|unique:users,email',
                'password'  => 'required|string|min:8',
                'image'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                'role_ids'  => 'nullable|array',
                'role_ids.*' => 'integer|exists:roles,id',
            ]);

            // Prevent assigning superAdmin role during creation.
            if (isset($validated['role_ids'])) {
                $superAdminRole = Role::where('name', 'superAdmin')->first();
                if ($superAdminRole && in_array($superAdminRole->id, $validated['role_ids'])) {
                    return response()->json(['error' => 'Cannot assign superAdmin role.'], Response::HTTP_FORBIDDEN);
                }
            }

            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            if (isset($validated['role_ids'])) {
                $user->roles()->attach($validated['role_ids']);
            }

            if ($request->hasFile('image')) {
                $user->addMediaFromRequest('image')->toMediaCollection('images');
            } else {
                // Use default image URL if no image provided.
                $user->addMediaFromUrl(config('app.url') . '/assets/images/static/person.png')
                    ->toMediaCollection('images');
            }

            return response()->json(['message' => 'User created successfully'], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to create user.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update user details including name, email, password, and image.
     *
     * Note: superAdmin users cannot be modified.
     */
    public function update(Request $request, User $user)
    {
        // Authorize update using the 'edit-user' permission.
        $this->authorizeAction($user, 'edit-user');

        try {
            $validated = $request->validate([
                'name'     => 'sometimes|required|string|max:255',
                'email'    => 'sometimes|required|email|max:255|unique:users,email,' . $user->id,
                'password' => 'sometimes|required|string|min:8',
                'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            ]);

            $data = $validated;
            if (isset($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }

            $user->update($data);

            if ($request->hasFile('image')) {
                $user->clearMediaCollection('images');
                $user->addMediaFromRequest('image')->toMediaCollection('images');
            }

            return new UserResource($user);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update user.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a user along with their associated data.
     *
     * Note: superAdmin users cannot be deleted.
     */
    public function destroy(User $user)
    {
        // Authorize deletion using the 'delete-user' permission.
        $this->authorizeAction($user, 'delete-user');

        try {
            $user->clearMediaCollection('images');
            $user->delete();
            return response()->json(['message' => 'User deleted successfully'], Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete user.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Add a role to a user and notify them of the change.
     *
     * Note: Adding the superAdmin role is not allowed.
     */
    public function addRole(Request $request, User $user)
    {
        // Authorize role modification using the 'edit-user' permission.
        $this->authorizeAction($user, 'edit-user');

        try {
            $validated = $request->validate([
                'role_id' => 'required|integer|exists:roles,id',
            ]);

            $role = Role::find($validated['role_id']);
            if ($role && $role->name === 'superAdmin') {
                return response()->json(['error' => 'Cannot assign superAdmin role.'], Response::HTTP_FORBIDDEN);
            }

            $user->roles()->attach($validated['role_id']);
            $user->notify(new RoleChangedNotification($user, $role));

            return response()->json(['message' => 'Role added successfully'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to add role.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove a role from a user and notify them of the change.
     *
     * Note: Removing the superAdmin role is not allowed.
     */
    public function removeRole(Request $request, User $user)
    {
        // Authorize role modification using the 'edit-user' permission.
        $this->authorizeAction($user, 'edit-user');

        try {
            $validated = $request->validate([
                'role_id' => 'required|integer|exists:roles,id',
            ]);

            $role = Role::find($validated['role_id']);
            if ($role && $role->name === 'superAdmin') {
                return response()->json(['error' => 'Cannot remove superAdmin role.'], Response::HTTP_FORBIDDEN);
            }

            $user->roles()->detach($validated['role_id']);
            $user->notify(new RoleChangedNotification($user, $role));

            return response()->json(['message' => 'Role removed successfully'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to remove role.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
