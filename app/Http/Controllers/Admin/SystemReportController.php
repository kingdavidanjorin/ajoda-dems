<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CustomerCommunication;
use App\Models\House;
use App\Models\Payment;
use App\Models\PropertyAllocation;
use App\Models\PropertyDocument;
use App\Models\PropertyEnquiry;
use App\Models\PropertyHandover;
use App\Models\PropertyInspection;
use App\Models\Purchase;
use App\Models\Reservation;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class SystemReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAccess($request->user());

        $summary = $this->summary();
        app(AuditLogger::class)->record('report_viewed');

        $recentAudits = AuditLog::query()
            ->with('actor')
            ->latest()
            ->limit(15)
            ->get();

        return view('admin.reports.index', compact('summary', 'recentAudits'));
    }

    public function export(Request $request): StreamedResponse
    {
        $this->ensureAccess($request->user());
        $summary = $this->summary();
        app(AuditLogger::class)->record('report_exported');

        return response()->streamDownload(
            function () use ($summary): void {
                $output = fopen('php://output', 'wb');
                fputcsv($output, ['Category', 'Metric', 'Value']);

                foreach ($summary as $section => $metrics) {
                    foreach ($metrics as $metric => $value) {
                        fputcsv($output, [
                            str($section)->headline(),
                            str((string) $metric)->headline(),
                            $value,
                        ]);
                    }
                }

                fclose($output);
            },
            'ajoda-dems-system-report-'.now()->format('Y-m-d').'.csv',
            ['Content-Type' => 'text/csv'],
        );
    }

    private function summary(): array
    {
        return [
            'users' => [
                'customers' => User::query()
                    ->where('account_type', AccountType::Customer->value)
                    ->count(),
                'staff' => User::query()
                    ->where('account_type', AccountType::Staff->value)
                    ->count(),
            ],
            'properties' => $this->grouped(House::class),
            'enquiries' => $this->grouped(PropertyEnquiry::class),
            'reservations' => $this->grouped(Reservation::class),
            'purchases' => $this->grouped(Purchase::class),
            'payments' => array_merge(
                $this->grouped(Payment::class),
                [
                    'total_recorded_amount' => $this->sum(Payment::class, 'amount'),
                    'verified_amount' => $this->sumWhere(
                        Payment::class,
                        'amount',
                        'status',
                        'verified',
                    ),
                ],
            ),
            'allocations' => ['total' => PropertyAllocation::query()->count()],
            'documents' => ['total' => PropertyDocument::query()->count()],
            'communications' => ['total' => CustomerCommunication::query()->count()],
            'inspections' => $this->grouped(PropertyInspection::class),
            'handovers' => $this->grouped(PropertyHandover::class),
        ];
    }

    private function grouped(string $modelClass): array
    {
        if (! class_exists($modelClass)) {
            return [];
        }

        $model = new $modelClass();
        if (! Schema::hasColumn($model->getTable(), 'status')) {
            return [];
        }

        return $modelClass::query()
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->mapWithKeys(fn (mixed $value, mixed $key): array => [
                (string) $key => (int) $value,
            ])
            ->all();
    }

    private function sum(string $modelClass, string $column): float
    {
        $model = new $modelClass();
        if (! Schema::hasColumn($model->getTable(), $column)) {
            return 0;
        }

        return (float) $modelClass::query()->sum($column);
    }

    private function sumWhere(
        string $modelClass,
        string $sumColumn,
        string $filterColumn,
        string $filterValue,
    ): float {
        $model = new $modelClass();
        $table = $model->getTable();

        if (
            ! Schema::hasColumn($table, $sumColumn)
            || ! Schema::hasColumn($table, $filterColumn)
        ) {
            return 0;
        }

        return (float) $modelClass::query()
            ->where($filterColumn, $filterValue)
            ->sum($sumColumn);
    }

    private function ensureAccess(User $user): void
    {
        abort_unless(
            $user->hasRole(RoleName::SystemAdministrator->value),
            403,
        );
    }
}