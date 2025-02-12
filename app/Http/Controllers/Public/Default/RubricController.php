<?php

namespace App\Http\Controllers\Public\Default;

use App\Http\Controllers\Controller;
use App\Models\Admin\Rubric\Rubric;
use App\Models\Admin\Setting\Setting;
use Illuminate\Http\JsonResponse;
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
        // Получаем текущую локаль
        $locale = Setting::where('option', 'locale')->value('value');

        // Логирование для проверки текущей локали
        //Log::info("Текущая локаль для фильтрации: " . $locale);

        // Получаем рубрики с фильтрацией по активности и локали
        $rubrics = Rubric::where('activity', 1)
            ->where('locale', $locale)
            ->orderBy('sort')
            ->get(['id', 'title', 'url', 'locale']);

        // Логирование результата перед отправкой
        //Log::info("Найденные рубрики: ", $rubrics->toArray());

        return response()->json([
            'rubrics' => $rubrics,
            'rubricsCount' => $rubrics->count()
        ]);
    }


}
