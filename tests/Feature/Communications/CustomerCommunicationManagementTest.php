<?php

declare(strict_types=1);

namespace Tests\Feature\Communications;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\CommunicationChannel;
use App\Enums\CommunicationDirection;
use App\Enums\CommunicationStatus;
use App\Models\CustomerCommunication;
use App\Models\User;
use App\Services\Communications\CustomerCommunicationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CustomerCommunicationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_service_officer_can_open_workspace(): void
    {
        $staff = $this->customerServiceOfficer();

        $this->actingAs($staff)
            ->get(route('staff.communications.index'))
            ->assertOk()
            ->assertSeeText('Record communication')
            ->assertSeeText('Communication history');
    }

    public function test_customer_service_officer_can_record_follow_up(): void
    {
        $staff = $this->customerServiceOfficer();
        $customer = $this->customer();

        $this->actingAs($staff)
            ->post(
                route('staff.communications.store'),
                [
                    'customer_id' => $customer->id,
                    'assigned_to' => $staff->id,
                    'channel' =>
                    CommunicationChannel::PhoneCall->value,
                    'direction' =>
                    CommunicationDirection::Incoming->value,
                    'subject' => 'Property inspection request',
                    'summary' =>
                    'Customer requested a property inspection appointment.',
                    'customer_visible' => true,
                    'occurred_at' => now()->toDateTimeString(),
                    'follow_up_at' =>
                    now()->addDay()->toDateTimeString(),
                ],
            )
            ->assertRedirect(
                route('staff.communications.index'),
            )
            ->assertSessionHas('communication_status');

        $this->assertDatabaseHas(
            'customer_communications',
            [
                'customer_id' => $customer->id,
                'recorded_by' => $staff->id,
                'assigned_to' => $staff->id,
                'status' =>
                CommunicationStatus::FollowUpPending->value,
                'subject' => 'Property inspection request',
                'customer_visible' => true,
            ],
        );
    }

    public function test_customer_service_officer_can_complete_follow_up(): void
    {
        $staff = $this->customerServiceOfficer();
        $customer = $this->customer();

        $communication = app(
            CustomerCommunicationService::class,
        )->record(
            [
                'customer_id' => $customer->id,
                'assigned_to' => $staff->id,
                'channel' =>
                CommunicationChannel::Email->value,
                'direction' =>
                CommunicationDirection::Outgoing->value,
                'subject' => 'Inspection confirmation',
                'summary' =>
                'Inspection details were sent to the customer.',
                'occurred_at' => now(),
                'follow_up_at' => now()->addDay(),
            ],
            $staff,
        );

        $this->actingAs($staff)
            ->patch(
                route(
                    'staff.communications.complete',
                    $communication,
                ),
            )
            ->assertRedirect(
                route('staff.communications.index'),
            );

        $this->assertDatabaseHas(
            'customer_communications',
            [
                'id' => $communication->id,
                'status' =>
                CommunicationStatus::Completed->value,
                'completed_by' => $staff->id,
            ],
        );
    }

    public function test_unrelated_staff_cannot_open_workspace(): void
    {
        $staff = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $staff->assignRole('Sales Officer');

        $this->actingAs($staff)
            ->get(route('staff.communications.index'))
            ->assertForbidden();
    }

    private function customerServiceOfficer(): User
    {
        $staff = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $staff->assignRole('Customer Service Officer');

        return $staff;
    }

    private function customer(): User
    {
        $customer = User::factory()->create([
            'account_type' => AccountType::Customer->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $customer->assignRole('Customer');

        return $customer;
    }
}
