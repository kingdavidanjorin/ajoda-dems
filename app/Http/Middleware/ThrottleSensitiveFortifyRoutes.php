<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

class ThrottleSensitiveFortifyRoutes
{
    public function handle(Request $request, Closure $next): Response
    {
        $limiters = [
            'register.store' => 'registration',
            'password.email' => 'password-reset-link',
            'password.update' => 'password-reset-submit',
        ];

        $routeName = $request->route()?->getName();

        if ($routeName !== null && isset($limiters[$routeName])) {
            return app(ThrottleRequests::class)->handle(
                $request,
                $next,
                $limiters[$routeName],
            );
        }

        return $next($request);
    }
}
