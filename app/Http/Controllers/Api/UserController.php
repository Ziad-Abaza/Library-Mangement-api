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
    /*
    |------------------------------------------------------
    | Constructor to handle development auto-login
    |------------------------------------------------------
    */
    public function __construct()
    {
        $environment = env('DEV_ENVIRONMENT', false);
        if ($environment) {
            Auth::loginUsingId(1); // Auto-login for development
        }
        // Removed the policy-based authorization to allow inline checks.
    }

    /*
    |------------------------------------------------------
    | Retrieve and cache a list of users based on a search query
    |------------------------------------------------------
    */
    public function index(Request $request)
    {
        // Authorization check (equivalent to UserPolicy::viewAny):
        // The current user must have the 'view-users' permission.
        if (!auth()->user()->hasPermission('view-users')) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        try {
            $cacheKey = 'users_' . md5($request->get('search'));
            $users = Cache::remember($cacheKey, 20 * 60, function () use ($request) {
                $query = User::with(['roles', 'media']);

                if ($request->has('search')) {
                    $search = $request->get('search');
                    $query->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                }

                return $query->get();
            });
            return UserResource::collection($users);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve users.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Show details of a specific user with inline authorization
    |------------------------------------------------------
    */
    public function show(User $user)
    {
        $currentUser = auth()->user();

        // Authorization logic (equivalent to UserPolicy::view):
        // Allow if the current user is the same as the target user,
        // OR if the current user has a higher role level,
        // OR if the current user has the 'view-users' permission.
        if (!(
            $currentUser->id === $user->id ||
            $currentUser->getRoleLevel() > $user->getRoleLevel() ||
            $currentUser->hasPermission('view-users')
        )) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        try {
            return new UserResource($user);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve the user.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Create a new user with inline authorization
    |------------------------------------------------------
    */
    public function store(Request $request)
    {
        // Authorization check (equivalent to UserPolicy::create):
        // The current user must have the 'create-user' permission.
        if (!auth()->user()->hasPermission('create-user')) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        try {
            $validated = $request->validate([
                'name'      => 'required|string|max:255',
                'email'     => 'required|email|max:255|unique:users,email',
                'password'  => 'required|string|min:8',
                'image'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                'role_ids'  => 'nullable|array',
                'role_ids.*' => 'integer|exists:roles,id',
            ]);

            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            if ($request->has('role_ids')) {
                $user->roles()->attach($validated['role_ids']);
            }

            if ($request->hasFile('image')) {
                $user->addMediaFromRequest('image')->toMediaCollection('images');
            } else {
                $defaultImage = config('app.url') . '/assets/images/static/person.png';
                $user->addMediaFromUrl($defaultImage)->toMediaCollection('images');
            }

            return response()->json(['message' => 'User created successfully']);
        } catch (\Throwable $e) {
            return response()->json([
                'error'   => 'Failed to create user.',
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Update user details with inline authorization
    |------------------------------------------------------
    */
    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();

        // Authorization logic (equivalent to UserPolicy::update):
        // Deny update if:
        //   - The target user has the 'superAdmin' role,
        //   - OR the current user is trying to update their own profile,
        //   - OR the current user's role level is lower or equal to the target user's role level,
        //   - OR the current user does not have the 'edit-user' permission.
        if (
            $user->hasRole('superAdmin') ||
            $currentUser->id === $user->id ||
            $currentUser->getRoleLevel() <= $user->getRoleLevel() ||
            !$currentUser->hasPermission('edit-user')
        ) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        try {
            $validated = $request->validate([
                'name'      => 'sometimes|required|string|max:255',
                'email'     => 'sometimes|required|email|max:255|unique:users,email,' . $user->id,
                'password'  => 'sometimes|required|string|min:8',
                'image'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                'role_ids'  => 'nullable|array',
                'role_ids.*' => 'integer|exists:roles,id',
            ]);

            $data = $validated;
            if (isset($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }

            $user->update($data);

            if ($request->has('role_ids')) {
                $user->roles()->sync($validated['role_ids']);
            }

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

    /*
    |------------------------------------------------------
    | Delete a user with inline authorization
    |------------------------------------------------------
    */
    public function destroy(User $user)
    {
        $currentUser = auth()->user();

        // Authorization logic (equivalent to UserPolicy::delete):
        // Deny deletion if:
        //   - The target user has the 'superAdmin' role,
        //   - OR the current user is trying to delete their own account,
        //   - OR the current user's role level is lower or equal to the target user's role level,
        //   - OR the current user does not have the 'delete-user' permission.
        if (
            $user->hasRole('superAdmin') ||
            $currentUser->id === $user->id ||
            $currentUser->getRoleLevel() <= $user->getRoleLevel() ||
            !$currentUser->hasPermission('delete-user')
        ) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

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

    /*
    |------------------------------------------------------
    | Add a role to a user with inline authorization
    |------------------------------------------------------
    */
    public function addRole(Request $request, User $user)
    {
        $currentUser = auth()->user();

        // Use similar authorization as in the update action:
        if (
            $user->hasRole('superAdmin') ||
            $currentUser->id === $user->id ||
            $currentUser->getRoleLevel() <= $user->getRoleLevel() ||
            !$currentUser->hasPermission('edit-user')
        ) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        try {
            $validated = $request->validate([
                'role_id' => 'required|integer|exists:roles,id',
            ]);

            $user->roles()->attach($validated['role_id']);
            $role = Role::find($validated['role_id']);
            $user->notify(new RoleChangedNotification($user, $role));

            return response()->json(['message' => 'Role added successfully'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to add role.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    |------------------------------------------------------
    | Remove a role from a user with inline authorization
    |------------------------------------------------------
    */
    public function removeRole(Request $request, User $user)
    {
        $currentUser = auth()->user();

        // Use similar authorization as in the update action:
        if (
            $user->hasRole('superAdmin') ||
            $currentUser->id === $user->id ||
            $currentUser->getRoleLevel() <= $user->getRoleLevel() ||
            !$currentUser->hasPermission('edit-user')
        ) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        try {
            $validated = $request->validate([
                'role_id' => 'required|integer|exists:roles,id',
            ]);

            $user->roles()->detach($validated['role_id']);
            $role = Role::find($validated['role_id']);
            $user->notify(new RoleChangedNotification($user, $role));

            return response()->json(['message' => 'Role removed successfully'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to remove role.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
