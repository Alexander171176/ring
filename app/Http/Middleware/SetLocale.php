<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $lang = $request->route('lang');
        if (in_array($lang, config('app.supported_locales'))) {
            app()->setLocale($lang);
        }

        return $next($request);
    }
}
