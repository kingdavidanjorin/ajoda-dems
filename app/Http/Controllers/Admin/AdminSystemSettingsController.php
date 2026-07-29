<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

final class AdminSystemSettingsController extends Controller
{
    public function index(): View
    {
        $this->ensureAccess();

        $settings = [
            'Application name' => config('app.name'),
            'Environment' => app()->environment(),
            'Application URL' => config('app.url'),
            'Debug mode' => config('app.debug') ? 'Enabled' : 'Disabled',
            'Database connection' => config('database.default'),
            'Queue connection' => config('queue.default'),
            'Cache store' => config('cache.default'),
            'Session driver' => config('session.driver'),
            'Mail transport' => config('mail.default'),
            'Application timezone' => config('app.timezone'),
        ];

        return view(
            'admin.system-settings.index',
            compact('settings'),
        );
    }

    private function ensureAccess(): void
    {
        abort_unless(
            auth()->user()?->hasRole(
                RoleName::SystemAdministrator->value,
            ),
            403,
        );
    }
}