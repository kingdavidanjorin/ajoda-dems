<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\RoleName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => Str::lower(
                trim((string) $this->input('email')),
            ),
            'phone' => $this->filled('phone')
                ? trim((string) $this->input('phone'))
                : null,
            'job_title' => trim(
                (string) $this->input('job_title'),
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id'),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'job_title' => [
                'required',
                'string',
                'max:150',
            ],

            'employment_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'roles' => [
                'required',
                'array',
                'min:1',
            ],

            'roles.*' => [
                'required',
                'string',
                'distinct',
                Rule::in(RoleName::assignableStaffValues()),
            ],
        ];
    }
}
