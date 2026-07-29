<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class AuditLogger
{
    public function record(
        string $event,
        ?Model $model = null,
        array $oldValues = [],
        array $newValues = [],
    ): AuditLog {
        $request = app()->runningInConsole() ? null : request();

        return AuditLog::query()->create([
            'user_id' => auth()->id(),
            'auditable_type' => $model?->getMorphClass(),
            'auditable_id' => $model?->getKey(),
            'event' => $event,
            'route_name' => $request?->route()?->getName(),
            'request_method' => $request?->method(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? Str::limit((string) $request->userAgent(), 1000) : null,
            'old_values' => $this->clean($oldValues),
            'new_values' => $this->clean($newValues),
        ]);
    }

    private function clean(array $values): array
    {
        foreach ([
            'password',
            'password_hash',
            'passwordHash',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'token',
            'reset_token',
        ] as $key) {
            unset($values[$key]);
        }

        return $values;
    }
}