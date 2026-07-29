<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\PropertyHandover;
use App\Notifications\HandoverScheduledNotification;

final class PropertyHandoverObserver
{
    public function created(
        PropertyHandover $handover,
    ): void {
        $handover->loadMissing([
            'customer',
            'house',
        ]);

        $handover->customer->notify(
            new HandoverScheduledNotification(
                $handover,
            ),
        );
    }
}
