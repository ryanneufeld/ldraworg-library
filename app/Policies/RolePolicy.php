<?php

namespace App\Policies;

use Spatie\Permission\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('role.manage');
    }

    public function manage(User $user, Role $role): bool
    {
        return $role->name == "Super Admin" ? $user->can('role.manage.superuser') : $user->can('role.manage');
    }
}
