<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAccess($request->user());

        $auditLogs = AuditLog::query()
            ->with('actor')
            ->when(
                $request->filled('event'),
                fn ($query) => $query->where(
                    'event',
                    $request->string('event')->toString(),
                ),
            )
            ->when(
                $request->filled('date_from'),
                fn ($query) => $query->whereDate(
                    'created_at',
                    '>=',
                    $request->string('date_from')->toString(),
                ),
            )
            ->when(
                $request->filled('date_to'),
                fn ($query) => $query->whereDate(
                    'created_at',
                    '<=',
                    $request->string('date_to')->toString(),
                ),
            )
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.audit.index', compact('auditLogs'));
    }

    private function ensureAccess(User $user): void
    {
        abort_unless(
            $user->hasRole(RoleName::SystemAdministrator->value),
            403,
        );
    }
}