<?php

declare(strict_types=1);

namespace Tests\Feature\Communications;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\CommunicationChannel;
use App\Enums\CommunicationDirection;
use App\Enums\CommunicationStatus;
use App\Enums\RoleName;
use App\Models\CustomerCommunication;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CustomerCommunicationHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_can_view_customer_visible_communication(): void
    {
        $customer = $this->customer();
        $staff = $this->staff();

        $communication = $this->communication(
            customer: $customer,
            staff: $staff,
            subject: 'Property inspection confirmation',
            customerVisible: true,
        );

        $this->actingAs($customer)
            ->get(route('customer.communications.index'))
            ->assertOk()
            ->assertSeeText($communication->subject)
            ->assertSeeText($communication->reference);
    }

    public function test_customer_cannot_view_hidden_communication(): void
    {
        $customer = $this->customer();
        $staff = $this->staff();

        $visibleCommunication = $this->communication(
            customer: $customer,
            staff: $staff,
            subject: 'Visible customer update',
            customerVisible: true,
        );

        $hiddenCommunication = $this->communication(
            customer: $customer,
            staff: $staff,
            subject: 'Private internal staff note',
            customerVisible: false,
        );

        $this->actingAs($customer)
            ->get(route('customer.communications.index'))
            ->assertOk()
            ->assertSeeText($visibleCommunication->subject)
            ->assertDontSeeText($hiddenCommunication->subject);
    }

    public function test_customer_cannot_view_another_customers_communication(): void
    {
        $firstCustomer = $this->customer();
        $secondCustomer = $this->customer();
        $staff = $this->staff();

        $ownCommunication = $this->communication(
            customer: $firstCustomer,
            staff: $staff,
            subject: 'First customer message',
            customerVisible: true,
        );

        $otherCommunication = $this->communication(
            customer: $secondCustomer,
            staff: $staff,
            subject: 'Second customer private message',
            customerVisible: true,
        );

        $this->actingAs($firstCustomer)
            ->get(route('customer.communications.index'))
            ->assertOk()
            ->assertSeeText($ownCommunication->subject)
            ->assertDontSeeText($otherCommunication->subject);
    }

    public function test_internal_communication_is_not_shown_to_customer(): void
    {
        $customer = $this->customer();
        $staff = $this->staff();

        CustomerCommunication::query()->create([
            'reference' => 'AJD-COM-INTERNAL-001',
            'customer_id' => $customer->id,
            'recorded_by' => $staff->id,
            'channel' => CommunicationChannel::Other->value,
            'direction' => CommunicationDirection::Internal->value,
            'status' => CommunicationStatus::Recorded->value,
            'subject' => 'Internal management discussion',
            'summary' => 'This should never appear in the customer portal.',
            'customer_visible' => true,
            'occurred_at' => now(),
        ]);

        $this->actingAs($customer)
            ->get(route('customer.communications.index'))
            ->assertOk()
            ->assertDontSeeText('Internal management discussion');
    }

    private function communication(
        User $customer,
        User $staff,
        string $subject,
        bool $customerVisible,
    ): CustomerCommunication {
        return CustomerCommunication::query()->create([
            'reference' =>
            'AJD-COM-' . str()->upper(str()->random(10)),
            'customer_id' => $customer->id,
            'recorded_by' => $staff->id,
            'channel' => CommunicationChannel::Email->value,
            'direction' => CommunicationDirection::Outgoing->value,
            'status' => CommunicationStatus::Recorded->value,
            'subject' => $subject,
            'summary' => 'This is a test customer communication.',
            'customer_visible' => $customerVisible,
            'occurred_at' => now(),
        ]);
    }

    private function customer(): User
    {
        $customer = User::factory()->create([
            'account_type' => AccountType::Customer->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $customer->assignRole(
            RoleName::Customer->value,
        );

        return $customer;
    }

    private function staff(): User
    {
        return User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);
    }
}
