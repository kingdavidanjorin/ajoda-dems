<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Jobs\QueueHealthCheckJob;
use App\Models\PropertyHandover;
use App\Models\PropertyInspection;
use App\Notifications\HandoverReminderNotification;
use App\Notifications\HandoverScheduledNotification;
use App\Notifications\InspectionReminderNotification;
use App\Notifications\InspectionScheduledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Tests\TestCase;

final class QueuedNotificationConfigurationTest extends TestCase
{
    public function test_inspection_scheduled_notification_is_queued(): void
    {
        $this->assertQueuedNotification(
            new InspectionScheduledNotification(
                new PropertyInspection(),
            ),
        );
    }

    public function test_handover_scheduled_notification_is_queued(): void
    {
        $this->assertQueuedNotification(
            new HandoverScheduledNotification(
                new PropertyHandover(),
            ),
        );
    }

    public function test_inspection_reminder_notification_is_queued(): void
    {
        $this->assertQueuedNotification(
            new InspectionReminderNotification(
                new PropertyInspection(),
            ),
        );
    }

    public function test_handover_reminder_notification_is_queued(): void
    {
        $this->assertQueuedNotification(
            new HandoverReminderNotification(
                new PropertyHandover(),
            ),
        );
    }

    public function test_queue_health_check_uses_background_queue(): void
    {
        $job = new QueueHealthCheckJob();

        $this->assertInstanceOf(
            ShouldQueue::class,
            $job,
        );

        $this->assertSame(
            'health-check',
            $job->queue,
        );
    }

    private function assertQueuedNotification(
        Notification $notification,
    ): void {
        $this->assertInstanceOf(
            ShouldQueue::class,
            $notification,
        );

        $this->assertSame(
            'notifications',
            $notification->queue,
        );

        $this->assertTrue(
            $notification->afterCommit,
        );
    }
}
