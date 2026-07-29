<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use App\Enums\PaymentPlanType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class InitiatePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('sales.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'approved_price' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999999999999.99',
            ],
            'payment_plan_type' => [
                'required',
                Rule::enum(PaymentPlanType::class),
            ],
            'sales_notes' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ];
    }
}