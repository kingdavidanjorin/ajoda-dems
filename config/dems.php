<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Customer defaults
    |--------------------------------------------------------------------------
    */

    'customer' => [
        'default_country' => env(
            'DEMS_DEFAULT_CUSTOMER_COUNTRY',
            'Nigeria',
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Local authentication diagnostics
    |--------------------------------------------------------------------------
    |
    | These files may contain temporary password-reset or verification
    | links. They must only be written during explicitly enabled local
    | development.
    |
    */

    'auth_debug' => [
        'write_links' => (bool) env(
            'DEMS_WRITE_LOCAL_AUTH_LINKS',
            false,
        ),

        'reset_link_filename' => env(
            'DEMS_RESET_LINK_FILENAME',
            'staff-setup-link.txt',
        ),

        'verification_link_filename' => env(
            'DEMS_VERIFICATION_LINK_FILENAME',
            'verification-link.txt',
        ),
    ],
];
