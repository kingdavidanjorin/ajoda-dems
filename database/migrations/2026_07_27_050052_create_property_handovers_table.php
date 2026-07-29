<?php

declare(strict_types=1);

use App\Enums\HandoverStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'property_handovers',
            function (Blueprint $table): void {
                $table->id();

                $table->string('reference')->unique();

                $table->foreignId('customer_id')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->foreignId('house_id')
                    ->constrained('houses')
                    ->restrictOnDelete();

                $table->foreignId('purchase_id')
                    ->nullable()
                    ->constrained('purchases')
                    ->restrictOnDelete();

                $table->foreignId('property_allocation_id')
                    ->nullable()
                    ->constrained('property_allocations')
                    ->restrictOnDelete();

                $table->foreignId('scheduled_by')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->foreignId('assigned_to')
                    ->nullable()
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->foreignId('completed_by')
                    ->nullable()
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->string('status')
                    ->default(HandoverStatus::Scheduled->value)
                    ->index();

                $table->string('handover_condition')
                    ->nullable()
                    ->index();

                $table->timestamp('scheduled_at');
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamp('possession_released_at')->nullable();

                $table->boolean('keys_released')
                    ->default(false);

                $table->string('customer_representative_name')
                    ->nullable();

                $table->text('location_notes')->nullable();
                $table->text('customer_notes')->nullable();
                $table->text('staff_notes')->nullable();
                $table->text('outcome')->nullable();

                $table->timestamps();

                $table->index(
                    [
                        'customer_id',
                        'status',
                        'scheduled_at',
                    ],
                    'property_handovers_customer_status_schedule_index',
                );

                $table->index(
                    [
                        'assigned_to',
                        'status',
                        'scheduled_at',
                    ],
                    'property_handovers_assignee_status_schedule_index',
                );
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('property_handovers');
    }
};
