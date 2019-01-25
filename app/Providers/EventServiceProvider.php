<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider {

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\OrderCreateEvent' => [
            'App\Listeners\OrderCreateEventListener',
        ],
        'App\Events\RefundsCreateEvent' => [
            'App\Listeners\RefundCreateEventListener',
        ],
        'App\Events\DispatchCreateEvent' => [
            'App\Listeners\DispatchCreateEventListener',
        ],
        'App\Events\BackorderEvent' => [
            'App\Listeners\BackorderEventListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot() {
        parent::boot();
        //
    }

}
