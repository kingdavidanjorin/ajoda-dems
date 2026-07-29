<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\HandoverStatus;
use App\Enums\InspectionStatus;
use App\Models\PropertyHandover;
use App\Models\PropertyInspection;
use App\Notifications\HandoverReminderNotification;
use App\Notifications\InspectionReminderNotification;
use Illuminate\Console\Command;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;


final class SendPropertyScheduleReminders extends Command
{
    protected $signature = 'dems:send-property-reminders';

    protected $description =
    'Send reminders for inspections and handovers due within 24 hours.';

    public function handle(): int
    {
        $windowStart = now();
        $windowEnd = now()->addDay();

        $inspectionCount = $this->sendInspectionReminders(
            $windowStart,
            $windowEnd,
        );

        $handoverCount = $this->sendHandoverReminders(
            $windowStart,
            $windowEnd,
        );

        $this->info(sprintf(
            'Sent %d inspection reminder(s) and %d handover reminder(s).',
            $inspectionCount,
            $handoverCount,
        ));

        return self::SUCCESS;
    }

    private function sendInspectionReminders(
        CarbonInterface $windowStart,
        CarbonInterface $windowEnd,
    ): int {
        $inspectionIds = PropertyInspection::query()
            ->where(
                'status',
                InspectionStatus::Scheduled->value,
            )
            ->whereNull('reminder_sent_at')
            ->whereBetween(
                'scheduled_at',
                [
                    $windowStart,
                    $windowEnd,
                ],
            )
            ->pluck('id');

        $sentCount = 0;

        foreach ($inspectionIds as $inspectionId) {
            DB::transaction(
                function () use (
                    $inspectionId,
                    $windowStart,
                    $windowEnd,
                    &$sentCount,
                ): void {
                    $inspection = PropertyInspection::query()
                        ->with([
                            'customer',
                            'house',
                        ])
                        ->lockForUpdate()
                        ->whereKey($inspectionId)
                        ->where(
                            'status',
                            InspectionStatus::Scheduled->value,
                        )
                        ->whereNull('reminder_sent_at')
                        ->whereBetween(
                            'scheduled_at',
                            [
                                $windowStart,
                                $windowEnd,
                            ],
                        )
                        ->first();

                    if ($inspection === null) {
                        return;
                    }

                    $inspection->customer->notify(
                        new InspectionReminderNotification(
                            $inspection,
                        ),
                    );

                    $inspection->update([
                        'reminder_sent_at' => now(),
                    ]);

                    $sentCount++;
                },
            );
        }

        return $sentCount;
    }

    private function sendHandoverReminders(
        CarbonInterface $windowStart,
        CarbonInterface $windowEnd,
    ): int {
        $handoverIds = PropertyHandover::query()
            ->where(
                'status',
                HandoverStatus::Scheduled->value,
            )
            ->whereNull('reminder_sent_at')
            ->whereBetween(
                'scheduled_at',
                [
                    $windowStart,
                    $windowEnd,
                ],
            )
            ->pluck('id');

        $sentCount = 0;

        foreach ($handoverIds as $handoverId) {
            DB::transaction(
                function () use (
                    $handoverId,
                    $windowStart,
                    $windowEnd,
                    &$sentCount,
                ): void {
                    $handover = PropertyHandover::query()
                        ->with([
                            'customer',
                            'house',
                        ])
                        ->lockForUpdate()
                        ->whereKey($handoverId)
                        ->where(
                            'status',
                            HandoverStatus::Scheduled->value,
                        )
                        ->whereNull('reminder_sent_at')
                        ->whereBetween(
                            'scheduled_at',
                            [
                                $windowStart,
                                $windowEnd,
                            ],
                        )
                        ->first();

                    if ($handover === null) {
                        return;
                    }

                    $handover->customer->notify(
                        new HandoverReminderNotification(
                            $handover,
                        ),
                    );

                    $handover->update([
                        'reminder_sent_at' => now(),
                    ]);

                    $sentCount++;
                },
            );
        }

        return $sentCount;
    }
}
