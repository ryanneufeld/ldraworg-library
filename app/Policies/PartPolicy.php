<?php

namespace App\Policies;

use App\Models\Part;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PartPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->canAny(['part.submit.regular', 'part.submit.fix']);
    }

    public function update(User $user, Part $part)
    {
        return $part->isUnofficial() && $user->can('part.edit.header');
    }

    public function move(User $user, Part $part)
    {
        return $user->can('part.edit.number');
    }

    public function delete(User $user, Part $part)
    {
        return $part->isUnofficial() && $user->can('part.delete');
    }

}
