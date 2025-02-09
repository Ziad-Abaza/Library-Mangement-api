<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermission('view-users');
    }


    public function view(User $user, User $model)
    {
        return $user->hasPermission('view-users');
    }


    public function create(User $user)
    {
        return $user->hasPermission('create-user');
    }

    public function update(User $user, User $model)
    {
        if ($model->hasRole('SuperAdmin') && $user->id !== $model->id) {
            return false;
        }
        return $user->hasPermission('edit-user');
    }

    public function delete(User $user, User $model)
    {
        if ($model->hasRole('SuperAdmin')) {
            return false;
        }
        return $user->hasPermission('delete-user');
    }


    public function addRole(User $user, User $model)
    {
        if ($model->hasRole('SuperAdmin')) {
            return false;
        }
        return $user->hasPermission('edit-user');
    }


    public function removeRole(User $user, User $model)
    {
        if ($model->hasRole('SuperAdmin')) {
            return false;
        }
        return $user->hasPermission('edit-user');
    }
}
