<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Rubric\RubricResource;
use App\Http\Resources\Admin\Section\SectionResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Rubric\Rubric;
use App\Models\Admin\Section\Section;
use App\Models\Admin\Setting\Setting;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    use CacheTimeTrait;

    /**
     * Отображает отчёт в зависимости от параметра type (JSON или Inertia).
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse|InertiaResponse
     */
    public function index(Request $request): JsonResponse|InertiaResponse
    {
        $cacheTime = $this->getCacheTime();

        if ($request->has('type')) {
            $type = $request->query('type');
            $cacheKey = $this->generateCacheKey($type, 'data');

            $data = Cache::store('redis')->remember($cacheKey, $cacheTime, function () use ($type) {
                // Получаем текущую локаль из настроек
                $locale = Setting::where('option', 'locale')->value('value');

                if ($type === 'rubrics') {
                    return Rubric::with([
                        'sections' => function ($query) use ($locale) {
                            $query->where('activity', 1)
                                ->where('locale', $locale)
                                ->orderBy('sort', 'asc')
                                ->with(['articles' => function ($query) use ($locale) {
                                    $query->where('activity', 1)
                                        ->where('locale', $locale)
                                        ->orderBy('sort', 'asc')
                                        ->with(['images', 'tags']);
                                }]);
                        }
                    ])->get();
                } elseif ($type === 'sections') {
                    return Section::withCount('articles')
                        ->with([
                            'articles' => function ($query) use ($locale) {
                                $query->where('activity', 1)
                                    ->where('locale', $locale)
                                    ->orderBy('sort', 'asc')
                                    ->with(['images', 'tags']);
                            }
                        ])
                        ->where('activity', 1)
                        ->where('locale', $locale)
                        ->orderBy('sort', 'asc')
                        ->get();
                } else {
                    // articles
                    return Article::with('sections')
                        ->where('activity', 1)
                        ->where('locale', $locale)
                        ->orderBy('sort', 'asc')
                        ->with(['images', 'tags'])
                        ->get();
                }
            });

            if ($type === 'rubrics') {
                $resource = RubricResource::collection($data);
            } elseif ($type === 'sections') {
                $resource = SectionResource::collection($data);
            } else {
                $resource = ArticleResource::collection($data);
            }
            return response()->json(['data' => $resource]);
        }

        // Если параметр type не передан, готовим Inertia-отображение.
        // Получаем рубрики с секциями и статьями (фильтрация по активности и текущей локали)
        $locale = Setting::where('option', 'locale')->value('value');

        $rubrics = Cache::store('redis')->remember('report.rubrics', $cacheTime, function () use ($locale) {
            return Rubric::with([
                'sections' => function ($query) use ($locale) {
                    $query->where('activity', 1)
                        ->where('locale', $locale)
                        ->orderBy('sort', 'asc')
                        ->with(['articles' => function ($query) use ($locale) {
                            $query->where('activity', 1)
                                ->where('locale', $locale)
                                ->orderBy('sort', 'asc')
                                ->with(['images', 'tags']);
                        }]);
                }
            ])->get();
        });

        // Из рубрик собираем секции
        $sections = $rubrics->pluck('sections')->flatten();

        // Получаем статьи отдельно (если требуется)
        $articles = Cache::store('redis')->remember('report.articles', $cacheTime, function () use ($locale) {
            return Article::with('sections')
                ->where('activity', 1)
                ->where('locale', $locale)
                ->orderBy('sort', 'asc')
                ->with(['images', 'tags'])
                ->get();
        });

        // Считаем общее количество статей во всех секциях
        $activeArticlesCount = $sections->reduce(function ($carry, $section) {
            return $carry + ($section->articles ? $section->articles->count() : 0);
        }, 0);

        return Inertia::render('Admin/Reports/Index', [
            'rubrics'              => RubricResource::collection($rubrics),
            'sections'             => SectionResource::collection($sections),
            'sectionsCount'        => $sections->count(),
            'activeArticlesCount'  => $activeArticlesCount,
            'articles'             => ArticleResource::collection($articles),
        ]);
    }

    /**
     * Экспорт отчёта в выбранном формате.
     *
     * @param \Illuminate\Http\Request $request
     * @return StreamedResponse
     */
    public function download($request): StreamedResponse
    {
        $cacheTime = $this->getCacheTime();

        $type = $request->query('type');
        $format = $request->query('format');
        $cacheKey = $this->generateCacheKey($type, 'download');

        $data = Cache::store('redis')->remember($cacheKey, $cacheTime, function () use ($type) {
            $locale = Setting::where('option', 'locale')->value('value');

            if ($type === 'rubrics') {
                return Rubric::with([
                    'sections' => function ($query) use ($locale) {
                        $query->where('activity', 1)
                            ->where('locale', $locale)
                            ->orderBy('sort', 'asc')
                            ->with(['articles' => function ($query) use ($locale) {
                                $query->where('activity', 1)
                                    ->where('locale', $locale)
                                    ->orderBy('sort', 'asc');
                            }]);
                    }
                ])->get();
            } elseif ($type === 'sections') {
                return Section::withCount('articles')
                    ->with([
                        'articles' => function ($query) use ($locale) {
                            $query->where('activity', 1)
                                ->where('locale', $locale)
                                ->orderBy('sort', 'asc');
                        }
                    ])
                    ->where('activity', 1)
                    ->where('locale', $locale)
                    ->orderBy('sort', 'asc')
                    ->get();
            } else {
                return Article::with('sections')
                    ->where('activity', 1)
                    ->where('locale', $locale)
                    ->orderBy('sort', 'asc')
                    ->get();
            }
        });

        $filename = "report.{$format}";
        $content = $this->generateReport($data, $format);

        return Response::streamDownload(function () use ($content) {
            echo $content;
        }, $filename, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    private function generateCacheKey(string $type, string $context): string
    {
        return "report.{$context}.{$type}_" . now()->format('Y-m-d');
    }

    private function generateReport($data, $format): bool|string
    {
        switch ($format) {
            case 'csv':
                ob_start();
                $csv = fopen('php://output', 'w');
                // Получаем ключи первого объекта
                $headers = array_keys($data->first()->toArray() ?? []);
                fputcsv($csv, $headers);
                foreach ($data as $item) {
                    $row = $item->toArray();
                    foreach ($row as $key => $value) {
                        if (is_array($value)) {
                            $row[$key] = json_encode($value);
                        }
                    }
                    fputcsv($csv, $row);
                }
                fclose($csv);
                return ob_get_clean();
            case 'xls':
                // Логика для Excel
                return ''; // Здесь можно реализовать генерацию Excel
            case 'pdf':
                // Логика для PDF
                return ''; // Здесь можно реализовать генерацию PDF
            case 'zip':
                // Логика для ZIP
                return ''; // Здесь можно реализовать генерацию ZIP
            default:
                throw new \InvalidArgumentException('Неподдерживаемый формат');
        }
    }
}
