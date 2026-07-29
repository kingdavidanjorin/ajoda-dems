<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

final class StorePropertyReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'reservations.create',
        ) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}