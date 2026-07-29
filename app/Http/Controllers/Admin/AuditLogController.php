<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAccess($request->user());

        $filters = $request->validate([
            'event' => [
                'nullable',
                'string',
                'max:100',
            ],

            'date_from' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'date_to' => [
                'nullable',
                'date_format:Y-m-d',

                Rule::when(
                    $request->filled('date_from'),
                    [
                        'after_or_equal:date_from',
                    ],
                ),
            ],
        ]);

        $auditLogs = AuditLog::query()
            ->with('actor')
            ->when(
                $filters['event'] ?? null,
                fn($query, string $event) => $query->where(
                    'event',
                    $event,
                ),
            )
            ->when(
                $filters['date_from'] ?? null,
                fn($query, string $dateFrom) => $query->whereDate(
                    'created_at',
                    '>=',
                    $dateFrom,
                ),
            )
            ->when(
                $filters['date_to'] ?? null,
                fn($query, string $dateTo) => $query->whereDate(
                    'created_at',
                    '<=',
                    $dateTo,
                ),
            )
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view(
            'admin.audit.index',
            compact('auditLogs'),
        );
    }

    private function ensureAccess(User $user): void
    {
        abort_unless(
            $user->hasRole(
                RoleName::SystemAdministrator->value,
            ),
            403,
        );
    }
}
