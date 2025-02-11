<?php

namespace App\Http\Controllers\Public\Default;

use App\Http\Controllers\Controller;
use App\Models\Admin\Rubric\Rubric;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class RubricController extends Controller
{
    /**
     * Возвращает список активных рубрик в зависимости от выбранного языка.
     *
     * @return JsonResponse
     */
    public function getRubrics(): JsonResponse
    {
        Log::info('Метод getRubrics вызван');  // Проверка вызова метода

        $locale = App::getLocale();
        Log::info("Текущая локаль: " . $locale);  // Проверка текущей локали

        $rubrics = Rubric::where('activity', 1)
            ->where('locale', $locale)
            ->orderBy('sort')
            ->get(['id', 'title', 'url', 'locale']);

        Log::info("Найденные рубрики:", $rubrics->toArray());  // Проверка данных рубрик

        return response()->json($rubrics);
    }

}
