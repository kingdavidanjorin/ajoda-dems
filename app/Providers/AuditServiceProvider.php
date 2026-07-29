<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Audit\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

final class AuditServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        foreach ($this->models() as $modelClass) {
            if (! class_exists($modelClass)) {
                continue;
            }

            $modelClass::created(function (Model $model): void {
                app(AuditLogger::class)->record('created', $model, [], $model->getAttributes());
            });

            $modelClass::updated(function (Model $model): void {
                $changes = $model->getChanges();
                $old = [];

                foreach (array_keys($changes) as $attribute) {
                    $old[$attribute] = $model->getRawOriginal($attribute);
                }

                app(AuditLogger::class)->record('updated', $model, $old, $changes);
            });

            $modelClass::deleted(function (Model $model): void {
                app(AuditLogger::class)->record('deleted', $model, $model->getAttributes(), []);
            });
        }
    }

    private function models(): array
    {
        return [
            'App\Models\User',
            'App\Models\House',
            'App\Models\PropertyEnquiry',
            'App\Models\Reservation',
            'App\Models\Purchase',
            'App\Models\Payment',
            'App\Models\PropertyAllocation',
            'App\Models\PropertyDocument',
            'App\Models\CustomerCommunication',
            'App\Models\PropertyInspection',
            'App\Models\PropertyHandover',
        ];
    }
}