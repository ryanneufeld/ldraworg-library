<?php

namespace App\Policies;

use App\Models\Part;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class PartPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
      return $user->canAny('part.submit.regular', 'part.submit.fix', 'part.submit.proxy');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Part  $part
     * @return mixed
     */
    public function update(User $user, Part $part)
    {
      return $user->can('part.edit.header', 'part.edit.number');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Part  $part
     * @return mixed
     */
    public function delete(User $user, Part $part)
    {
      return $user->can('part.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Part  $part
     * @return mixed
     */
    public function restore(User $user, Part $part)
    {
      return $user->can('part.edit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Part  $part
     * @return mixed
     */
    public function forceDelete(User $user, Part $part)
    {
      return $user->can('part.delete');
    }
}
