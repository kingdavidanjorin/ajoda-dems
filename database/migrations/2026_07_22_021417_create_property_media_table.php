<?php

declare(strict_types=1);

use App\Enums\PropertyMediaType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_media', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('house_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('type', 30)
                ->default(PropertyMediaType::Image->value);

            $table->string('disk', 50)->default('public');
            $table->string('path');
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->index([
                'house_id',
                'type',
                'is_public',
                'sort_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_media');
    }
};
