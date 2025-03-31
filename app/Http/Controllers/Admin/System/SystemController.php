<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;


class SystemController extends Controller
{
    public function clearCache(): RedirectResponse
    {
        // Очищаем кэш Laravel
        Cache::flush();

        // Очищаем весь кэш Redis
        Redis::connection()->flushAll();

        return back()->with('success', 'Весь кэш успешно очищен!');
    }
}
