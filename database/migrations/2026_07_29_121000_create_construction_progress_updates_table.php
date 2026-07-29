<?php

declare(strict_types=1);

use App\Enums\ConstructionStage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'construction_progress_updates',
            function (Blueprint $table): void {
                $table->id();

                $table->string('reference')->unique();

                $table->foreignId('house_id')
                    ->constrained('houses')
                    ->restrictOnDelete();

                $table->foreignId('purchase_id')
                    ->nullable()
                    ->constrained('purchases')
                    ->nullOnDelete();

                $table->foreignId('property_allocation_id')
                    ->nullable()
                    ->constrained('property_allocations')
                    ->nullOnDelete();

                $table->foreignId('recorded_by')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->string('stage')
                    ->default(ConstructionStage::Planning->value)
                    ->index();

                $table->unsignedTinyInteger(
                    'progress_percentage',
                )->default(0);

                $table->string('title', 150);

                $table->text('summary');

                $table->text('internal_notes')
                    ->nullable();

                $table->boolean('customer_visible')
                    ->default(false)
                    ->index();

                $table->date('expected_completion_date')
                    ->nullable();

                $table->timestamp('occurred_at')
                    ->index();

                $table->timestamps();

                $table->index(
                    [
                        'house_id',
                        'occurred_at',
                    ],
                    'construction_updates_house_date_idx',
                );

                $table->index(
                    [
                        'purchase_id',
                        'customer_visible',
                    ],
                    'construction_updates_purchase_visible_idx',
                );

                $table->index(
                    [
                        'property_allocation_id',
                        'customer_visible',
                    ],
                    'construction_updates_allocation_visible_idx',
                );
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'construction_progress_updates',
        );
    }
};
