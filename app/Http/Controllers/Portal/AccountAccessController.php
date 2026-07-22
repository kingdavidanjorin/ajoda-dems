<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Enums\AccountStatus;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class AccountAccessController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $message = match ($user->account_status) {
            AccountStatus::Pending => 'Your account is awaiting administrator approval.',

            AccountStatus::Suspended => 'Your account has been suspended. Contact the system administrator.',

            AccountStatus::Deactivated => 'Your account has been deactivated.',

            AccountStatus::Active => 'Your account is active, but no portal role has been assigned.',
        };

        return view('portal.access-status', [
            'user' => $user,
            'message' => $message,
        ]);
    }
}
