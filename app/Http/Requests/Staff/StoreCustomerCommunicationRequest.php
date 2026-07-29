<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use App\Enums\CommunicationChannel;
use App\Enums\CommunicationDirection;
use App\Enums\PermissionName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreCustomerCommunicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            PermissionName::MessagesManage->value,
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

            'assigned_to' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'property_enquiry_id' => [
                'nullable',
                'integer',
                'exists:property_enquiries,id',
            ],

            'purchase_id' => [
                'nullable',
                'integer',
                'exists:purchases,id',
            ],

            'house_id' => [
                'nullable',
                'integer',
                'exists:houses,id',
            ],

            'channel' => [
                'required',
                Rule::enum(CommunicationChannel::class),
            ],

            'direction' => [
                'required',
                Rule::enum(CommunicationDirection::class),
            ],

            'subject' => [
                'required',
                'string',
                'max:150',
            ],

            'summary' => [
                'required',
                'string',
                'max:5000',
            ],

            'customer_visible' => [
                'nullable',
                'boolean',
            ],

            'occurred_at' => [
                'required',
                'date',
            ],

            'follow_up_at' => [
                'nullable',
                'date',
                'after_or_equal:occurred_at',
            ],
        ];
    }
}
