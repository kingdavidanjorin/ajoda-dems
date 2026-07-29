<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use App\Enums\ConstructionStage;
use App\Enums\PermissionName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreConstructionProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            PermissionName::ConstructionManage->value,
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'house_id' => [
                'required',
                'integer',
                'exists:houses,id',
            ],

            'purchase_id' => [
                'nullable',
                'integer',
                'exists:purchases,id',
            ],

            'property_allocation_id' => [
                'nullable',
                'integer',
                'exists:property_allocations,id',
            ],

            'stage' => [
                'required',
                Rule::enum(ConstructionStage::class),
            ],

            'progress_percentage' => [
                'required',
                'integer',
                'between:0,100',
            ],

            'title' => [
                'required',
                'string',
                'max:150',
            ],

            'summary' => [
                'required',
                'string',
                'max:5000',
            ],

            'internal_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'customer_visible' => [
                'nullable',
                'boolean',
            ],

            'expected_completion_date' => [
                'nullable',
                'date',
            ],

            'occurred_at' => [
                'required',
                'date',
                'before_or_equal:now',
            ],
        ];
    }
}
