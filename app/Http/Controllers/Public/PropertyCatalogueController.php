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

final class PropertyCatalogueController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => [
                'nullable',
                Rule::enum(PropertyStatus::class),
            ],
            'phase_id' => [
                'nullable',
                'integer',
                'exists:estate_phases,id',
            ],
            'house_type_id' => [
                'nullable',
                'integer',
                'exists:house_types,id',
            ],
            'bedrooms' => [
                'nullable',
                'integer',
                'min:0',
                'max:20',
            ],
            'max_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'sort' => [
                'nullable',
                Rule::in([
                    'featured',
                    'newest',
                    'price_asc',
                    'price_desc',
                ]),
            ],
        ]);

        $query = House::query()
            ->published()
            ->with([
                'estate',
                'phase',
                'houseType',
                'primaryMedia',
            ]);

        $query->when(
            $filters['search'] ?? null,
            function (Builder $query, string $search): void {
                $query->where(
                    function (Builder $query) use ($search): void {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere(
                                'property_code',
                                'like',
                                "%{$search}%",
                            )
                            ->orWhere(
                                'plot_number',
                                'like',
                                "%{$search}%",
                            )
                            ->orWhereHas(
                                'houseType',
                                fn (Builder $query): Builder => $query
                                    ->where(
                                        'name',
                                        'like',
                                        "%{$search}%",
                                    ),
                            );
                    },
                );
            },
        );

        $query->when(
            $filters['status'] ?? null,
            fn (Builder $query, string $status): Builder => $query
                ->where('status', $status),
        );

        $query->when(
            $filters['phase_id'] ?? null,
            fn (Builder $query, int|string $phaseId): Builder => $query
                ->where('estate_phase_id', $phaseId),
        );

        $query->when(
            $filters['house_type_id'] ?? null,
            fn (
                Builder $query,
                int|string $houseTypeId,
            ): Builder => $query
                ->where('house_type_id', $houseTypeId),
        );

        $query->when(
            $filters['bedrooms'] ?? null,
            fn (
                Builder $query,
                int|string $bedrooms,
            ): Builder => $query
                ->whereHas(
                    'houseType',
                    fn (Builder $query): Builder => $query
                        ->where('bedrooms', $bedrooms),
                ),
        );

        $query->when(
            $filters['max_price'] ?? null,
            function (
                Builder $query,
                int|float|string $maxPrice,
            ): void {
                $query->where(
                    function (Builder $query) use ($maxPrice): void {
                        $query
                            ->where(
                                function (Builder $query) use ($maxPrice): void {
                                    $query
                                        ->whereNotNull('price_override')
                                        ->where(
                                            'price_override',
                                            '<=',
                                            $maxPrice,
                                        );
                                },
                            )
                            ->orWhere(
                                function (Builder $query) use ($maxPrice): void {
                                    $query
                                        ->whereNull('price_override')
                                        ->whereHas(
                                            'houseType',
                                            fn (
                                                Builder $query,
                                            ): Builder => $query
                                                ->where(
                                                    'base_price',
                                                    '<=',
                                                    $maxPrice,
                                                ),
                                        );
                                },
                            );
                    },
                );
            },
        );

        match ($filters['sort'] ?? 'featured') {
            'newest' => $query->latest('id'),

            'price_asc' => $query->orderByRaw(
                'COALESCE(
                    price_override,
                    (
                        SELECT base_price
                        FROM house_types
                        WHERE house_types.id = houses.house_type_id
                    )
                ) ASC',
            ),

            'price_desc' => $query->orderByRaw(
                'COALESCE(
                    price_override,
                    (
                        SELECT base_price
                        FROM house_types
                        WHERE house_types.id = houses.house_type_id
                    )
                ) DESC',
            ),

            default => $query
                ->orderByDesc('is_featured')
                ->orderBy('id'),
        };

        $houses = $query
            ->paginate(
                (int) config('dems.pagination.per_page', 15),
            )
            ->withQueryString();

        return view('public.properties.index', [
            'houses' => $houses,
            'statuses' => PropertyStatus::cases(),

            'phases' => EstatePhase::query()
                ->where('is_active', true)
                ->whereHas(
                    'houses',
                    fn (Builder $query): Builder => $query->published(),
                )
                ->orderBy('sort_order')
                ->get(),

            'houseTypes' => HouseType::query()
                ->where('is_active', true)
                ->whereHas(
                    'houses',
                    fn (Builder $query): Builder => $query->published(),
                )
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function show(House $house): View
    {
        abort_unless($house->is_published, 404);

        $house->load([
            'estate',
            'phase',
            'houseType',
            'primaryMedia',
            'media' => fn ($query) => $query
                ->where('is_public', true)
                ->orderBy('sort_order'),
        ]);

        $relatedHouses = House::query()
            ->published()
            ->where('id', '!=', $house->id)
            ->where('house_type_id', $house->house_type_id)
            ->with([
                'estate',
                'phase',
                'houseType',
            ])
            ->orderByDesc('is_featured')
            ->limit(3)
            ->get();

        return view('public.properties.show', [
            'house' => $house,
            'relatedHouses' => $relatedHouses,
        ]);
    }
}
