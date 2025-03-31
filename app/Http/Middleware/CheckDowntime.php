<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckDowntime
{
    /**
     * Обработка входящего запроса.
     */
    public function handle(Request $request, Closure $next)
    {
        // Получаем настройку downtimeSite из глобальной конфигурации
        $downtime = config('site_settings.downtimeSite', 'false');

        // Если включён режим обслуживания и запрос НЕ для админки, дашборда или maintenance
        if (
            $downtime === 'true' &&
            !$request->is('admin*') &&
            !$request->is('dashboard') &&
            !$request->is('maintenance')
        ) {
            return redirect('/maintenance');
        }

        return $next($request);
    }
}
