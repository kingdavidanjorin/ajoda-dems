<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use App\Enums\InspectionType;
use App\Enums\PermissionName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePropertyInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            PermissionName::InspectionsManage->value,
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

            'inspection_type' => [
                'required',
                Rule::enum(InspectionType::class),
            ],

            'scheduled_at' => [
                'required',
                'date',
                'after:now',
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
