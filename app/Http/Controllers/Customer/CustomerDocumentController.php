<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Enums\DocumentStatus;
use App\Enums\DocumentVisibility;
use App\Http\Controllers\Controller;
use App\Models\PropertyDocument;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class CustomerDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $documents = PropertyDocument::query()
            ->where(
                'customer_id',
                $request->user()->id,
            )
            ->where(
                'visibility',
                DocumentVisibility::CustomerVisible->value,
            )
            ->where(
                'status',
                DocumentStatus::Active->value,
            )
            ->with([
                'purchase',
                'house',
            ])
            ->latest('uploaded_at')
            ->paginate(15);

        return view(
            'customer.documents.index',
            compact('documents'),
        );
    }

    public function download(
        Request $request,
        PropertyDocument $document,
    ): StreamedResponse {
        abort_unless(
            $document->customer_id === $request->user()->id
            && $document->visibility
            === DocumentVisibility::CustomerVisible
            && $document->status === DocumentStatus::Active,
            403,
        );

        abort_unless(
            Storage::disk($document->disk)
                ->exists($document->path),
            404,
        );

        return Storage::disk($document->disk)->download(
            $document->path,
            $document->original_name,
            [
                'Content-Type' => $document->mime_type,
            ],
        );
    }
}