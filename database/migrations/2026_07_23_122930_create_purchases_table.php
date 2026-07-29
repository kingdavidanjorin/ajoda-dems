<?php

declare(strict_types=1);

use App\Enums\PurchaseStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'purchases',
            function (Blueprint $table): void {
                $table->id();

                $table->string('reference', 40)
                    ->unique();

                $table->foreignId('reservation_id')
                    ->unique()
                    ->constrained('property_reservations')
                    ->restrictOnDelete();

                $table->foreignId('house_id')
                    ->constrained('houses')
                    ->restrictOnDelete();

                /*
                 * This nullable unique column represents the purchase
                 * currently controlling the house.
                 *
                 * Cancelled purchases can clear it, allowing a future
                 * authorised purchase attempt.
                 */
                $table->foreignId('active_house_id')
                    ->nullable()
                    ->unique()
                    ->constrained('houses')
                    ->restrictOnDelete();

                $table->foreignId('customer_id')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->foreignId('initiated_by')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->string('status', 40)
                    ->default(
                        PurchaseStatus::AwaitingPayment->value,
                    )
                    ->index();

                $table->decimal(
                    'approved_price',
                    15,
                    2,
                );

                $table->string(
                    'payment_plan_type',
                    30,
                );

                $table->date('effective_date');

                $table->text('sales_notes')
                    ->nullable();

                $table->timestamp('initiated_at');
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();

                $table->timestamps();

                $table->index([
                    'customer_id',
                    'status',
                ]);

                $table->index([
                    'house_id',
                    'status',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
