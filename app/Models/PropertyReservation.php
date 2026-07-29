<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class PropertyReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'house_id',
        'customer_id',
        'property_enquiry_id',
        'reviewed_by',
        'status',
        'customer_notes',
        'review_notes',
        'approved_at',
        'rejected_at',
        'cancelled_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'customer_id',
        );
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(
            PropertyEnquiry::class,
            'property_enquiry_id',
        );
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by',
        );
    }

    public function scopePending(
        Builder $query,
    ): Builder {
        return $query->where(
            'status',
            ReservationStatus::PendingReview->value,
        );
    }

    public function scopeApproved(
        Builder $query,
    ): Builder {
        return $query->where(
            'status',
            ReservationStatus::Approved->value,
        );
    }
    public function purchase(): HasOne
    {
    return $this->hasOne(
        Purchase::class,
        'reservation_id',
        );
    }
}