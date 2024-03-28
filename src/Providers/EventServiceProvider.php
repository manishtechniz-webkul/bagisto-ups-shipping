<?php

namespace Webkul\UpsShipping\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'sales.shipment.save.before' => [
            'Webkul\UpsShipping\Listeners\Shipment@beforeCreated',
        ],
    ];
}
