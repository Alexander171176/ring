<?php

namespace App\Http\Middleware;

use App\Models\Admin\Setting\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromSettings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Пытаемся получить локаль из кэша или базы данных
        $locale = Cache::rememberForever('app_locale', function () {
            $setting = Setting::where('option', 'locale')->first();
            return $setting ? $setting->value : config('app.locale');
        });

        // Устанавливаем локаль
        App::setLocale($locale);

        return $next($request);
    }
}
