<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AllocationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PropertyAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'purchase_id',
        'house_id',
        'customer_id',
        'allocated_by',
        'status',
        'allocated_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => AllocationStatus::class,
            'allocated_at' => 'datetime',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
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

    public function allocatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'allocated_by',
        );
    }

    public function constructionProgressUpdates(): HasMany
    {
        return $this->hasMany(
            ConstructionProgressUpdate::class,
            'property_allocation_id',
        )->latest('occurred_at');
    }
}
