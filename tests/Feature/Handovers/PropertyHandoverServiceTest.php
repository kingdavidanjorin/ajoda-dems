<?php

declare(strict_types=1);

namespace Tests\Feature\Handovers;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\HandoverCondition;
use App\Enums\HandoverStatus;
use App\Enums\PropertyStatus;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use App\Models\User;
use App\Services\Handovers\PropertyHandoverService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PropertyHandoverServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_handover_can_be_scheduled(): void
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
                'assigned_to' => $staff->id,
                'scheduled_at' => now()->addDays(3),
                'customer_representative_name' =>
                'Test Customer',
                'location_notes' =>
                'Meet at the estate administrative office.',
                'customer_notes' =>
                'Customer should bring valid identification.',
            ],
            $staff,
        );

        $this->assertStringStartsWith(
            'AJD-HND-',
            $handover->reference,
        );

        $this->assertSame(
            HandoverStatus::Scheduled,
            $handover->status,
        );

        $this->assertFalse($handover->keys_released);

        $this->assertDatabaseHas(
            'property_handovers',
            [
                'id' => $handover->id,
                'customer_id' => $customer->id,
                'house_id' => $house->id,
                'scheduled_by' => $staff->id,
                'assigned_to' => $staff->id,
                'status' =>
                HandoverStatus::Scheduled->value,
                'keys_released' => false,
            ],
        );
    }

    public function test_scheduled_handover_can_be_completed(): void
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
                'scheduled_at' => now()->addDay(),
            ],
            $staff,
        );

        $completed = app(
            PropertyHandoverService::class,
        )->complete(
            $handover,
            $staff,
            HandoverCondition::Accepted,
            'The property was handed over successfully.',
            true,
            'All keys and access documents were released.',
        );

        $this->assertSame(
            HandoverStatus::Completed,
            $completed->status,
        );

        $this->assertSame(
            HandoverCondition::Accepted,
            $completed->handover_condition,
        );

        $this->assertTrue($completed->keys_released);

        $this->assertSame(
            $staff->id,
            $completed->completed_by,
        );

        $this->assertNotNull(
            $completed->completed_at,
        );

        $this->assertNotNull(
            $completed->possession_released_at,
        );

        $this->assertDatabaseHas(
            'property_handovers',
            [
                'id' => $handover->id,
                'status' =>
                HandoverStatus::Completed->value,
                'handover_condition' =>
                HandoverCondition::Accepted->value,
                'completed_by' => $staff->id,
                'keys_released' => true,
                'outcome' =>
                'The property was handed over successfully.',
            ],
        );
    }

    public function test_repeated_completion_does_not_replace_handover_result(): void
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
                'scheduled_at' => now()->addDay(),
            ],
            $staff,
        );

        $service = app(PropertyHandoverService::class);

        $service->complete(
            $handover,
            $staff,
            HandoverCondition::Accepted,
            'Original handover outcome.',
            true,
        );

        $service->complete(
            $handover->fresh(),
            $staff,
            HandoverCondition::RectificationRequired,
            'This should not replace the original outcome.',
            false,
        );

        $this->assertDatabaseCount(
            'property_handovers',
            1,
        );

        $this->assertDatabaseHas(
            'property_handovers',
            [
                'id' => $handover->id,
                'handover_condition' =>
                HandoverCondition::Accepted->value,
                'outcome' => 'Original handover outcome.',
                'keys_released' => true,
            ],
        );
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
