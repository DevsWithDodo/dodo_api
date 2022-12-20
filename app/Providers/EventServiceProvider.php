<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Transactions\Payment;
use App\Transactions\Purchase;
use App\Transactions\PurchaseReceiver;
use App\Observers\PaymentObserver;
use App\Observers\PurchaseObserver;
use App\Observers\PurchaseReceiverObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Payment::observe(PaymentObserver::class);
        Purchase::observe(PurchaseObserver::class);
        PurchaseReceiver::observe(PurchaseReceiverObserver::class);
    }
}
