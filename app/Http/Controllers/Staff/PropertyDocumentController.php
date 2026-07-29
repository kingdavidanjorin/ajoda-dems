<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff;

use App\Enums\AccountType;
use App\Enums\PermissionName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StorePropertyDocumentRequest;
use App\Models\PropertyDocument;
use App\Models\Purchase;
use App\Models\User;
use App\Services\Documents\PropertyDocumentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PropertyDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $this->ensureDocumentViewAccess($user);

        $canManageDocuments = $user->can(
            PermissionName::DocumentsManage->value,
        );

        $documents = PropertyDocument::query()
            ->with([
                'customer',
                'purchase',
                'house',
                'uploadedBy',
            ])
            ->latest('uploaded_at')
            ->paginate(15);

        $customers = collect();
        $purchases = collect();

        if ($canManageDocuments) {
            $customers = User::query()
                ->where(
                    'account_type',
                    AccountType::Customer->value,
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'email',
                ]);

            $purchases = Purchase::query()
                ->with('customer')
                ->latest('initiated_at')
                ->get([
                    'id',
                    'reference',
                    'customer_id',
                ]);
        }

        return view(
            'staff.documents.index',
            compact(
                'documents',
                'customers',
                'purchases',
                'canManageDocuments',
            ),
        );
    }

    public function store(
        StorePropertyDocumentRequest $request,
        PropertyDocumentService $service,
    ): RedirectResponse {
        $service->upload(
            $request->safe()->except('file'),
            $request->file('file'),
            $request->user(),
        );

        return redirect()
            ->route('staff.documents.index')
            ->with(
                'document_status',
                'The document was uploaded securely.',
            );
    }

    public function download(
        Request $request,
        PropertyDocument $document,
    ): StreamedResponse {
        $this->ensureDocumentViewAccess(
            $request->user(),
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

    private function ensureDocumentViewAccess(
        User $user,
    ): void {
        abort_unless(
            $user->can(
                PermissionName::DocumentsView->value,
            )
                || $user->can(
                    PermissionName::DocumentsManage->value,
                ),
            403,
        );
    }
}
