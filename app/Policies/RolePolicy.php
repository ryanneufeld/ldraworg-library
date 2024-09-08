<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('role.manage');
    }

    public function manage(User $user, Role $role): bool
    {
        return $role->name == 'Super Admin' ? $user->can('role.manage.superuser') : $user->can('role.manage');
    }
}
