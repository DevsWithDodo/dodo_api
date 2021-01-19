<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\Response;
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
        App\Group::class => 'App\Policies\GroupPolicy',
        App\Transactions\Payment::class => 'App\Policies\PaymentPolicy',
        App\Transactions\Purchase::class => 'App\Policies\PurchasePolicy',
        App\Request::class => 'App\Policies\RequestPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('is_not_guest', function (User $user) {
            return $user->is_guest ? Response::deny(__('errors.unauthorized_for_guests')) : Response::allow();
        });
        Gate::define('member', function (User $user, Group $group) {
            return $group->members->contains($user);
        });

        //log queries
        if (env('APP_DEBUG')) {
            DB::listen(function ($query) {
                File::append(
                    storage_path('/logs/query.log'),
                    \Carbon\Carbon::now()->toDateTimeString() . ": " . $query->sql . ' [' . implode(', ', $query->bindings) . '] (' . $query->time . ')' . PHP_EOL
                );
            });
        }
    }
}
