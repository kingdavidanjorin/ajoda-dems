<?php

declare(strict_types=1);

use App\Enums\AllocationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'property_allocations',
            function (Blueprint $table): void {
                $table->id();

                $table->string('reference', 40)
                    ->unique();

                $table->foreignId('purchase_id')
                    ->unique()
                    ->constrained('purchases')
                    ->restrictOnDelete();

                $table->foreignId('house_id')
                    ->unique()
                    ->constrained('houses')
                    ->restrictOnDelete();

                $table->foreignId('customer_id')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->foreignId('allocated_by')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->string('status', 30)
                    ->default(AllocationStatus::Active->value)
                    ->index();

                $table->timestamp('allocated_at');

                $table->text('notes')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'customer_id',
                    'status',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('property_allocations');
    }
};
