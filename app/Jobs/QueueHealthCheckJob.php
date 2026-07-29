<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

final class QueueHealthCheckJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct()
    {
        $this->onQueue('health-check');
    }

    public function handle(): void
    {
        Storage::disk('local')->put(
            'queue-health-check.txt',
            now()->toIso8601String(),
        );
    }
}
