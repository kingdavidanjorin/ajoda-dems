<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Reservations\PropertyReservationService;
use Illuminate\Console\Command;

final class ExpirePropertyReservations extends Command
{
    protected $signature = 'reservations:expire';

    protected $description =
        'Expire reservation holds that have passed their expiry time';

    public function handle(
        PropertyReservationService $service,
    ): int {
        $expiredCount = $service->expireDueReservations();

        $this->info(
            "Expired {$expiredCount} reservation(s).",
        );

        return self::SUCCESS;
    }
}
