<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\PermissionName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreCustomerCommunicationRequest;
use App\Models\CustomerCommunication;
use App\Models\House;
use App\Models\PropertyEnquiry;
use App\Models\Purchase;
use App\Models\User;
use App\Services\Communications\CustomerCommunicationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class CustomerCommunicationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $this->ensureAccess($user);

        $communications = CustomerCommunication::query()
            ->with([
                'customer',
                'recordedBy',
                'assignedTo',
                'completedBy',
                'propertyEnquiry',
                'purchase',
                'house',
            ])
            ->latest('occurred_at')
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

        $enquiries = PropertyEnquiry::query()
            ->whereNotNull('user_id')
            ->with([
                'customer',
                'house',
            ])
            ->latest()
            ->limit(100)
            ->get();

        $purchases = Purchase::query()
            ->with([
                'customer',
                'house',
            ])
            ->latest('initiated_at')
            ->limit(100)
            ->get();

        $houses = House::query()
            ->orderBy('property_code')
            ->get([
                'id',
                'name',
                'property_code',
            ]);

        return view(
            'staff.communications.index',
            compact(
                'communications',
                'customers',
                'staffMembers',
                'enquiries',
                'purchases',
                'houses',
            ),
        );
    }

    public function store(
        StoreCustomerCommunicationRequest $request,
        CustomerCommunicationService $service,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $service->record(
            $request->validated(),
            $user,
        );

        return redirect()
            ->route('staff.communications.index')
            ->with(
                'communication_status',
                'The customer communication was recorded successfully.',
            );
    }

    public function complete(
        Request $request,
        CustomerCommunication $communication,
        CustomerCommunicationService $service,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $this->ensureAccess($user);

        $service->complete(
            $communication,
            $user,
        );

        return redirect()
            ->route('staff.communications.index')
            ->with(
                'communication_status',
                'The follow-up was marked as completed.',
            );
    }

    private function ensureAccess(User $user): void
    {
        abort_unless(
            $user->can(
                PermissionName::MessagesManage->value,
            ),
            403,
        );
    }
}
