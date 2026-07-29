<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\HandoverStatus;
use App\Enums\InspectionStatus;
use App\Enums\InspectionType;
use App\Enums\PropertyStatus;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use App\Models\PropertyHandover;
use App\Models\PropertyInspection;
use App\Models\User;
use App\Notifications\HandoverReminderNotification;
use App\Notifications\InspectionReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PropertyScheduleReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_due_inspection_receives_one_reminder(): void
    {
        Notification::fake();

        $customer = $this->customer();

        $inspection = $this->inspection(
            customer: $customer,
            scheduledAt: now()->addHours(12),
        );

        $this->artisan('dems:send-property-reminders')
            ->assertSuccessful();

        Notification::assertSentTo(
            $customer,
            InspectionReminderNotification::class,
        );

        $this->assertNotNull(
            $inspection->fresh()->reminder_sent_at,
        );
    }

    public function test_due_handover_receives_one_reminder(): void
    {
        Notification::fake();

        $customer = $this->customer();

        $handover = $this->handover(
            customer: $customer,
            scheduledAt: now()->addHours(18),
        );

        $this->artisan('dems:send-property-reminders')
            ->assertSuccessful();

        Notification::assertSentTo(
            $customer,
            HandoverReminderNotification::class,
        );

        $this->assertNotNull(
            $handover->fresh()->reminder_sent_at,
        );
    }

    public function test_running_command_twice_does_not_duplicate_reminders(): void
    {
        Notification::fake();

        $customer = $this->customer();

        $this->inspection(
            customer: $customer,
            scheduledAt: now()->addHours(10),
        );

        $this->artisan('dems:send-property-reminders')
            ->assertSuccessful();

        $this->artisan('dems:send-property-reminders')
            ->assertSuccessful();

        Notification::assertSentToTimes(
            $customer,
            InspectionReminderNotification::class,
            1,
        );
    }

    public function test_future_and_completed_records_are_not_reminded(): void
    {
        Notification::fake();

        $customer = $this->customer();

        $this->inspection(
            customer: $customer,
            scheduledAt: now()->addDays(3),
        );

        $this->handover(
            customer: $customer,
            scheduledAt: now()->addHours(8),
            status: HandoverStatus::Completed,
        );

        $this->artisan('dems:send-property-reminders')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }

    private function inspection(
        User $customer,
        mixed $scheduledAt,
        InspectionStatus $status = InspectionStatus::Scheduled,
    ): PropertyInspection {
        $staff = $this->staff();
        $house = $this->house();

        return PropertyInspection::withoutEvents(
            fn(): PropertyInspection =>
            PropertyInspection::query()->create([
                'reference' =>
                'AJD-INS-' . Str::upper(Str::random(10)),
                'customer_id' => $customer->id,
                'house_id' => $house->id,
                'scheduled_by' => $staff->id,
                'inspection_type' =>
                InspectionType::General->value,
                'status' => $status->value,
                'scheduled_at' => $scheduledAt,
            ]),
        );
    }

    private function handover(
        User $customer,
        mixed $scheduledAt,
        HandoverStatus $status = HandoverStatus::Scheduled,
    ): PropertyHandover {
        $staff = $this->staff();
        $house = $this->house();

        return PropertyHandover::withoutEvents(
            fn(): PropertyHandover =>
            PropertyHandover::query()->create([
                'reference' =>
                'AJD-HND-' . Str::upper(Str::random(10)),
                'customer_id' => $customer->id,
                'house_id' => $house->id,
                'scheduled_by' => $staff->id,
                'status' => $status->value,
                'scheduled_at' => $scheduledAt,
                'keys_released' => false,
            ]),
        );
    }

    private function customer(): User
    {
        return User::factory()->create([
            'account_type' => AccountType::Customer->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);
    }

    private function staff(): User
    {
        return User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);
    }

    private function house(): House
    {
        $estate = Estate::factory()->create();

        $phase = EstatePhase::factory()->create([
            'estate_id' => $estate->id,
        ]);

        $houseType = HouseType::factory()->create();

        return House::factory()->create([
            'estate_id' => $estate->id,
            'estate_phase_id' => $phase->id,
            'house_type_id' => $houseType->id,
            'status' => PropertyStatus::Available->value,
            'is_published' => true,
        ]);
    }
}
