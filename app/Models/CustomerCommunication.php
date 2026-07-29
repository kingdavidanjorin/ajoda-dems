<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CommunicationChannel;
use App\Enums\CommunicationDirection;
use App\Enums\CommunicationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CustomerCommunication extends Model
{
    protected $fillable = [
        'reference',
        'customer_id',
        'recorded_by',
        'assigned_to',
        'completed_by',
        'property_enquiry_id',
        'purchase_id',
        'house_id',
        'channel',
        'direction',
        'status',
        'subject',
        'summary',
        'customer_visible',
        'occurred_at',
        'follow_up_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'channel' => CommunicationChannel::class,
            'direction' => CommunicationDirection::class,
            'status' => CommunicationStatus::class,
            'customer_visible' => 'boolean',
            'occurred_at' => 'datetime',
            'follow_up_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'customer_id',
        );
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'recorded_by',
        );
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to',
        );
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'completed_by',
        );
    }

    public function propertyEnquiry(): BelongsTo
    {
        return $this->belongsTo(
            PropertyEnquiry::class,
            'property_enquiry_id',
        );
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function scopePendingFollowUp(
        Builder $query,
    ): Builder {
        return $query
            ->where(
                'status',
                CommunicationStatus::FollowUpPending->value,
            )
            ->whereNotNull('follow_up_at');
    }

    public function scopeCustomerVisible(
        Builder $query,
    ): Builder {
        return $query->where(
            'customer_visible',
            true,
        );
    }
}
