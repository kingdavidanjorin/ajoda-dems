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
        ->toBe(15);
});
