<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use App\Enums\DocumentType;
use App\Enums\DocumentVisibility;
use App\Enums\PermissionName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

final class StorePropertyDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            PermissionName::DocumentsManage->value,
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

            'purchase_id' => [
                'nullable',
                'integer',
                'exists:purchases,id',
            ],

            'document_type' => [
                'required',
                Rule::enum(DocumentType::class),
            ],

            'visibility' => [
                'required',
                Rule::enum(DocumentVisibility::class),
            ],

            'title' => [
                'required',
                'string',
                'max:150',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'file' => [
                'required',
                File::types([
                    'pdf',
                    'jpg',
                    'jpeg',
                    'png',
                    'doc',
                    'docx',
                ])->max(10 * 1024),
            ],
        ];
    }
}
