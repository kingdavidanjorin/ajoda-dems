<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\PropertyInspection;
use App\Notifications\InspectionScheduledNotification;

final class PropertyInspectionObserver
{
    public function created(
        PropertyInspection $inspection,
    ): void {
        $inspection->loadMissing([
            'customer',
            'house',
        ]);

        $inspection->customer->notify(
            new InspectionScheduledNotification(
                $inspection,
            ),
        );
    }
}
