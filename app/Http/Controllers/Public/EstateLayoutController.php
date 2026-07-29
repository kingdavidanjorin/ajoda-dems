<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\PropertyStatus;
use App\Http\Controllers\Controller;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class EstateLayoutController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'phase' => [
                'nullable',
                'integer',
                Rule::exists('estate_phases', 'id'),
            ],

            'type' => [
                'nullable',
                'integer',
                Rule::exists('house_types', 'id'),
            ],

            'status' => [
                'nullable',
                Rule::enum(PropertyStatus::class),
            ],
        ]);

        $layoutQuery = House::query()
            ->published()
            ->whereNotNull('layout_x')
            ->whereNotNull('layout_y');

        $phaseIds = (clone $layoutQuery)
            ->distinct()
            ->pluck('estate_phase_id');

        $houseTypeIds = (clone $layoutQuery)
            ->distinct()
            ->pluck('house_type_id');

        $houses = $layoutQuery
            ->with([
                'estatePhase',
                'houseType',
            ])
            ->when(
                $filters['phase'] ?? null,
                fn (
                    Builder $query,
                    int|string $phase,
                ): Builder => $query->where(
                    'estate_phase_id',
                    $phase,
                ),
            )
            ->when(
                $filters['type'] ?? null,
                fn (
                    Builder $query,
                    int|string $type,
                ): Builder => $query->where(
                    'house_type_id',
                    $type,
                ),
            )
            ->when(
                $filters['status'] ?? null,
                fn (
                    Builder $query,
                    string $status,
                ): Builder => $query->where(
                    'status',
                    $status,
                ),
            )
            ->orderBy('property_code')
            ->get();

        $statusCounts = House::query()
            ->published()
            ->whereNotNull('layout_x')
            ->whereNotNull('layout_y')
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('public.estate-layout.index', [
            'houses' => $houses,

            'phases' => EstatePhase::query()
                ->whereIn('id', $phaseIds)
                ->orderBy('name')
                ->get(),

            'houseTypes' => HouseType::query()
                ->whereIn('id', $houseTypeIds)
                ->orderBy('name')
                ->get(),

            'statusCounts' => $statusCounts,
            'statusOptions' => PropertyStatus::cases(),
            'selectedHouse' => $houses->first(),

            'statusPresentation' => [
                'available' => [
                    'label' => 'Available',
                    'class' => 'bg-emerald-600 ring-emerald-200',
                ],

                'temporarily_reserved' => [
                    'label' => 'Temporarily Reserved',
                    'class' => 'bg-amber-500 ring-amber-200',
                ],

                'sale_processing' => [
                    'label' => 'Sale Processing',
                    'class' => 'bg-orange-500 ring-orange-200',
                ],

                'sold' => [
                    'label' => 'Sold',
                    'class' => 'bg-red-600 ring-red-200',
                ],

                'under_construction' => [
                    'label' => 'Under Construction',
                    'class' => 'bg-blue-600 ring-blue-200',
                ],

                'completed' => [
                    'label' => 'Completed',
                    'class' => 'bg-violet-600 ring-violet-200',
                ],

                'handed_over' => [
                    'label' => 'Handed Over',
                    'class' => 'bg-stone-700 ring-stone-300',
                ],
            ],
        ]);
    }
}
