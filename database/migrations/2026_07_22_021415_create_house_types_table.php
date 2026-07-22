<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('house_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 30)->unique();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('bedrooms')->default(0);
            $table->unsignedTinyInteger('bathrooms')->default(0);
            $table->unsignedTinyInteger('toilets')->default(0);
            $table->unsignedTinyInteger('floors')->default(1);
            $table->decimal('plot_size_sqm', 12, 2)->nullable();
            $table->decimal('building_size_sqm', 12, 2)->nullable();
            $table->decimal('base_price', 15, 2);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index([
                'is_active',
                'bedrooms',
                'base_price',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('house_types');
    }
};
