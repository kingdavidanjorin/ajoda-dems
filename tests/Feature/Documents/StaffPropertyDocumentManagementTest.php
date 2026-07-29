<?php

declare(strict_types=1);

namespace Tests\Feature\Documents;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Enums\DocumentVisibility;
use App\Enums\RoleName;
use App\Models\PropertyDocument;
use App\Models\User;
use App\Services\Documents\PropertyDocumentService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class StaffPropertyDocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_system_administrator_can_open_document_management_page(): void
    {
        $administrator = $this->systemAdministrator();

        $this->actingAs($administrator)
            ->get(route('staff.documents.index'))
            ->assertOk()
            ->assertSeeText('Upload document')
            ->assertSeeText('Uploaded documents');
    }

    public function test_system_administrator_can_upload_customer_document(): void
    {
        $administrator = $this->systemAdministrator();
        $customer = $this->customer();

        $file = UploadedFile::fake()->create(
            'allocation-letter.pdf',
            100,
            'application/pdf',
        );

        $this->actingAs($administrator)
            ->post(
                route('staff.documents.store'),
                [
                    'customer_id' => $customer->id,
                    'purchase_id' => null,
                    'document_type' =>
                        DocumentType::AllocationLetter->value,
                    'visibility' =>
                        DocumentVisibility::CustomerVisible->value,
                    'title' => 'Official Allocation Letter',
                    'description' =>
                        'Official property allocation document.',
                    'file' => $file,
                ],
            )
            ->assertRedirect(route('staff.documents.index'))
            ->assertSessionHas('document_status');

        $document = PropertyDocument::query()
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        $this->assertDatabaseHas(
            'property_documents',
            [
                'id' => $document->id,
                'customer_id' => $customer->id,
                'uploaded_by' => $administrator->id,
                'document_type' =>
                    DocumentType::AllocationLetter->value,
                'visibility' =>
                    DocumentVisibility::CustomerVisible->value,
                'status' => DocumentStatus::Active->value,
                'title' => 'Official Allocation Letter',
                'original_name' => 'allocation-letter.pdf',
                'disk' => 'local',
            ],
        );

        Storage::disk('local')->assertExists(
            $document->path,
        );
    }

    public function test_system_administrator_can_download_document(): void
    {
        $administrator = $this->systemAdministrator();
        $customer = $this->customer();

        $file = UploadedFile::fake()->create(
            'sale-agreement.pdf',
            100,
            'application/pdf',
        );

        $document = app(PropertyDocumentService::class)
            ->upload(
                [
                    'customer_id' => $customer->id,
                    'purchase_id' => null,
                    'document_type' =>
                        DocumentType::SaleAgreement->value,
                    'visibility' =>
                        DocumentVisibility::StaffOnly->value,
                    'title' => 'Sale Agreement',
                    'description' => null,
                ],
                $file,
                $administrator,
            );

        $this->actingAs($administrator)
            ->get(
                route(
                    'staff.documents.download',
                    $document,
                ),
            )
            ->assertOk()
            ->assertDownload('sale-agreement.pdf');
    }

    private function systemAdministrator(): User
    {
        $administrator = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $administrator->assignRole(
            RoleName::SystemAdministrator->value,
        );

        return $administrator;
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
}