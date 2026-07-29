<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StorePropertyReservationRequest;
use App\Models\House;
use App\Models\PropertyReservation;
use App\Services\Reservations\PropertyReservationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PropertyReservationController extends Controller
{
    public function index(Request $request): View
    {
        $reservations = PropertyReservation::query()
            ->with([
                'house.houseType',
                'reviewedBy',
            ])
            ->where(
                'customer_id',
                $request->user()->id,
            )
            ->latest()
            ->paginate(
                (int) config(
                    'dems.pagination.per_page',
                    15,
                ),
            );

        return view(
            'customer.reservations.index',
            compact('reservations'),
        );
    }

    public function store(
        StorePropertyReservationRequest $request,
        House $house,
        PropertyReservationService $service,
    ): RedirectResponse {
        abort_unless(
            $house->is_published,
            404,
        );

        $reservation = $service->submit(
            $house,
            $request->user(),
            $request->validated('customer_notes'),
        );

        return redirect()
            ->route('customer.reservations.index')
            ->with(
                'status',
                "Reservation request {$reservation->reference} submitted successfully.",
            );
    }
}