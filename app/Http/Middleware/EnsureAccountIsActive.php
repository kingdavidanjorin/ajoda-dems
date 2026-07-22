<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAccountIsActive
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(
        Request $request,
        Closure $next,
    ): Response|RedirectResponse {
        $user = $request->user();

        if ($user === null || ! $user->isActive()) {
            return redirect()->route('access.status');
        }

        return $next($request);
    }
}
