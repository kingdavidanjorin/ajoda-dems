<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use Illuminate\Contracts\View\View;

final class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featuredHouses = House::query()
            ->published()
            ->where('is_featured', true)
            ->with([
                'estate',
                'phase',
                'houseType',
                'primaryMedia',
            ])
            ->orderBy('id')
            ->limit(6)
            ->get();

        return view('public.home', [
            'featuredHouses' => $featuredHouses,

            'availableHomes' => House::query()
                ->published()
                ->available()
                ->count(),

            'estatePhases' => EstatePhase::query()
                ->where('is_active', true)
                ->count(),

            'houseTypes' => HouseType::query()
                ->where('is_active', true)
                ->count(),
        ]);
    }
}
