<?php

declare(strict_types=1);

use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();

            $table->string('reference', 40)
                ->unique();

            $table->foreignId('purchase_id')
                ->constrained('purchases')
                ->restrictOnDelete();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('recorded_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('status', 40)
                ->default(
                    PaymentStatus::PendingVerification->value,
                )
                ->index();

            $table->decimal('amount', 15, 2);

            $table->string('payment_method', 40);

            $table->string('transaction_reference', 120)
                ->nullable()
                ->unique();

            $table->timestamp('paid_at');

            $table->text('notes')
                ->nullable();

            $table->text('verification_notes')
                ->nullable();

            $table->timestamp('verified_at')
                ->nullable();

            $table->timestamp('rejected_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'purchase_id',
                'status',
            ]);

            $table->index([
                'customer_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
