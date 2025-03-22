<?php

namespace App\Http\Controllers\Api\Article;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Article\ArticleRequest;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Article\ArticleImage;
use App\Models\Admin\Article\Tag;
use App\Models\Admin\Section\Section;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *     title="API Documentation",
 *     version="1.0.0",
 *     @OA\Contact(
 *         email="kosolapov1976@gmail.com"
 *     )
 * )
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Article",
 *     type="object",
 *     title="Article",
 *     description="Article model",
 *     required={"id", "title", "content", "created_at", "updated_at"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Article Title"),
 *     @OA\Property(property="content", type="string", example="Article content..."),
 *     @OA\Property(property="image_url", type="string", example="https://example.com/image.jpg"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00Z")
 * )
 */
class ApiArticleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/articles",
     *     operationId="getArticles",
     *     tags={"Articles"},
     *     summary="Display a listing of the articles",
     *     description="Get a list of articles with their sections, tags and images",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Article")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $articles = Article::with(['sections', 'tags', 'images'])->get();
        $articlesCount = DB::table('articles')->count();

        return response()->json([
            'articles' => ArticleResource::collection($articles),
            'articlesCount' => $articlesCount,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/articles",
     *     operationId="storeArticle",
     *     tags={"Articles"},
     *     summary="Store a newly created article",
     *     description="Create a new article with sections, tags and images",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Article")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Article")
     *     )
     * )
     */
    public function store(ArticleRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Обработка разделов (sections)
        $sectionIds = [];
        if ($request->has('sections')) {
            $sectionTitles = array_column($request->input('sections'), 'title');
            $sectionIds = Section::whereIn('title', $sectionTitles)->pluck('id')->toArray();
        }

        // Обработка тегов
        $tagIds = [];
        if ($request->has('tags')) {
            $tagNames = array_column($request->input('tags'), 'name');
            $tagIds = Tag::whereIn('name', $tagNames)->pluck('id')->toArray();
        }

        // Создание статьи
        $article = Article::create($data);

        if ($sectionIds) {
            $article->sections()->sync($sectionIds);
        }
        if ($tagIds) {
            $article->tags()->sync($tagIds);
        }

        // Обработка изображений (новые файлы или обновление существующих)
        if ($request->has('images')) {
            foreach ($data['images'] as $imageData) {
                if (isset($imageData['file']) && $imageData['file'] instanceof UploadedFile) {
                    // Загружаем новое изображение
                    $path = $imageData['file']->store('article_images', 'public');
                    $image = ArticleImage::create([
                        'path'    => $path,
                        'order'   => $imageData['order'] ?? 0,
                        'alt'     => $imageData['alt'] ?? '',
                        'caption' => $imageData['caption'] ?? '',
                    ]);
                    $article->images()->attach($image->id);
                } elseif (isset($imageData['id'])) {
                    // Обновляем alt и caption для существующего изображения
                    $existingImage = ArticleImage::find($imageData['id']);
                    if ($existingImage) {
                        $existingImage->update([
                            'order'   => $imageData['order'] ?? 0,
                            'alt'     => $imageData['alt'] ?? '',
                            'caption' => $imageData['caption'] ?? '',
                        ]);
                        $article->images()->syncWithoutDetaching([$existingImage->id]);
                    }
                }
            }
        }

        return response()->json(new ArticleResource($article), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/articles/{id}",
     *     operationId="getArticle",
     *     tags={"Articles"},
     *     summary="Display the specified article",
     *     description="Get a specific article by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Article")
     *     )
     * )
     */
    public function show(Article $article): JsonResponse
    {
        return response()->json(new ArticleResource($article));
    }

    /**
     * @OA\Put(
     *     path="/api/articles/{id}",
     *     operationId="updateArticle",
     *     tags={"Articles"},
     *     summary="Update the specified article",
     *     description="Update a specific article by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Article")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Article")
     *     )
     * )
     */
    public function update(ArticleRequest $request, Article $article): JsonResponse
    {
        $data = $request->validated();
        $imagesData = $data['images'] ?? [];
        unset($data['images']);

        // Обработка удаления изображений (удалённых с клиента)
        if ($request->has('deletedImages') && is_array($request->deletedImages)) {
            $this->deleteImages($request->deletedImages);
        }

        // Обновление данных статьи
        $article->update($data);

        // Обновление связей разделов (sections)
        $sectionIds = [];
        if ($request->has('sections')) {
            $sectionTitles = array_column($request->input('sections'), 'title');
            $sectionIds = Section::whereIn('title', $sectionTitles)->pluck('id')->toArray();
        }
        $article->sections()->sync($sectionIds);

        // Обновление связей тегов
        $tagIds = [];
        if ($request->has('tags')) {
            $tagNames = array_column($request->input('tags'), 'name');
            $tagIds = Tag::whereIn('name', $tagNames)->pluck('id')->toArray();
        }
        $article->tags()->sync($tagIds);

        // Обработка изображений: новые или обновление существующих
        if (!empty($imagesData)) {
            $imageIds = [];
            foreach ($imagesData as $imageData) {
                if (isset($imageData['file']) && $imageData['file'] instanceof UploadedFile) {
                    $path = $imageData['file']->store('article_images', 'public');
                    $image = ArticleImage::create([
                        'path'    => $path,
                        'order'   => $imageData['order'] ?? 0,
                        'alt'     => $imageData['alt'] ?? '',
                        'caption' => $imageData['caption'] ?? '',
                    ]);
                    $imageIds[] = $image->id;
                } elseif (isset($imageData['id'])) {
                    $existingImage = ArticleImage::find($imageData['id']);
                    if ($existingImage) {
                        $existingImage->update([
                            'order'   => $imageData['order'] ?? 0,
                            'alt'     => $imageData['alt'] ?? '',
                            'caption' => $imageData['caption'] ?? '',
                        ]);
                        $imageIds[] = $existingImage->id;
                    } else {
                        Log::warning('Существующее изображение не найдено', ['image_id' => $imageData['id']]);
                    }
                }
            }
            $article->images()->sync($imageIds);
        } else {
            Log::info('В запросе нет изображений');
        }

        return response()->json(new ArticleResource($article));
    }

    /**
     * @OA\Delete(
     *     path="/api/articles/{id}",
     *     operationId="deleteArticle",
     *     tags={"Articles"},
     *     summary="Remove the specified article",
     *     description="Delete a specific article by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation"
     *     )
     * )
     */
    public function destroy(Article $article): JsonResponse
    {
        // Удаление связанных изображений
        foreach ($article->images as $image) {
            if ($image->path && Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
                Log::info("Файл успешно удалён: {$image->path}");
            } else {
                Log::warning("Файл не найден: {$image->path}");
            }
            $image->delete();
        }

        $article->delete();

        return response()->json(null, 204);
    }

    /**
     * Вспомогательный метод для удаления изображений по списку ID.
     *
     * @param array $imageIds
     * @return void
     */
    private function deleteImages(array $imageIds): void
    {
        $imagesToDelete = ArticleImage::whereIn('id', $imageIds)->get();

        foreach ($imagesToDelete as $image) {
            if ($image->path && Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
                Log::info("Файл успешно удалён: {$image->path}");
            } else {
                Log::warning("Файл не найден: {$image->path}");
            }
            $image->delete();
        }

        Log::info('Удалены изображения: ', ['image_ids' => $imageIds]);
    }
}
