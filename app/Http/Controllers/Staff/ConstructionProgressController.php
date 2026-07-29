<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff;

use App\Enums\ConstructionStage;
use App\Enums\PermissionName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreConstructionProgressRequest;
use App\Models\ConstructionProgressUpdate;
use App\Models\House;
use App\Models\PropertyAllocation;
use App\Models\Purchase;
use App\Models\User;
use App\Services\Construction\ConstructionProgressService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ConstructionProgressController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $this->ensureViewAccess($user);

        $updates = ConstructionProgressUpdate::query()
            ->with([
                'house',
                'purchase.customer',
                'propertyAllocation.customer',
                'recordedBy',
            ])
            ->latest('occurred_at')
            ->paginate(
                (int) config(
                    'dems.pagination.per_page',
                    15,
                ),
            );

        $houses = House::query()
            ->orderBy('property_code')
            ->get([
                'id',
                'property_code',
                'name',
            ]);

        $purchases = Purchase::query()
            ->with([
                'customer:id,name,email',
                'house:id,name,property_code',
            ])
            ->latest('initiated_at')
            ->limit(200)
            ->get();

        $allocations = PropertyAllocation::query()
            ->with([
                'customer:id,name,email',
                'house:id,name,property_code',
                'purchase:id,reference',
            ])
            ->latest('allocated_at')
            ->limit(200)
            ->get();

        $constructionStages = ConstructionStage::cases();

        $canManageConstruction = $user->can(
            PermissionName::ConstructionManage->value,
        );

        return view(
            'staff.construction.index',
            compact(
                'updates',
                'houses',
                'purchases',
                'allocations',
                'constructionStages',
                'canManageConstruction',
            ),
        );
    }

    public function store(
        StoreConstructionProgressRequest $request,
        ConstructionProgressService $service,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $service->record(
            $request->validated(),
            $user,
        );

        return redirect()
            ->route('staff.construction.index')
            ->with(
                'construction_status',
                'The construction progress update was recorded successfully.',
            );
    }

    private function ensureViewAccess(User $user): void
    {
        abort_unless(
            $user->can(
                PermissionName::ConstructionView->value,
            )
                || $user->can(
                    PermissionName::ConstructionManage->value,
                ),
            403,
        );
    }
}
