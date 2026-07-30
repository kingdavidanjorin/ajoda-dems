<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;

final class PasswordResetLinkResponse implements
    SuccessfulPasswordResetLinkRequestResponse,
    FailedPasswordResetLinkRequestResponse
{
    public function __construct(
        private readonly string $status = '',
    ) {}

    public function toResponse(
        $request,
    ): JsonResponse|RedirectResponse {
        $message = __(
            'If an account exists for that email address, a password reset link will be sent.',
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message,
            ]);
        }

        return back()
            ->withInput(
                $request->only('email'),
            )
            ->with('status', $message);
    }
}
