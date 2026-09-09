<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\DocumentType;
use App\Enums\DocumentVisibility;
use App\Enums\RoleName;
use App\Models\PropertyDocument;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class FileUploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_php_file_is_rejected(): void
    {
        $administrator = $this->systemAdministrator();
        $customer = $this->customer();

        $file = UploadedFile::fake()->create(
            'shell.php',
            5,
            'application/x-php',
        );

        $this->actingAs($administrator)
            ->post(
                route('staff.documents.store'),
                $this->payload($customer, $file),
            )
            ->assertSessionHasErrors('file');

        $this->assertSame(
            0,
            PropertyDocument::query()->count(),
        );
    }

    public function test_double_extension_php_file_is_rejected(): void
    {
        $administrator = $this->systemAdministrator();
        $customer = $this->customer();

        $file = UploadedFile::fake()->create(
            'shell.php.jpg',
            5,
            'application/x-php',
        );

        $this->actingAs($administrator)
            ->post(
                route('staff.documents.store'),
                $this->payload($customer, $file),
            )
            ->assertSessionHasErrors('file');

        $this->assertSame(
            0,
            PropertyDocument::query()->count(),
        );
    }

    public function test_pdf_extension_with_non_pdf_content_is_rejected(): void
    {
        $administrator = $this->systemAdministrator();
        $customer = $this->customer();

        $path = tempnam(
            sys_get_temp_dir(),
            'ajoda_upload_',
        );

        file_put_contents(
            $path,
            "<?php echo 'malicious'; ?>",
        );

        $file = new UploadedFile(
            $path,
            'fake-document.pdf',
            null,
            null,
            true,
        );

        try {
            $this->actingAs($administrator)
                ->post(
                    route('staff.documents.store'),
                    $this->payload($customer, $file),
                )
                ->assertSessionHasErrors('file');

            $this->assertSame(
                0,
                PropertyDocument::query()->count(),
            );
        } finally {
            @unlink($path);
        }
    }

    public function test_file_larger_than_ten_megabytes_is_rejected(): void
    {
        $administrator = $this->systemAdministrator();
        $customer = $this->customer();

        $file = UploadedFile::fake()->create(
            'oversized-document.pdf',
            10 * 1024 + 1,
            'application/pdf',
        );

        $this->actingAs($administrator)
            ->post(
                route('staff.documents.store'),
                $this->payload($customer, $file),
            )
            ->assertSessionHasErrors('file');

        $this->assertSame(
            0,
            PropertyDocument::query()->count(),
        );
    }

    public function test_valid_pdf_is_still_accepted(): void
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
                $this->payload($customer, $file),
            )
            ->assertRedirect(
                route('staff.documents.index'),
            );

        $document = PropertyDocument::query()
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        $this->assertSame(
            'allocation-letter.pdf',
            $document->original_name,
        );

        Storage::disk('local')
            ->assertExists($document->path);
    }

    private function payload(
        User $customer,
        UploadedFile $file,
    ): array {
        return [
            'customer_id' => $customer->id,
            'purchase_id' => null,
            'document_type' =>
            DocumentType::AllocationLetter->value,
            'visibility' =>
            DocumentVisibility::CustomerVisible->value,
            'title' => 'Security Upload Test',
            'description' =>
            'Automated file-upload security test.',
            'file' => $file,
        ];
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
