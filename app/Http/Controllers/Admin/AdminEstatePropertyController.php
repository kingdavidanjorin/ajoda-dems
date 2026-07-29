<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;

final class AdminEstatePropertyController extends Controller
{
    public function index(): View
    {
        $this->ensureAccess();

        $stats = [
            'estates' => Estate::query()->count(),
            'phases' => EstatePhase::query()->count(),
            'house_types' => HouseType::query()->count(),
            'properties' => House::query()->count(),
        ];

        $propertyStatusCounts = Schema::hasColumn(
            (new House())->getTable(),
            'status',
        )
            ? House::query()
                ->selectRaw('status, COUNT(*) AS total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->mapWithKeys(
                    fn (mixed $value, mixed $key): array => [
                        (string) $key => (int) $value,
                    ],
                )
                ->all()
            : [];

        $recentProperties = House::query()
            ->latest('id')
            ->limit(20)
            ->get();

        return view(
            'admin.estate-properties.index',
            compact(
                'stats',
                'propertyStatusCounts',
                'recentProperties',
            ),
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