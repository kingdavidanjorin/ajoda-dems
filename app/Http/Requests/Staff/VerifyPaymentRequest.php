<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

final class VerifyPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('payments.verify') ?? false;
    }

    public function rules(): array
    {
        return [
            'verification_notes' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ];
    }
}