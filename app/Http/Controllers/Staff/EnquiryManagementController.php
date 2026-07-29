<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff;

use App\Enums\EnquiryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UpdatePropertyEnquiryRequest;
use App\Models\PropertyEnquiry;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class EnquiryManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

            'status' => [
                'nullable',
                Rule::enum(EnquiryStatus::class),
            ],
        ]);

        $enquiries = PropertyEnquiry::query()
            ->with([
                'house.houseType',
                'customer',
                'assignedTo',
            ])
            ->when(
                $filters['search'] ?? null,
                function (
                    Builder $query,
                    string $search,
                ): void {
                    $query->where(
                        function (Builder $query) use ($search): void {
                            $query
                                ->where(
                                    'full_name',
                                    'like',
                                    "%{$search}%",
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%",
                                )
                                ->orWhereHas(
                                    'house',
                                    fn (Builder $houseQuery): Builder => $houseQuery
                                        ->where(
                                            'property_code',
                                            'like',
                                            "%{$search}%",
                                        )
                                        ->orWhere(
                                            'name',
                                            'like',
                                            "%{$search}%",
                                        ),
                                );
                        },
                    );
                },
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
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('staff.enquiries.index', [
            'enquiries' => $enquiries,
            'statuses' => EnquiryStatus::cases(),
        ]);
    }

    public function update(
        UpdatePropertyEnquiryRequest $request,
        PropertyEnquiry $enquiry,
    ): RedirectResponse {
        $data = $request->validated();

        $status = EnquiryStatus::from(
            $data['status'],
        );

        $enquiry->update([
            'status' => $status->value,

            'assigned_to' => $enquiry->assigned_to
                ?? $request->user()->id,

            'responded_at' => $status === EnquiryStatus::NewEnquiry
                ? null
                : ($enquiry->responded_at ?? now()),
        ]);

        return back()->with(
            'status',
            'Enquiry status updated successfully.',
        );
    }
}
