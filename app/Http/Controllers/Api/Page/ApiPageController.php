<?php

namespace App\Http\Controllers\Api\Page;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Page\PageRequest;
use App\Http\Resources\Admin\Page\PageResource;
use App\Models\Admin\Page\Page;
use App\Models\Admin\Setting\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
 *     schema="Page",
 *     type="object",
 *     title="Page",
 *     description="Page model",
 *     properties={
 *         @OA\Property(property="id", type="integer", description="Page ID"),
 *         @OA\Property(property="title", type="string", description="Page title"),
 *         @OA\Property(property="content", type="string", description="Page content"),
 *         @OA\Property(property="slug", type="string", description="Page slug"),
 *         @OA\Property(property="activity", type="boolean", description="Page activity"),
 *         @OA\Property(property="print_in_menu", type="boolean", description="Page print in menu"),
 *         @OA\Property(property="sort", type="integer", description="Page sort"),
 *         @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", description="Last update date")
 *     }
 * )
 */
class ApiPageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/pages",
     *     operationId="getPages",
     *     tags={"Pages"},
     *     summary="Display a listing of the pages",
     *     description="Get a list of pages with their parent and children pages",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Page")
     *         )
     *     )
     * )
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $pages = Page::with(['parent', 'children'])->get();
        $pagesCount = DB::table('pages')->count();

        return response()->json([
            'pages' => PageResource::collection($pages),
            'pagesCount' => $pagesCount,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/pages",
     *     operationId="storePage",
     *     tags={"Pages"},
     *     summary="Store a newly created page",
     *     description="Create a new page",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Page")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Page")
     *     )
     * )
     */
    public function store(PageRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $page = Page::create($data);

        // Log::info('API - Страница создана: ', $page->toArray());

        return response()->json(new PageResource($page), 201);
    }

    /**
     * @OA\Put(
     *     path="/api/pages/{id}",
     *     operationId="updatePage",
     *     tags={"Pages"},
     *     summary="Update the specified page",
     *     description="Update a specific page by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Page")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Page")
     *     )
     * )
     */
    public function update(PageRequest $request, string $id): \Illuminate\Http\JsonResponse
    {
        $page = Page::findOrFail($id);
        $data = $request->validated();
        $page->update($data);

        // Log::info('API - Страница обновлена: ', $page->toArray());

        return response()->json(new PageResource($page));
    }

    /**
     * @OA\Delete(
     *     path="/api/pages/{id}",
     *     operationId="deletePage",
     *     tags={"Pages"},
     *     summary="Remove the specified page",
     *     description="Delete a specific page by ID",
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
    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        $page = Page::findOrFail($id);
        $page->delete();

        // Log::info('API - Страница удалена: ', $page->toArray());

        return response()->json(null, 204);
    }

    /**
     * @OA\Post(
     *     path="/api/pages/bulk-destroy",
     *     operationId="bulkDestroyPages",
     *     tags={"Pages"},
     *     summary="Bulk delete pages",
     *     description="Delete multiple pages by their IDs",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean"))
     *     )
     * )
     */
    public function bulkDestroy(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:pages,id',
        ]);

        $pageIds = $validated['ids'];

        Page::whereIn('id', $pageIds)->delete();

        // Log::info('API - Страницы удалены: ', ['ids' => $pageIds]);

        return response()->json(['success' => true]);
    }

    /**
     * @OA\Patch(
     *     path="/api/pages/{id}/activity",
     *     operationId="updatePageActivity",
     *     tags={"Pages"},
     *     summary="Update the activity of the specified page",
     *     description="Update the activity status of a specific page by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="activity", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean"))
     *     )
     * )
     */
    public function updateActivity(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'activity' => 'required|boolean',
        ]);

        $page = Page::findOrFail($id);
        $page->activity = $validated['activity'];
        $page->save();

        // Log::info("API - Обновлено activity страницы: ", $page->toArray());

        return response()->json(['success' => true]);
    }

    /**
     * @OA\Patch(
     *     path="/api/pages/{id}/print-in-menu",
     *     operationId="updatePagePrintInMenu",
     *     tags={"Pages"},
     *     summary="Update the print in menu status of the specified page",
     *     description="Update the print in menu status of a specific page by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="print_in_menu", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean"))
     *     )
     * )
     */
    public function printInMenu(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'print_in_menu' => 'required|boolean',
        ]);

        $page = Page::findOrFail($id);
        $page->print_in_menu = $validated['print_in_menu'];
        $page->save();

        // Log::info("API - Обновлено print_in_menu страницы: ", $page->toArray());

        return response()->json(['success' => true]);
    }

    /**
     * @OA\Patch(
     *     path="/api/pages/{id}/sort",
     *     operationId="updatePageSort",
     *     tags={"Pages"},
     *     summary="Update the sort order of the specified page",
     *     description="Update the sort order of a specific page by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="sort", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean"))
     *     )
     * )
     */
    public function updateSort(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'sort' => 'required|integer',
        ]);

        $page = Page::findOrFail($id);
        $page->sort = $validated['sort'];
        $page->save();

        // Log::info("API - Обновлено sort страницы: ", $page->toArray());

        return response()->json(['success' => true]);
    }

    /**
     * @OA\Post(
     *     path="/api/pages/{id}/clone",
     *     operationId="clonePage",
     *     tags={"Pages"},
     *     summary="Clone the specified page",
     *     description="Clone a specific page by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Page")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Page not found",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     )
     * )
     */
    public function clone(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $page = Page::findOrFail($id);

            $clonedPage = $page->replicate();
            $clonedPage->title = $page->title . ' 2';
            $clonedPage->url = $page->url . '-2';
            $clonedPage->slug = $page->slug . '-2';
            $clonedPage->save();

            // Log::info('API - Страница клонирована: ', $clonedPage->toArray());

            return response()->json(new PageResource($clonedPage));
        } catch (\Exception $e) {
            Log::error('API - Ошибка клонирования страницы: ', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Не удалось клонировать страницу'], 404);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/pages/{slug}",
     *     operationId="getPageBySlug",
     *     tags={"Pages"},
     *     summary="Display the specified page by slug",
     *     description="Get a specific page by slug",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Page")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Page not found",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     )
     * )
     */
    public function show(string $slug): \Illuminate\Http\JsonResponse
    {
        if (in_array($slug, ['dashboard', 'admin'])) {
            throw new NotFoundHttpException();
        }

        $page = Page::where('slug', $slug)->firstOrFail();

        $siteLayout = Setting::where('option', 'siteLayout')->value('value');

        $componentPath = $siteLayout ? 'Templates/' . ucfirst($siteLayout) . '/pages/Page' : 'Page';

        return response()->json(new PageResource($page));
    }

    // все подключенные страницы
    public function showMenu(): \Illuminate\Http\JsonResponse
    {
        $pages = Page::where('activity', true)
            ->where('print_in_menu', true)
            ->orderBy('sort', 'asc')
            ->with(['parent', 'children'])
            ->get();

        return response()->json(PageResource::collection($pages));
    }
}
