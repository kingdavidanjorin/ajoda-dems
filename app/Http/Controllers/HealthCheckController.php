<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

final class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::select('SELECT 1');
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'application' => config('app.name'),
                'status' => 'unhealthy',
                'services' => [
                    'database' => 'unavailable',
                ],
                'timestamp' => now()->toIso8601String(),
            ], 503);
        }

        return response()->json([
            'application' => config('app.name'),
            'status' => 'healthy',
            'services' => [
                'database' => 'available',
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
