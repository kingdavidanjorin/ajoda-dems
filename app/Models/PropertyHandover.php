<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\HandoverCondition;
use App\Enums\HandoverStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Observers\PropertyHandoverObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([PropertyHandoverObserver::class])]
final class PropertyHandover extends Model
{
    protected $fillable = [
        'reference',
        'reminder_sent_at',
        'customer_id',
        'house_id',
        'purchase_id',
        'property_allocation_id',
        'scheduled_by',
        'assigned_to',
        'completed_by',
        'status',
        'handover_condition',
        'scheduled_at',
        'completed_at',
        'cancelled_at',
        'possession_released_at',
        'keys_released',
        'customer_representative_name',
        'location_notes',
        'customer_notes',
        'staff_notes',
        'outcome',
    ];

    protected function casts(): array
    {
        return [
            'status' => HandoverStatus::class,
            'handover_condition' => HandoverCondition::class,
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'possession_released_at' => 'datetime',
            'keys_released' => 'boolean',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'customer_id',
        );
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function propertyAllocation(): BelongsTo
    {
        return $this->belongsTo(
            PropertyAllocation::class,
            'property_allocation_id',
        );
    }

    public function scheduledBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'scheduled_by',
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

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query
            ->where(
                'status',
                HandoverStatus::Scheduled->value,
            )
            ->where('scheduled_at', '>=', now());
    }
}
