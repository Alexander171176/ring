<?php

namespace App\Http\Middleware;

use App\Services\SystemAccountService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class CheckAdminOwnerExists
{
    public function handle(Request $request, Closure $next): Response
    {
        // Log::info('[MIDDLEWARE].');

        // Всегда вызываем восстановление — оно внутри само проверит роли/права
        \App\Services\SystemAccountService::restoreOwner();

        return $next($request);
    }
}
