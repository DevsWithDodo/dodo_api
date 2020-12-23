<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
    }
}
