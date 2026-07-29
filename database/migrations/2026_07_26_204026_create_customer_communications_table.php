<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'customer_communications',
            function (Blueprint $table): void {
                $table->id();

                $table->string('reference')->unique();

                $table->foreignId('customer_id')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->foreignId('recorded_by')
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

                $table->foreignId('property_enquiry_id')
                    ->nullable()
                    ->constrained('property_enquiries')
                    ->restrictOnDelete();

                $table->foreignId('purchase_id')
                    ->nullable()
                    ->constrained('purchases')
                    ->restrictOnDelete();

                $table->foreignId('house_id')
                    ->nullable()
                    ->constrained('houses')
                    ->restrictOnDelete();

                $table->string('channel')->index();
                $table->string('direction')->index();

                $table->string('status')
                    ->default('recorded')
                    ->index();

                $table->string('subject');
                $table->text('summary');

                $table->boolean('customer_visible')
                    ->default(false)
                    ->index();

                $table->timestamp('occurred_at');
                $table->timestamp('follow_up_at')->nullable();
                $table->timestamp('completed_at')->nullable();

                $table->timestamps();

                $table->index(
                    [
                        'customer_id',
                        'occurred_at',
                    ],
                    'customer_communications_customer_occurred_index',
                );

                $table->index(
                    [
                        'assigned_to',
                        'status',
                        'follow_up_at',
                    ],
                    'customer_communications_follow_up_index',
                );
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'customer_communications',
        );
    }
};