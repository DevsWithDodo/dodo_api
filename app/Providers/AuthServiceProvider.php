<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

use App\User;
use App\Group;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('edit-group', function ($user, Group $group) {
            return $group->members->find($user)->member_data->is_admin
                ? Response::allow()
                : Response::deny('You must be a group admin.');
        });

        Gate::define('edit-member', function ($user, User $user_to_edit, Group $group) {
            if($user->id == $user_to_edit->id) {
                return Response::allow();
            } else { return Gate::authorize('edit-group', $group); }
        });
    }
}
