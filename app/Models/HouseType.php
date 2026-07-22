<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class HouseType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'bedrooms',
        'bathrooms',
        'toilets',
        'floors',
        'plot_size_sqm',
        'building_size_sqm',
        'base_price',
        'features',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'toilets' => 'integer',
            'floors' => 'integer',
            'plot_size_sqm' => 'decimal:2',
            'building_size_sqm' => 'decimal:2',
            'base_price' => 'decimal:2',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function houses(): HasMany
    {
        return $this->hasMany(House::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
