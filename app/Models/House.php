<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PropertyStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class House extends Model
{
    use HasFactory;

    protected $fillable = [
        'estate_id',
        'estate_phase_id',
        'house_type_id',
        'property_code',
        'name',
        'slug',
        'plot_number',
        'house_number',
        'description',
        'price_override',
        'status',
        'is_featured',
        'is_published',
        'layout_x',
        'layout_y',
        'reserved_until',
    ];

    protected function casts(): array
    {
        return [
            'price_override' => 'decimal:2',
            'status' => PropertyStatus::class,
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'layout_x' => 'decimal:4',
            'layout_y' => 'decimal:4',
            'reserved_until' => 'datetime',
        ];
    }

    public function estate(): BelongsTo
    {
        return $this->belongsTo(Estate::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(
            EstatePhase::class,
            'estate_phase_id',
        );
    }

    public function houseType(): BelongsTo
    {
        return $this->belongsTo(HouseType::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(PropertyMedia::class)
            ->orderBy('sort_order');
    }

    public function primaryMedia(): HasOne
    {
        return $this->hasOne(PropertyMedia::class)
            ->where('is_primary', true);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(HouseStatusHistory::class)
            ->orderByDesc('changed_at');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where(
            'status',
            PropertyStatus::Available->value,
        );
    }

    public function effectivePrice(): string
    {
        return $this->price_override
            ?? $this->houseType->base_price;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
