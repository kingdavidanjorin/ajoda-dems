<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Business Timezone
    |--------------------------------------------------------------------------
    |
    | Application and database timestamps remain in UTC. This timezone is used
    | when dates and times are displayed to Ajoda GRA customers and staff.
    |
    */

    'business_timezone' => env(
        'DEMS_BUSINESS_TIMEZONE',
        'Africa/Lagos',
    ),

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    */

    'currency' => env('DEMS_CURRENCY', 'NGN'),

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    'pagination' => [
        'per_page' => (int) env('DEMS_PAGINATION_PER_PAGE', 15),
    ],

];
