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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Отображает отчёт в зависимости от параметра type (JSON или Inertia).
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse|InertiaResponse
     */
    public function index(Request $request): JsonResponse|InertiaResponse
    {
        if ($request->has('type')) {
            $type = $request->query('type');

            // Получаем текущую локаль из настроек
            $locale = Setting::where('option', 'locale')->value('value');

            if ($type === 'rubrics') {
                $data = Rubric::with([
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
                $data = Section::withCount('articles')
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
                $data = Article::with('sections')
                    ->where('activity', 1)
                    ->where('locale', $locale)
                    ->orderBy('sort', 'asc')
                    ->with(['images', 'tags'])
                    ->get();
            }

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
        $locale = Setting::where('option', 'locale')->value('value');

        $rubrics = Rubric::with([
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

        // Из рубрик собираем секции
        $sections = $rubrics->pluck('sections')->flatten();

        // Получаем статьи отдельно (если требуется)
        $articles = Article::with('sections')
            ->where('activity', 1)
            ->where('locale', $locale)
            ->orderBy('sort', 'asc')
            ->with(['images', 'tags'])
            ->get();

        // Считаем общее количество статей во всех секциях
        $activeArticlesCount = $sections->reduce(function ($carry, $section) {
            return $carry + ($section->articles ? $section->articles->count() : 0);
        }, 0);

        return Inertia::render('Admin/Reports/Index', [
            'rubrics'             => RubricResource::collection($rubrics),
            'sections'            => SectionResource::collection($sections),
            'sectionsCount'       => $sections->count(),
            'activeArticlesCount' => $activeArticlesCount,
            'articles'            => ArticleResource::collection($articles),
        ]);
    }

    /**
     * Экспорт отчёта в выбранном формате.
     *
     * @param \Illuminate\Http\Request $request
     * @return StreamedResponse
     */
    public function download(Request $request): StreamedResponse
    {
        $type = $request->query('type');
        $format = $request->query('format');

        $locale = Setting::where('option', 'locale')->value('value');

        if ($type === 'rubrics') {
            $data = Rubric::with([
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
            $data = Section::withCount('articles')
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
            $data = Article::with('sections')
                ->where('activity', 1)
                ->where('locale', $locale)
                ->orderBy('sort', 'asc')
                ->get();
        }

        $filename = "report.{$format}";
        $content = $this->generateReport($data, $format);

        return Response::streamDownload(function () use ($content) {
            echo $content;
        }, $filename, [
            'Content-Type' => 'application/octet-stream',
        ]);
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
