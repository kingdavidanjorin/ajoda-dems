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
use Illuminate\Support\Str;
use Tests\TestCase;

final class PropertyDocumentSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_document_service_stores_file_on_private_disk(): void
    {
        $customer = $this->customer();
        $uploader = User::factory()->create();

        $file = UploadedFile::fake()->create(
            'allocation-letter.pdf',
            100,
            'application/pdf',
        );

        $document = app(PropertyDocumentService::class)->upload(
            [
                'customer_id' => $customer->id,
                'purchase_id' => null,
                'document_type' =>
                    DocumentType::AllocationLetter->value,
                'visibility' =>
                    DocumentVisibility::CustomerVisible->value,
                'title' => 'Allocation Letter',
                'description' => 'Official allocation document.',
            ],
            $file,
            $uploader,
        );

        Storage::disk('local')->assertExists(
            $document->path,
        );

        $this->assertDatabaseHas(
            'property_documents',
            [
                'id' => $document->id,
                'customer_id' => $customer->id,
                'uploaded_by' => $uploader->id,
                'title' => 'Allocation Letter',
                'visibility' =>
                    DocumentVisibility::CustomerVisible->value,
                'status' => DocumentStatus::Active->value,
                'disk' => 'local',
            ],
        );
    }

    public function test_customer_can_download_their_visible_document(): void
    {
        $customer = $this->customer();

        $document = $this->documentFor(
            $customer,
            DocumentVisibility::CustomerVisible,
        );

        $this->actingAs($customer)
            ->get(
                route(
                    'customer.documents.download',
                    $document,
                ),
            )
            ->assertOk()
            ->assertDownload($document->original_name);
    }

    public function test_customer_cannot_download_another_customers_document(): void
    {
        $firstCustomer = $this->customer();
        $secondCustomer = $this->customer();

        $document = $this->documentFor(
            $secondCustomer,
            DocumentVisibility::CustomerVisible,
        );

        $this->actingAs($firstCustomer)
            ->get(
                route(
                    'customer.documents.download',
                    $document,
                ),
            )
            ->assertForbidden();
    }

    public function test_staff_only_document_is_hidden_from_customer(): void
    {
        $customer = $this->customer();

        $document = $this->documentFor(
            $customer,
            DocumentVisibility::StaffOnly,
        );

        $this->actingAs($customer)
            ->get(route('customer.documents.index'))
            ->assertOk()
            ->assertDontSeeText($document->reference);

        $this->actingAs($customer)
            ->get(
                route(
                    'customer.documents.download',
                    $document,
                ),
            )
            ->assertForbidden();
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

    private function documentFor(
        User $customer,
        DocumentVisibility $visibility,
    ): PropertyDocument {
        $uploader = User::factory()->create();

        $path = sprintf(
            'property-documents/%d/%s.pdf',
            $customer->id,
            Str::uuid(),
        );

        Storage::disk('local')->put(
            $path,
            'Test document content',
        );

        return PropertyDocument::query()->create([
            'reference' =>
                'AJD-DOC-'.Str::upper(Str::random(10)),
            'customer_id' => $customer->id,
            'uploaded_by' => $uploader->id,
            'document_type' =>
                DocumentType::AllocationLetter->value,
            'visibility' => $visibility->value,
            'status' => DocumentStatus::Active->value,
            'title' => 'Customer Allocation Letter',
            'original_name' => 'allocation-letter.pdf',
            'disk' => 'local',
            'path' => $path,
            'mime_type' => 'application/pdf',
            'file_extension' => 'pdf',
            'size_bytes' => 21,
            'checksum_sha256' => hash(
                'sha256',
                'Test document content',
            ),
            'description' => 'Test customer document.',
            'uploaded_at' => now(),
        ]);
    }
}