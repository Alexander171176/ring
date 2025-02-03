<?php

namespace App\Http\Controllers\Api\Article;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Article\ArticleRequest;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Rubric\Rubric;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Info(
 *     title="API Documentation",
 *     version="1.0.0",
 *     @OA\Contact(
 *         email="support@example.com"
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
 *     properties={
 *         @OA\Property(property="id", type="integer", description="Article ID"),
 *         @OA\Property(property="title", type="string", description="Article title"),
 *         @OA\Property(property="content", type="string", description="Article content"),
 *         @OA\Property(property="image_url", type="string", description="Image URL"),
 *         @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", description="Last update date")
 *     }
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
     *     description="Get a list of articles with their rubrics",
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
    public function index(): \Illuminate\Http\JsonResponse
    {
        $articles = Article::with('rubrics')->get();
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
     *     description="Create a new article with rubrics",
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
    public function store(ArticleRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image_url')) {
            $data['image_url'] = $request->file('image_url')->store('article_images', 'public');
        }

        $article = Article::create($data);

        if ($request->has('rubrics')) {
            $rubricTitles = array_map(function ($rubric) {
                return $rubric['title'];
            }, $request->input('rubrics'));

            $rubricIds = Rubric::whereIn('title', $rubricTitles)->pluck('id')->toArray();
            $article->rubrics()->sync($rubricIds);
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
    public function show(Article $article): \Illuminate\Http\JsonResponse
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
    public function update(ArticleRequest $request, Article $article): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image_url')) {
            if ($article->image_url) {
                Storage::disk('public')->delete($article->image_url);
            }
            $data['image_url'] = $request->file('image_url')->store('article_images', 'public');
        } else {
            $data['image_url'] = $article->image_url;
        }

        $article->update($data);

        if ($request->has('rubrics')) {
            $rubricTitles = array_column($request->input('rubrics'), 'title');
            $rubricIds = Rubric::whereIn('title', $rubricTitles)->pluck('id')->toArray();
            $article->rubrics()->sync($rubricIds);
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
    public function destroy(Article $article): \Illuminate\Http\JsonResponse
    {
        $article->delete();

        return response()->json(null, 204);
    }
}
