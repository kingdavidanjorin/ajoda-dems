<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PropertyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class HouseStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'changed_by',
        'from_status',
        'to_status',
        'reason',
        'metadata',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'from_status' => PropertyStatus::class,
            'to_status' => PropertyStatus::class,
            'metadata' => 'array',
            'changed_at' => 'datetime',
        ];
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'changed_by',
        );
    }
}
