<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $locale = $request->header('Accept-Language') ?? 'en';

        Config::set('app.locale', $locale);

        return $next($request);
    }
}
