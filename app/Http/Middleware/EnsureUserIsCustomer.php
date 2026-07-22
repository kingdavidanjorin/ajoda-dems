<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\RoleName;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserIsCustomer
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        $user = $request->user();

        abort_unless(
            $user !== null
            && $user->isCustomer()
            && $user->hasRole(RoleName::Customer->value),
            403,
        );

        return $next($request);
    }
}
