<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use App\Enums\PermissionName;
use Illuminate\Foundation\Http\FormRequest;

final class StorePropertyHandoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            PermissionName::HandoverManage->value,
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'house_id' => [
                'required',
                'integer',
                'exists:houses,id',
            ],

            'assigned_to' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'scheduled_at' => [
                'required',
                'date',
                'after:now',
            ],

            'customer_representative_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'location_notes' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'customer_notes' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'staff_notes' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ];
    }
}
