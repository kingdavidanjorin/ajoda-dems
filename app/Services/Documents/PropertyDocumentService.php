<?php

declare(strict_types=1);

namespace App\Services\Documents;

use App\Enums\DocumentStatus;
use App\Models\PropertyDocument;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

final class PropertyDocumentService
{
    /**
     * @param array<string, mixed> $data
     */
    public function upload(
        array $data,
        UploadedFile $file,
        User $uploadedBy,
    ): PropertyDocument {
        $purchase = null;

        if (!empty($data['purchase_id'])) {
            $purchase = Purchase::query()
                ->with('allocation')
                ->findOrFail($data['purchase_id']);

            if ($purchase->customer_id !== (int) $data['customer_id']) {
                throw ValidationException::withMessages([
                    'purchase_id' =>
                        'The selected purchase does not belong to the selected customer.',
                ]);
            }
        }

        $extension = strtolower(
            $file->getClientOriginalExtension()
            ?: $file->extension()
            ?: '',
        );

        $storedName = Str::uuid()->toString();

        if ($extension !== '') {
            $storedName .= '.' . $extension;
        }

        $directory = sprintf(
            'property-documents/%d',
            (int) $data['customer_id'],
        );

        $path = $file->storeAs(
            $directory,
            $storedName,
            'local',
        );

        if ($path === false) {
            throw new RuntimeException(
                'The document could not be stored.',
            );
        }

        $checksum = hash_file(
            'sha256',
            $file->getRealPath(),
        );

        try {
            return DB::transaction(
                fn(): PropertyDocument =>
                PropertyDocument::query()->create([
                    'reference' => $this->generateReference(),
                    'customer_id' => (int) $data['customer_id'],
                    'purchase_id' => $purchase?->id,
                    'property_allocation_id' =>
                        $purchase?->allocation?->id,
                    'house_id' => $purchase?->house_id,
                    'uploaded_by' => $uploadedBy->id,
                    'document_type' => $data['document_type'],
                    'visibility' => $data['visibility'],
                    'status' => DocumentStatus::Active->value,
                    'title' => $data['title'],
                    'original_name' =>
                        $file->getClientOriginalName(),
                    'disk' => 'local',
                    'path' => $path,
                    'mime_type' =>
                        $file->getMimeType()
                        ?: $file->getClientMimeType()
                        ?: 'application/octet-stream',
                    'file_extension' =>
                        $extension !== '' ? $extension : null,
                    'size_bytes' => $file->getSize(),
                    'checksum_sha256' =>
                        $checksum !== false ? $checksum : null,
                    'description' =>
                        $data['description'] ?? null,
                    'uploaded_at' => now(),
                ]),
            );
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }
    }

    private function generateReference(): string
    {
        do {
            $reference = sprintf(
                'AJD-DOC-%s-%s',
                now()->format('Ymd'),
                Str::upper(Str::random(6)),
            );
        } while (
            PropertyDocument::query()
                ->where('reference', $reference)
                ->exists()
        );

        return $reference;
    }
}