<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PropertyMediaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PropertyMedia extends Model
{
    use HasFactory;

    protected $table = 'property_media';

    protected $fillable = [
        'house_id',
        'type',
        'disk',
        'path',
        'alt_text',
        'caption',
        'sort_order',
        'is_primary',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'type' => PropertyMediaType::class,
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
            'is_public' => 'boolean',
        ];
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }
}
