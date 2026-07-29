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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CustomerCommunicationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_record_customer_communication(): void
    {
        $customer = $this->customer();
        $staff = $this->staff();

        $communication = app(
            CustomerCommunicationService::class,
        )->record(
            [
                'customer_id' => $customer->id,
                'channel' =>
                    CommunicationChannel::PhoneCall->value,
                'direction' =>
                    CommunicationDirection::Incoming->value,
                'subject' => 'Property availability enquiry',
                'summary' =>
                    'Customer called to ask about available properties.',
                'customer_visible' => false,
                'occurred_at' => now(),
            ],
            $staff,
        );

        $this->assertStringStartsWith(
            'AJD-COM-',
            $communication->reference,
        );

        $this->assertDatabaseHas(
            'customer_communications',
            [
                'id' => $communication->id,
                'customer_id' => $customer->id,
                'recorded_by' => $staff->id,
                'channel' =>
                    CommunicationChannel::PhoneCall->value,
                'direction' =>
                    CommunicationDirection::Incoming->value,
                'status' =>
                    CommunicationStatus::Recorded->value,
                'subject' => 'Property availability enquiry',
                'customer_visible' => false,
            ],
        );
    }

    public function test_communication_with_follow_up_is_marked_pending(): void
    {
        $customer = $this->customer();
        $staff = $this->staff();

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
                'subject' => 'Requested property information',
                'summary' =>
                    'Property details were sent to the customer.',
                'customer_visible' => true,
                'occurred_at' => now(),
                'follow_up_at' => now()->addDays(2),
            ],
            $staff,
        );

        $this->assertSame(
            CommunicationStatus::FollowUpPending,
            $communication->status,
        );

        $this->assertNotNull(
            $communication->follow_up_at,
        );
    }

    public function test_pending_follow_up_can_be_completed(): void
    {
        $customer = $this->customer();
        $staff = $this->staff();

        $communication = app(
            CustomerCommunicationService::class,
        )->record(
            [
                'customer_id' => $customer->id,
                'assigned_to' => $staff->id,
                'channel' =>
                    CommunicationChannel::WhatsApp->value,
                'direction' =>
                    CommunicationDirection::Outgoing->value,
                'subject' => 'Payment follow-up',
                'summary' =>
                    'Customer was contacted about the outstanding payment.',
                'occurred_at' => now(),
                'follow_up_at' => now()->addDay(),
            ],
            $staff,
        );

        $completed = app(
            CustomerCommunicationService::class,
        )->complete(
            $communication,
            $staff,
        );

        $this->assertSame(
            CommunicationStatus::Completed,
            $completed->status,
        );

        $this->assertSame(
            $staff->id,
            $completed->completed_by,
        );

        $this->assertNotNull(
            $completed->completed_at,
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
}