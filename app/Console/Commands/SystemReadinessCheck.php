<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class SystemReadinessCheck extends Command
{
    protected $signature = 'dems:system-check';

    protected $description = 'Check whether Ajoda DEMS is ready for deployment.';

    public function handle(): int
    {
        $checks = [
            $this->check('Application key', filled(config('app.key'))),
            $this->databaseCheck(),
            $this->check('Database queue', config('queue.default') === 'database'),
            $this->check('Frontend build', File::exists(public_path('build/manifest.json'))),
            $this->check('Log directory writable', is_writable(storage_path('logs'))),
            $this->check(
                'Private storage writable',
                is_writable((string) config('filesystems.disks.local.root')),
            ),
        ];

        foreach ($this->requiredTables() as $table) {
            $checks[] = $this->check('Table: '.$table, Schema::hasTable($table));
        }

        if (app()->environment('production')) {
            $checks[] = $this->check('Debug disabled', config('app.debug') === false);
        }

        $this->table(
            ['Check', 'Status'],
            array_map(
                fn (array $check): array => [
                    $check['name'],
                    $check['passed'] ? 'PASS' : 'FAIL',
                ],
                $checks,
            ),
        );

        $failed = collect($checks)->contains(
            fn (array $check): bool => ! $check['passed'],
        );

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function databaseCheck(): array
    {
        try {
            DB::connection()->getPdo();
            return $this->check('Database connection', true);
        } catch (Throwable) {
            return $this->check('Database connection', false);
        }
    }

    private function check(string $name, bool $passed): array
    {
        return compact('name', 'passed');
    }

    private function requiredTables(): array
    {
        return [
            'users',
            'houses',
            'property_enquiries',
            'reservations',
            'purchases',
            'payments',
            'property_allocations',
            'property_documents',
            'customer_communications',
            'property_inspections',
            'property_handovers',
            'notifications',
            'jobs',
            'failed_jobs',
            'audit_logs',
        ];
    }
}