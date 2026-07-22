<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('house_status_histories', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('house_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->index([
                'house_id',
                'changed_at',
            ]);

            $table->index([
                'to_status',
                'changed_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('house_status_histories');
    }
};
