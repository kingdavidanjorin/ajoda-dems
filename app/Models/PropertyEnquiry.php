<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EnquiryStatus;
use Database\Factories\PropertyEnquiryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PropertyEnquiry extends Model
{
    /** @use HasFactory<PropertyEnquiryFactory> */
    use HasFactory;

    protected $fillable = [
        'house_id',
        'user_id',
        'assigned_to',
        'full_name',
        'email',
        'phone',
        'message',
        'status',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => EnquiryStatus::class,
            'responded_at' => 'datetime',
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
            'user_id',
        );
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to',
        );
    }
}
