<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
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
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        Auth::viaRequest('mybb-user', function (Request $request) {
          $mybb = $request->cookie('mybbuser') ?? '';
          $mybb = explode("_", $mybb);
          if (!is_array($mybb) || count($mybb) !== 2) return null;
          $u = DB::connection('mybb')->table('mybb_users')
            ->select('uid', 'loginname', 'loginkey')
            ->where('uid', $mybb[0])->first();
          $user = User::firstWhere('forum_user_id', $mybb[0]);
          return $u->loginkey == $mybb[1] && isset($user) ? $user : null;          
        });

    }
}
