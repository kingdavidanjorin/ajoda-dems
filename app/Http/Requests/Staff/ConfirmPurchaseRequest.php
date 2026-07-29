<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

final class ConfirmPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'purchases.confirm',
        ) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
