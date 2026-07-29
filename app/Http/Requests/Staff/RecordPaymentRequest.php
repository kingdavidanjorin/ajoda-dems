<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('payments.record') ?? false;
    }

    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999999999999.99',
            ],
            'payment_method' => [
                'required',
                Rule::enum(PaymentMethod::class),
            ],
            'transaction_reference' => [
                'nullable',
                'string',
                'max:120',
            ],
            'paid_at' => [
                'required',
                'date',
                'before_or_equal:now',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ];
    }
}