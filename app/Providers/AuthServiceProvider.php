<?php

namespace App\Providers;

use App\Models\Part;
use App\Models\ReviewSummary;
use App\Models\User;
use App\Models\Vote;
use App\Policies\PartPolicy;
use App\Policies\ReviewSummaryPolicy;
use App\Policies\UserPolicy;
use App\Policies\VotePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Part::class => PartPolicy::class,
        Vote::class => VotePolicy::class,
        User::class => UserPolicy::class,
        ReviewSummary::class => ReviewSummaryPolicy:: class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        
        // Implicitly grant "Super Admin" role all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}

