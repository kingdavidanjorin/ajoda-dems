<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

final class RejectPropertyReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'reservations.manage',
        ) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'review_notes' => [
                'required',
                'string',
                'max:2000',
            ],
        ];
    }
}