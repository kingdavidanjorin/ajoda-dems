<?php

declare(strict_types=1);

use App\Enums\DocumentStatus;
use App\Enums\DocumentVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_documents', function (Blueprint $table): void {
            $table->id();

            $table->string('reference')->unique();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('purchase_id')
                ->nullable()
                ->constrained('purchases')
                ->restrictOnDelete();

            $table->foreignId('property_allocation_id')
                ->nullable()
                ->constrained('property_allocations')
                ->restrictOnDelete();

            $table->foreignId('house_id')
                ->nullable()
                ->constrained('houses')
                ->restrictOnDelete();

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('document_type')->index();

            $table->string('visibility')
                ->default(DocumentVisibility::StaffOnly->value)
                ->index();

            $table->string('status')
                ->default(DocumentStatus::Active->value)
                ->index();

            $table->string('title');
            $table->string('original_name');

            $table->string('disk')
                ->default('local');

            $table->string('path')
                ->unique();

            $table->string('mime_type');
            $table->string('file_extension', 20)->nullable();

            $table->unsignedBigInteger('size_bytes');

            $table->string('checksum_sha256', 64)
                ->nullable()
                ->index();

            $table->text('description')->nullable();

            $table->timestamp('uploaded_at');
            $table->timestamp('archived_at')->nullable();

            $table->timestamps();

            $table->index(
                [
                    'customer_id',
                    'visibility',
                    'status',
                ],
                'property_documents_customer_visibility_status_index',
            );

            $table->index(
                [
                    'purchase_id',
                    'document_type',
                ],
                'property_documents_purchase_type_index',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_documents');
    }
};
