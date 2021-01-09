<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        //Payments
        \App\Events\Payments\PaymentCreatedEvent::class => [
            \App\Listeners\Payments\PaymentCreatedListener::class
        ],
        \App\Events\Payments\PaymentUpdatedEvent::class => [
            \App\Listeners\Payments\PaymentUpdatedListener::class
        ],
        \App\Events\Payments\PaymentDeletedEvent::class => [
            \App\Listeners\Payments\PaymentDeletedListener::class
        ],
        //Purchases
        \App\Events\Purchases\PurchaseCreatedEvent::class => [
            \App\Listeners\Purchases\PurchaseCreatedListener::class
        ],
        \App\Events\Purchases\PurchaseUpdatedEvent::class => [
            \App\Listeners\Purchases\PurchaseUpdatedListener::class
        ],
        \App\Events\Purchases\PurchaseDeletedEvent::class => [
            \App\Listeners\Purchases\PurchaseDeletedListener::class
        ],
        \App\Events\Purchases\PurchaseReceiverCreatedEvent::class => [
            \App\Listeners\Purchases\PurchaseReceiverCreatedListener::class
        ],
        \App\Events\Purchases\PurchaseReceiverDeletedEvent::class => [
            \App\Listeners\Purchases\PurchaseReceiverDeletedListener::class
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
