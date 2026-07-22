<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estate_phases', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('estate_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('slug');
            $table->string('code', 30);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['estate_id', 'slug']);
            $table->unique(['estate_id', 'code']);
            $table->index(['estate_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estate_phases');
    }
};
