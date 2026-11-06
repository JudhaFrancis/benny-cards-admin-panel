<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Monitored Order Stages for Notifications
    |--------------------------------------------------------------------------
    |
    | Define the stage tables, models, display names, and routes.
    | To add a new stage in the future, simply add an entry here.
    |
    */
    'stages' => [
        'client_information' => [
            'table' => 'order_client_information',
            'model' => \App\Models\OrderClientInformation::class,
            'name' => 'Client Information',
            'stage_key' => 'client_information',
            'route' => '/order-management/client-information',
        ],
        'designing' => [
            'table' => 'order_designing',
            'model' => \App\Models\OrderDesigning::class,
            'name' => 'Designing',
            'stage_key' => 'designing',
            'route' => '/order-management/designing',
        ],
        'printing' => [
            'table' => 'order_printing',
            'model' => \App\Models\OrderPrinting::class,
            'name' => 'Printing',
            'stage_key' => 'printing',
            'route' => '/order-management/printing',
        ],
        'packaging' => [
            'table' => 'order_packaging',
            'model' => \App\Models\OrderPackaging::class,
            'name' => 'Packaging',
            'stage_key' => 'packaging',
            'route' => '/order-management/packaging',
        ],
        'delivery' => [
            'table' => 'order_dispatch_delivery',
            'model' => \App\Models\OrderDispatchDelivery::class,
            'name' => 'Dispatch & Delivery',
            'stage_key' => 'delivery',
            'route' => '/order-management/delivery',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Message Template
    |--------------------------------------------------------------------------
    */
    'default_template' => 'Order #{order_number} assigned to you for {stage_name} by {assigned_by}',
];
