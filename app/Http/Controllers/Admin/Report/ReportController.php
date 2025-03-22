<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Rubric\RubricResource;
use App\Http\Resources\Admin\Section\SectionResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Rubric\Rubric;
use App\Models\Admin\Section\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    /**
     * Страница отчётов в админке
     *
     * @param Request $request
     * @return JsonResponse|\Inertia\Response
     */
    public function index(Request $request): JsonResponse|\Inertia\Response
    {
        if ($request->has('type')) {
            $type = $request->query('type');
            if ($type === 'rubrics') {
                $data = Rubric::with([
                    'sections' => function ($query) {
                        $query->where('activity', 1)
                            ->orderBy('sort', 'asc')
                            ->with(['articles' => function ($query) {
                                $query->where('activity', 1)
                                    ->orderBy('sort', 'asc');
                            }]);
                    }
                ])->get();
            } elseif ($type === 'sections') {
                $data = Section::withCount('articles')
                    ->with([
                        'articles' => function ($query) {
                            $query->where('activity', 1)
                                ->orderBy('sort', 'asc');
                        }
                    ])->get();
            } else {
                $data = Article::with('sections')->get();
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

        // Для представления Inertia
        $rubrics = Rubric::with([
            'sections' => function ($query) {
                $query->where('activity', 1)
                    ->orderBy('sort', 'asc')
                    ->with(['articles' => function ($query) {
                        $query->where('activity', 1)
                            ->orderBy('sort', 'asc');
                    }]);
            }
        ])->get();

        $articles = Article::with('sections')->get();

        // Собираем секции из рубрик
        $sections = $rubrics->pluck('sections')->flatten();
        // Если используется withCount('articles'), общее количество статей
        $articlesCount = $sections->sum('articles_count');

        return Inertia::render('Admin/Reports/Index', [
            'rubrics'       => RubricResource::collection($rubrics),
            'sections'      => SectionResource::collection($sections),
            'sectionsCount' => $sections->count(),
            'articles'      => ArticleResource::collection($articles),
            'articlesCount' => $articlesCount,
        ]);
    }

    /**
     * Функция загрузки отчётов
     *
     * @param Request $request
     * @return StreamedResponse
     */
    public function download(Request $request): StreamedResponse
    {
        $type = $request->query('type');
        $format = $request->query('format');

        if ($type === 'rubrics') {
            $data = Rubric::with([
                'sections' => function ($query) {
                    $query->where('activity', 1)
                        ->orderBy('sort', 'asc')
                        ->with(['articles' => function ($query) {
                            $query->where('activity', 1)
                                ->orderBy('sort', 'asc');
                        }]);
                }
            ])->get();
        } elseif ($type === 'sections') {
            $data = Section::with([
                'articles' => function ($query) {
                    $query->where('activity', 1)
                        ->orderBy('sort', 'asc');
                }
            ])->get();
        } else {
            $data = Article::with('sections')->get();
        }

        $filename = "report.{$format}";
        $content = $this->generateReport($data, $format);

        return Response::streamDownload(function () use ($content) {
            echo $content;
        }, $filename, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    /**
     * Функция генерации отчётов
     *
     * @param $data
     * @param $format
     * @return bool|string
     */
    private function generateReport($data, $format): bool|string
    {
        switch ($format) {
            case 'csv':
                ob_start();
                $csv = fopen('php://output', 'w');
                fputcsv($csv, array_keys($data->first()->toArray() ?? []));
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
                // Здесь можно добавить логику для генерации Excel
                return '';
            case 'pdf':
                // Здесь можно добавить логику для генерации PDF
                return '';
            case 'zip':
                // Здесь можно добавить логику для генерации ZIP
                return '';
            default:
                throw new \InvalidArgumentException('Неподдерживаемый формат');
        }
    }
}
