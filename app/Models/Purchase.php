<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentPlanType;
use App\Enums\PaymentStatus;
use App\Enums\PurchaseStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'reservation_id',
        'house_id',
        'active_house_id',
        'customer_id',
        'initiated_by',
        'status',
        'approved_price',
        'payment_plan_type',
        'effective_date',
        'sales_notes',
        'initiated_at',
        'confirmed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PurchaseStatus::class,
            'payment_plan_type' => PaymentPlanType::class,
            'approved_price' => 'decimal:2',
            'effective_date' => 'date',
            'initiated_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(
            PropertyReservation::class,
            'reservation_id',
        );
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function activeHouse(): BelongsTo
    {
        return $this->belongsTo(
            House::class,
            'active_house_id',
        );
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'customer_id',
        );
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'initiated_by',
        );
    }

    public function scopeActive(
        Builder $query,
    ): Builder {
        return $query->whereNotNull(
            'active_house_id',
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function verifiedPayments(): HasMany
    {
        return $this->hasMany(Payment::class)
            ->where(
                'status',
                PaymentStatus::Verified->value,
            );
    }

    public function verifiedAmount(): string
    {
        return (string) $this->payments()
            ->verified()
            ->sum('amount');
    }

    public function allocation(): HasOne
    {
        return $this->hasOne(
            PropertyAllocation::class,
            'purchase_id',
        );
    }

    public function constructionProgressUpdates(): HasMany
    {
        return $this->hasMany(
            ConstructionProgressUpdate::class,
            'purchase_id',
        )->latest('occurred_at');
    }
}
