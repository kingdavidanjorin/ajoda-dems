<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use App\Enums\EnquiryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePropertyEnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'enquiries.manage',
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::enum(EnquiryStatus::class),
            ],
        ];
    }
}
