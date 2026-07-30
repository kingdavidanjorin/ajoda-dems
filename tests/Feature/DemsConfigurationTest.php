<?php

declare(strict_types=1);

it('uses the approved DEMS baseline configuration', function (): void {
    expect(config('app.name'))
        ->toBe('Ajoda GRA DEMS')
        ->and(config('app.timezone'))
        ->toBe('UTC')
        ->and(config('dems.business_timezone'))
        ->toBe('Africa/Lagos')
        ->and(config('dems.currency'))
        ->toBe('NGN')
        ->and(config('dems.pagination.per_page'))
        ->toBe(15)
        ->and(config('dems.reservation_hold_hours'))
        ->toBe(72)
        ->and(config('dems.customer.default_country'))
        ->toBe('Nigeria')
        ->and(config('dems.auth_debug.write_links'))
        ->toBeFalse()
        ->and(config('dems.auth_debug.reset_link_filename'))
        ->toBe('staff-setup-link.txt')
        ->and(config('dems.auth_debug.verification_link_filename'))
        ->toBe('verification-link.txt');
});
