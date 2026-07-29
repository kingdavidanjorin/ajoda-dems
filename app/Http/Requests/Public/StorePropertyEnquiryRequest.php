<?php

declare(strict_types=1);

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

final class StorePropertyEnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => trim(
                (string) $this->input('full_name'),
            ),

            'email' => Str::lower(
                trim((string) $this->input('email')),
            ),

            'phone' => $this->filled('phone')
                ? trim((string) $this->input('phone'))
                : null,

            'message' => trim(
                (string) $this->input('message'),
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'full_name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'message' => [
                'required',
                'string',
                'min:10',
                'max:2000',
            ],
        ];
    }
}
