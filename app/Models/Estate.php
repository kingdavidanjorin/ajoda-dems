<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Estate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'address',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function phases(): HasMany
    {
        return $this->hasMany(EstatePhase::class);
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
