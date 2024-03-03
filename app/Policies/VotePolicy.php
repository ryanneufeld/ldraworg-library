<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vote;
use App\Models\Part;
use Illuminate\Auth\Access\HandlesAuthorization;

class VotePolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user, Part $part, string $vote_type): bool
    {
        switch ($vote_type) {
            case 'A': 
                return $user->can('part.vote.admincertify');
                break;
            case 'T':
                return $user->can('part.vote.fasttrack');
                break;
            case 'C':
                if ($part->user_id === $user->id) {
                    return $user->can('part.own.vote.certify');
                } else {
                    return $user->can('part.vote.certify');
                }
                break;
            case 'H':
                if ($part->user_id !== $user->id) {
                    return $user->hasPermissionTo('part.vote.hold');
                } else {
                    return $user->hasAnyPermission(['part.vote.hold', 'part.own.vote.hold']);
                }
                break;
        }
        return false;
    }

    public function update(User $user, Vote $vote, string $vote_type): bool
    {
        if ($vote->user_id !== $user->id) {
            return false;
        }
        switch ($vote_type) {
            case 'N':
                return true;
                break;
            case 'A': 
                return $user->can('part.vote.admincertify');
                break;
            case 'T':
                return $user->can('part.vote.fasttrack');
                break;
            case 'C':
                if ($vote->part->user_id === $user->id) {
                    return $user->can('part.own.vote.certify');
                } else {
                    return $user->can('part.vote.certify');
                }
                break;
            case 'H':
                if ($vote->part->user_id !== $user->id) {
                    return $user->can('part.vote.hold');
                } else {
                    return $user->canAny(['part.vote.hold', 'part.own.vote.hold']);
                }
                break;
        }
        return false;
    }

    public function delete(User $user, Vote $vote): bool
    {
        return $vote->user_id === $user->id;
    }
}
