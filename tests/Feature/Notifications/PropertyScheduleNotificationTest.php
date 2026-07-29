<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\InspectionType;
use App\Enums\PropertyStatus;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use App\Models\User;
use App\Notifications\HandoverScheduledNotification;
use App\Notifications\InspectionScheduledNotification;
use App\Services\Handovers\PropertyHandoverService;
use App\Services\Inspections\PropertyInspectionService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;


final class PropertyScheduleNotificationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_scheduling_inspection_creates_customer_notification(): void
    {
        $customer = $this->customer();
        $staff = $this->staff();
        $house = $this->house();

        $inspection = app(
            PropertyInspectionService::class,
        )->schedule(
            [
                'customer_id' => $customer->id,
                'house_id' => $house->id,
                'inspection_type' =>
                InspectionType::General->value,
                'scheduled_at' => now()->addDays(2),
            ],
            $staff,
        );

        $notification = $customer
            ->fresh()
            ->notifications()
            ->firstOrFail();

        $this->assertSame(
            InspectionScheduledNotification::class,
            $notification->type,
        );

        $this->assertSame(
            'inspection_scheduled',
            $notification->data['event'],
        );

        $this->assertSame(
            $inspection->reference,
            $notification->data['reference'],
        );

        $this->assertSame(
            $house->property_code,
            $notification->data['property_code'],
        );

        $this->assertNull($notification->read_at);
    }

    public function test_scheduling_handover_creates_customer_notification(): void
    {
        $customer = $this->customer();
        $staff = $this->staff();
        $house = $this->house();

        $handover = app(
            PropertyHandoverService::class,
        )->schedule(
            [
                'customer_id' => $customer->id,
                'house_id' => $house->id,
                'scheduled_at' => now()->addDays(3),
            ],
            $staff,
        );

        $notification = $customer
            ->fresh()
            ->notifications()
            ->firstOrFail();

        $this->assertSame(
            HandoverScheduledNotification::class,
            $notification->type,
        );

        $this->assertSame(
            'handover_scheduled',
            $notification->data['event'],
        );

        $this->assertSame(
            $handover->reference,
            $notification->data['reference'],
        );

        $this->assertSame(
            $house->property_code,
            $notification->data['property_code'],
        );

        $this->assertNull($notification->read_at);
    }

    private function customer(): User
    {
        return User::factory()->create([
            'account_type' =>
            AccountType::Customer->value,
            'account_status' =>
            AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);
    }

    private function staff(): User
    {
        return User::factory()->create([
            'account_type' =>
            AccountType::Staff->value,
            'account_status' =>
            AccountStatus::Active->value,
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
