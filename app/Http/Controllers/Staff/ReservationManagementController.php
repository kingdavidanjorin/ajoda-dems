<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ApprovePropertyReservationRequest;
use App\Http\Requests\Staff\RejectPropertyReservationRequest;
use App\Models\PropertyReservation;
use App\Services\Reservations\PropertyReservationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ReservationManagementController extends Controller
{
    public function index(Request $request): View
    {
        $reservations = PropertyReservation::query()
            ->with([
                'house.houseType',
                'customer',
                'reviewedBy',
                'purchase',
            ])
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where(
                    'status',
                    $request->string('status')->toString(),
                ),
            )
            ->latest()
            ->paginate(
                (int) config(
                    'dems.pagination.per_page',
                    15,
                ),
            )
            ->withQueryString();

        return view(
            'staff.reservations.index',
            compact('reservations'),
        );
    }

    public function approve(
        ApprovePropertyReservationRequest $request,
        PropertyReservation $reservation,
        PropertyReservationService $service,
    ): RedirectResponse {
        $approvedReservation = $service->approve(
            $reservation,
            $request->user(),
            $request->validated('review_notes'),
        );

        return back()->with(
            'status',
            "Reservation {$approvedReservation->reference} approved.",
        );
    }

    public function reject(
        RejectPropertyReservationRequest $request,
        PropertyReservation $reservation,
        PropertyReservationService $service,
    ): RedirectResponse {
        $rejectedReservation = $service->reject(
            $reservation,
            $request->user(),
            $request->validated('review_notes'),
        );

        return back()->with(
            'status',
            "Reservation {$rejectedReservation->reference} rejected.",
        );
    }
}