<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Enums\DocumentVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PropertyDocument extends Model
{
    protected $fillable = [
        'reference',
        'customer_id',
        'purchase_id',
        'property_allocation_id',
        'house_id',
        'uploaded_by',
        'document_type',
        'visibility',
        'status',
        'title',
        'original_name',
        'disk',
        'path',
        'mime_type',
        'file_extension',
        'size_bytes',
        'checksum_sha256',
        'description',
        'uploaded_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'visibility' => DocumentVisibility::class,
            'status' => DocumentStatus::class,
            'size_bytes' => 'integer',
            'uploaded_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'customer_id',
        );
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

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by',
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(
            'status',
            DocumentStatus::Active->value,
        );
    }

    public function scopeCustomerVisible(Builder $query): Builder
    {
        return $query->where(
            'visibility',
            DocumentVisibility::CustomerVisible->value,
        );
    }
}
