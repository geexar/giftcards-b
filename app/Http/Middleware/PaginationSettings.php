<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaginationSettings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $defaultPerPage = 10;
        $maxPerPage = 100;

        // Get "per_page" from query (?per_page=) or use default
        $perPage = (int) $request->query('per_page', $defaultPerPage);

        // Validate "per_page"
        if ($perPage < 1) {
            $perPage = 1;
        } elseif ($perPage > $maxPerPage) {
            $perPage = $maxPerPage;
        }

        // Merge normalized pagination parameters into the request
        $request->merge([
            'page' => (int) $request->query('page', 1),
            'per_page' => $perPage,
        ]);

        return $next($request);
    }
}
