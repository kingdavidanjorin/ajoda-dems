<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\HandoverCondition;
use App\Enums\PermissionName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StorePropertyHandoverRequest;
use App\Models\House;
use App\Models\PropertyHandover;
use App\Models\User;
use App\Services\Handovers\PropertyHandoverService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class PropertyHandoverController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $this->ensureAccess($user);

        $handovers = PropertyHandover::query()
            ->with([
                'customer',
                'house',
                'purchase',
                'propertyAllocation',
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

        return view(
            'staff.handovers.index',
            compact(
                'handovers',
                'customers',
                'staffMembers',
                'houses',
            ),
        );
    }

    public function store(
        StorePropertyHandoverRequest $request,
        PropertyHandoverService $service,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $service->schedule(
            $request->validated(),
            $user,
        );

        return redirect()
            ->route('staff.handovers.index')
            ->with(
                'handover_status',
                'The property handover was scheduled successfully.',
            );
    }

    public function complete(
        Request $request,
        PropertyHandover $handover,
        PropertyHandoverService $service,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $this->ensureAccess($user);

        $validated = $request->validate([
            'handover_condition' => [
                'required',
                Rule::enum(HandoverCondition::class),
            ],

            'outcome' => [
                'required',
                'string',
                'max:5000',
            ],

            'keys_released' => [
                'required',
                'boolean',
            ],

            'staff_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $service->complete(
            $handover,
            $user,
            HandoverCondition::from(
                $validated['handover_condition'],
            ),
            $validated['outcome'],
            (bool) $validated['keys_released'],
            $validated['staff_notes'] ?? null,
        );

        return redirect()
            ->route('staff.handovers.index')
            ->with(
                'handover_status',
                'The property handover was completed successfully.',
            );
    }

    private function ensureAccess(User $user): void
    {
        abort_unless(
            $user->can(
                PermissionName::HandoverManage->value,
            ),
            403,
        );
    }
}
