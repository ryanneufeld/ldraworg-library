<?php

namespace App\Policies;

use App\Models\ReviewSummary;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReviewSummaryPolicy
{
    public function manage(User $user, ReviewSummary $summary): bool
    {
        return $user->can('reviewsummary.manage');
    }
}
