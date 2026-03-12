<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ActiveToken
{
    public function handle(Request $request, Closure $next, string $guard): Response
    {
        $token = $request->user($guard)->currentAccessToken();

        if ($token->expires_at && $token->expires_at->isPast()) {
            $token->delete();
            throw new UnauthorizedHttpException('', 'Session ended. Please login again.');
        }

        return $next($request);
    }
}
