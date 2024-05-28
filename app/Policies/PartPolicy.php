<?php

namespace App\Policies;

use App\Models\Part;
use App\Models\User;

class PartPolicy
{
    public function create(User $user)
    {
        return $user->can('part.submit.regular');
    }

    public function update(User $user, Part $part)
    {
        return $part->isUnofficial() && $user->can('part.edit.header');
    }

    public function move(User $user, Part $part)
    {
        return $user->can('part.edit.number');
    }

    public function flagManualHold(User $user, Part $part)
    {
        return $part->isUnofficial() && $user->can('part.flag.manual-hold');
    }

    public function flagDelete(User $user, Part $part)
    {
        return $part->isUnofficial() && $user->can('part.flag.delete');
    }

    public function delete(User $user, Part $part)
    {
        return $part->isUnofficial() && $user->can('part.delete');
    }

}
