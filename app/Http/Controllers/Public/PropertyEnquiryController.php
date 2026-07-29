<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\EnquiryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StorePropertyEnquiryRequest;
use App\Models\House;
use App\Models\PropertyEnquiry;
use Illuminate\Http\RedirectResponse;

final class PropertyEnquiryController extends Controller
{
    public function store(
        StorePropertyEnquiryRequest $request,
        House $house,
    ): RedirectResponse {
        abort_unless(
            $house->is_published,
            404,
        );

        $data = $request->validated();

        PropertyEnquiry::query()->create([
            'house_id' => $house->id,
            'user_id' => $request->user()?->id,
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'],
            'status' => EnquiryStatus::NewEnquiry->value,
        ]);

        return redirect()
            ->route('properties.show', $house)
            ->with(
                'enquiry_status',
                'Your property enquiry has been submitted successfully.',
            );
    }
}
