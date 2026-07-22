<?php

declare(strict_types=1);

use App\Enums\PropertyStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('houses', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('estate_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('estate_phase_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('house_type_id')
                ->constrained();

            $table->string('property_code', 50)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('plot_number', 50);
            $table->string('house_number', 50)->nullable();
            $table->text('description')->nullable();

            $table->decimal('price_override', 15, 2)->nullable();

            $table->string('status', 40)
                ->default(PropertyStatus::Available->value);

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false);

            $table->decimal('layout_x', 10, 4)->nullable();
            $table->decimal('layout_y', 10, 4)->nullable();

            $table->timestamp('reserved_until')->nullable();
            $table->timestamps();

            $table->unique(['estate_id', 'plot_number']);
            $table->unique(['estate_id', 'house_number']);

            $table->index([
                'estate_id',
                'status',
                'is_published',
            ]);

            $table->index([
                'house_type_id',
                'status',
            ]);

            $table->index('reserved_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('houses');
    }
};
