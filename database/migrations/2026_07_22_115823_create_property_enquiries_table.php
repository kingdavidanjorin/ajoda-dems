<?php

declare(strict_types=1);

use App\Enums\EnquiryStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_enquiries', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('house_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->text('message');

            $table->string('status', 30)
                ->default(EnquiryStatus::NewEnquiry->value)
                ->index();

            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index([
                'house_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_enquiries');
    }
};
