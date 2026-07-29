<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\PermissionName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StorePropertyInspectionRequest;
use App\Models\House;
use App\Models\PropertyInspection;
use App\Models\User;
use App\Services\Inspections\PropertyInspectionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PropertyInspectionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $this->ensureViewAccess($user);

        $inspections = PropertyInspection::query()
            ->with([
                'customer',
                'house',
                'scheduledBy',
                'assignedTo',
                'completedBy',
            ])
            ->latest('scheduled_at')
            ->paginate(15);

        $customers = User::query()
            ->where(
                'account_type',
                AccountType::Customer->value,
            )
            ->where(
                'account_status',
                AccountStatus::Active->value,
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
            ]);

        $staffMembers = User::query()
            ->where(
                'account_type',
                AccountType::Staff->value,
            )
            ->where(
                'account_status',
                AccountStatus::Active->value,
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
            ]);

        $houses = House::query()
            ->orderBy('property_code')
            ->get([
                'id',
                'property_code',
                'name',
            ]);

        $canManageInspections = $user->can(
            PermissionName::InspectionsManage->value,
        );

        return view(
            'staff.inspections.index',
            compact(
                'inspections',
                'customers',
                'staffMembers',
                'houses',
                'canManageInspections',
            ),
        );
    }

    public function store(
        StorePropertyInspectionRequest $request,
        PropertyInspectionService $service,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $service->schedule(
            $request->validated(),
            $user,
        );

        return redirect()
            ->route('staff.inspections.index')
            ->with(
                'inspection_status',
                'The property inspection was scheduled successfully.',
            );
    }

    public function complete(
        Request $request,
        PropertyInspection $inspection,
        PropertyInspectionService $service,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $this->ensureManageAccess($user);

        $validated = $request->validate([
            'outcome' => [
                'required',
                'string',
                'max:5000',
            ],

            'staff_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $service->complete(
            $inspection,
            $user,
            $validated['outcome'],
            $validated['staff_notes'] ?? null,
        );

        return redirect()
            ->route('staff.inspections.index')
            ->with(
                'inspection_status',
                'The property inspection was completed successfully.',
            );
    }

    private function ensureViewAccess(User $user): void
    {
        abort_unless(
            $user->can(PermissionName::InspectionsView->value)
                || $user->can(PermissionName::InspectionsManage->value),
            403,
        );
    }

    private function ensureManageAccess(User $user): void
    {
        abort_unless(
            $user->can(PermissionName::InspectionsManage->value),
            403,
        );
    }
}
