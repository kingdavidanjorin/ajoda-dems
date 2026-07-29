<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'property_inspections',
            function (Blueprint $table): void {
                $table->timestamp('reminder_sent_at')
                    ->nullable()
                    ->after('scheduled_at');

                $table->index(
                    [
                        'status',
                        'scheduled_at',
                        'reminder_sent_at',
                    ],
                    'property_inspections_reminder_index',
                );
            },
        );

        Schema::table(
            'property_handovers',
            function (Blueprint $table): void {
                $table->timestamp('reminder_sent_at')
                    ->nullable()
                    ->after('scheduled_at');

                $table->index(
                    [
                        'status',
                        'scheduled_at',
                        'reminder_sent_at',
                    ],
                    'property_handovers_reminder_index',
                );
            },
        );
    }

    public function down(): void
    {
        Schema::table(
            'property_inspections',
            function (Blueprint $table): void {
                $table->dropIndex(
                    'property_inspections_reminder_index',
                );

                $table->dropColumn('reminder_sent_at');
            },
        );

        Schema::table(
            'property_handovers',
            function (Blueprint $table): void {
                $table->dropIndex(
                    'property_handovers_reminder_index',
                );

                $table->dropColumn('reminder_sent_at');
            },
        );
    }
};
