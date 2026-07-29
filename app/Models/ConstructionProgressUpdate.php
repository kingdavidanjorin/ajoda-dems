<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ConstructionStage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ConstructionProgressUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'house_id',
        'purchase_id',
        'property_allocation_id',
        'recorded_by',
        'stage',
        'progress_percentage',
        'title',
        'summary',
        'internal_notes',
        'customer_visible',
        'expected_completion_date',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'stage' => ConstructionStage::class,
            'progress_percentage' => 'integer',
            'customer_visible' => 'boolean',
            'expected_completion_date' => 'date',
            'occurred_at' => 'datetime',
        ];
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

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'recorded_by',
        );
    }
}
