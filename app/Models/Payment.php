<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'purchase_id',
        'customer_id',
        'recorded_by',
        'verified_by',
        'status',
        'amount',
        'payment_method',
        'transaction_reference',
        'paid_at',
        'notes',
        'verification_notes',
        'verified_at',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'verified_at' => 'datetime',
            'rejected_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
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

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'verified_by',
        );
    }

    public function scopeVerified(
        Builder $query,
    ): Builder {
        return $query->where(
            'status',
            PaymentStatus::Verified->value,
        );
    }
}
