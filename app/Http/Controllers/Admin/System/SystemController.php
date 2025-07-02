<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan; // <--- Импорт Artisan
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;     // <--- Импорт Redis
use Illuminate\Contracts\Redis\Connection; // <--- Для проверки соединения
use Throwable;                             // <--- Для обработки ошибок

class SystemController extends Controller
{
    /**
     * Очищает различные кэши приложения.
     */
    public function clearCache(): RedirectResponse
    {
        $messages = [];

        try {
            // 1. Очистка тегированного кэша (если используется tagging)
            try {
                $tagGroups = ['rubrics', 'articles', 'banners', 'tournaments', 'videos'];
                foreach ($tagGroups as $tag) {
                    Cache::tags($tag)->flush();
                    $messages[] = "Кэш с тегом [{$tag}] успешно очищен.";
                }
                Log::info('Очистка тегированного кэша выполнена пользователем: ' . auth()->id());
            } catch (Throwable $e) {
                Log::error('Ошибка при очистке тегированного кэша: ' . $e->getMessage());
                $messages[] = 'Ошибка при очистке тегированного кэша.';
            }

            // 2. Полная очистка общего кэша Laravel
            try {
                Cache::flush();
                $messages[] = 'Глобальный кэш Laravel очищен.';
                Log::info('Laravel Cache::flush() выполнен пользователем: ' . auth()->id());
            } catch (Throwable $e) {
                Log::error('Ошибка Cache::flush(): ' . $e->getMessage());
                $messages[] = 'Ошибка при полной очистке Laravel Cache.';
            }

            // 3. Очистка системных кэшей (конфигурация, маршруты, шаблоны)
            $artisanCommands = [
                'config:clear' => 'Кэш конфигурации очищен.',
                'route:clear'  => 'Кэш маршрутов очищен.',
                'view:clear'   => 'Кэш представлений очищен.',
            ];

            foreach ($artisanCommands as $command => $message) {
                try {
                    Artisan::call($command);
                    $messages[] = $message;
                } catch (Throwable $e) {
                    Log::error("Ошибка Artisan [{$command}]: " . $e->getMessage());
                    $messages[] = "Ошибка при {$message}";
                }
            }

            return back()->with('success', implode(' ', $messages));
        } catch (Throwable $e) {
            Log::critical('Критическая ошибка при очистке кэша: ' . $e->getMessage(), ['trace' => $e->getTrace()]);
            return back()->with('error', 'Произошла критическая ошибка при очистке кэша.');
        }
    }

}
