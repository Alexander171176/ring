<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Rubric\RubricResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Rubric\Rubric;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    use CacheTimeTrait;

    public function index(Request $request): \Illuminate\Http\JsonResponse|\Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        if ($request->has('type')) {
            $type = $request->query('type');
            $cacheKey = $this->generateCacheKey($type, 'data');

            $data = Cache::store('redis')->remember($cacheKey, $cacheTime, function () use ($type) {
                return $type === 'rubrics' ? Rubric::withCount('articles')->get() : Article::with('rubrics')->get();
            });

            $resource = $type === 'rubrics' ? RubricResource::collection($data) : ArticleResource::collection($data);
            return response()->json(['data' => $resource]);
        }

        $rubrics = Cache::store('redis')->remember('report.rubrics', $cacheTime, function () {
            return Rubric::withCount('articles')->get();
        });

        $articles = Cache::store('redis')->remember('report.articles', $cacheTime, function () {
            return Article::with('rubrics')->get();
        });

        return Inertia::render('Admin/Reports/Index', [
            'rubrics' => RubricResource::collection($rubrics),
            'articles' => ArticleResource::collection($articles),
        ]);
    }

    public function download(Request $request): StreamedResponse
    {
        $cacheTime = $this->getCacheTime();

        $type = $request->query('type');
        $format = $request->query('format');
        $cacheKey = $this->generateCacheKey($type, 'download');

        $data = Cache::store('redis')->remember($cacheKey, $cacheTime, function () use ($type) {
            return $type === 'rubrics' ? Rubric::all() : Article::with('rubrics')->get();
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
                // Logic for Excel
                return ''; // Replace with actual logic for generating Excel file
            case 'pdf':
                // Logic for PDF
                return ''; // Replace with actual logic for generating PDF file
            case 'zip':
                // Logic for ZIP
                return ''; // Replace with actual logic for generating ZIP file
            default:
                throw new \InvalidArgumentException('Неподдерживаемый формат');
        }
    }

}
