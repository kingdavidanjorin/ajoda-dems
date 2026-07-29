<?php

declare(strict_types=1);

use App\Enums\ReservationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'property_reservations',
            function (Blueprint $table): void {
                $table->id();

                $table->string('reference', 40)
                    ->unique();

                $table->foreignId('house_id')
                    ->constrained('houses')
                    ->restrictOnDelete();

                $table->foreignId('customer_id')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->foreignId('property_enquiry_id')
                    ->nullable()
                    ->constrained('property_enquiries')
                    ->nullOnDelete();

                $table->foreignId('reviewed_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string('status', 30)
                    ->default(
                        ReservationStatus::PendingReview->value,
                    )
                    ->index();

                $table->text('customer_notes')
                    ->nullable();

                $table->text('review_notes')
                    ->nullable();

                $table->timestamp('approved_at')
                    ->nullable();

                $table->timestamp('rejected_at')
                    ->nullable();

                $table->timestamp('cancelled_at')
                    ->nullable();

                $table->timestamp('expires_at')
                    ->nullable()
                    ->index();

                $table->timestamps();

                $table->index([
                    'house_id',
                    'status',
                ]);

                $table->index([
                    'customer_id',
                    'status',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('property_reservations');
    }
};
