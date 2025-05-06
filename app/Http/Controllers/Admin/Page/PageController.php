<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Page\PageRequest;
use App\Http\Requests\Admin\UpdateActivityRequest;
use App\Http\Resources\Admin\Page\PageResource;
use App\Http\Resources\Admin\Page\PageSharedResource;
use App\Models\Admin\Page\Page;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse; // Добавлено для JsonResponse
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PageController extends Controller
{
    protected array $availableLocales = ['ru', 'en', 'kk']; // Обновите список

    /**
     * Отобразить список ресурсов.
     * GET /admin/pages
     * Route: admin.pages.index
     *
     * @param Request $request
     * @return Response
     * @throws AuthorizationException
     */
    public function index(Request $request): Response
    {
        // TODO: Проверка прав $this->authorize('view-page', Page::class);

        $currentLocale = $request->query('locale', config('app.fallback_locale', 'ru'));

        if (!in_array($currentLocale, $this->availableLocales)) {
            $currentLocale = config('app.fallback_locale', 'ru');
            session()->flash('warning', __('admin/controllers/pages.index_locale_error'));
        }

        $pages = collect();
        $pagesCount = 0;

        try {
            $pages = Page::query()
                ->byLocale($currentLocale)
                ->root()
                ->with(['children' => fn($q) => $q->with(['children' => fn($q2) => $q2->with('children')])])
                ->orderBy('sort')
                ->get();

            $pagesCount = Page::query()->byLocale($currentLocale)->count();

        } catch (Throwable $e) {
            Log::error("Ошибка загрузки страниц для Index (locale: {$currentLocale}): " . $e->getMessage(), ['exception' => $e]);
            session()->flash('error', __('admin/controllers/pages.index_error'));
        }

        return Inertia::render('Admin/Pages/Index', [
            'pages' => PageResource::collection($pages),
            'pagesCount' => $pagesCount,
            'currentLocale' => $currentLocale,
            'availableLocales' => $this->availableLocales,
        ]);
    }

    /**
     * Форма для создания нового ресурса.
     * GET /admin/pages/create
     * Route: admin.pages.create
     *
     * @param Request $request
     * @return Response
     * @throws AuthorizationException
     */
    public function create(Request $request): Response
    {
        // TODO: Проверка прав $this->authorize('create-page', Page::class);

        $targetLocale = $request->query('locale', config('app.fallback_locale', 'ru'));
        if (!in_array($targetLocale, $this->availableLocales)) {
            $targetLocale = config('app.fallback_locale', 'ru');
        }

        $potentialParents = collect();
        try {
            $potentialParents = Page::query()
                ->byLocale($targetLocale)
                ->orderBy('title')
                ->get(['id', 'title', 'parent_id', 'locale']);

        } catch (Throwable $e) {
            Log::error("Ошибка загрузки родительских страниц для Create (locale: {$targetLocale}): " . $e->getMessage(), ['exception' => $e]);
            session()->flash('error', __('admin/controllers/pages.parent_load_error'));
        }

        return Inertia::render('Admin/Pages/Create', [
            'targetLocale' => $targetLocale,
            'potentialParents' => PageSharedResource::collection($potentialParents),
            'availableLocales' => $this->availableLocales,
        ]);
    }

    /**
     * Сохранение вновь созданного ресурса в хранилище.
     * POST /admin/pages
     * Route: admin.pages.store
     *
     * @param PageRequest $request
     * @return RedirectResponse
     */
    public function store(PageRequest $request): RedirectResponse
    {
        // Авторизация выполняется в PageRequest->authorize()

        $data = $request->validated();

        try {
            $page = DB::transaction(function () use ($data) {
                if (!isset($data['sort']) || is_null($data['sort'])) {
                    $maxSort = Page::query()
                        ->where('locale', $data['locale'])
                        ->where('parent_id', $data['parent_id'] ?? null)
                        ->max('sort');
                    $data['sort'] = is_null($maxSort) ? 0 : $maxSort + 1;
                }
                $newPage = Page::create($data);
                Log::info('Страница создана: ', $newPage->toArray());
                return $newPage;
            });

            return redirect()->route('admin.pages.index', ['locale' => $page->locale])
                ->with('success', __('admin/controllers/pages.created'));

        } catch (Throwable $e) {
            Log::error("Ошибка при создании страницы: " . $e->getMessage(), ['exception' => $e, 'data' => $data]);
            return back()->withInput()->with('error', __('admin/controllers/pages.create_error'));
        }
    }

    /**
     * Отобразить форму для редактирования указанного ресурса.
     * GET /admin/pages/{page}/edit
     * Route: admin.pages.edit
     *
     * @param Page $page
     * @return Response|RedirectResponse
     * @throws AuthorizationException
     */
    public function edit(Page $page): Response|RedirectResponse
    {
        // TODO: Проверка прав $this->authorize('update-page', $page);

        $potentialParents = collect();
        try {
            // Загружаем страницы той же локали, кроме самой редактируемой
            $potentialParents = Page::query()
                ->byLocale($page->locale)
                ->where('id', '!=', $page->id)
                ->orderBy('title')
                ->get(['id', 'title', 'parent_id', 'locale']);

        } catch (Throwable $e) {
            Log::error("Ошибка загрузки родительских страниц для Edit (page ID: {$page->id}): " . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('admin.pages.index', ['locale' => $page->locale])
                ->with('error', __('admin/controllers/pages.parent_load_error'));
        }

        return Inertia::render('Admin/Pages/Edit', [
            'page' => new PageResource($page->loadMissing('parent')),
            'potentialParents' => PageSharedResource::collection($potentialParents),
            'availableLocales' => $this->availableLocales,
            'currentLocale' => $page->locale,
        ]);
    }

    /**
     * Обновление указанного ресурса в хранилище.
     * PUT/PATCH /admin/pages/{page}
     * Route: admin.pages.update
     *
     * @param PageRequest $request
     * @param Page $page
     * @return RedirectResponse
     */
    public function update(PageRequest $request, Page $page): RedirectResponse
    {
        $data = $request->validated();
        $originalParentId = $page->parent_id;
        $originalLocale = $page->locale;
        $pageId = $page->id; // Сохраняем ID для лога

        try {
            DB::transaction(function () use ($page, $data, $originalParentId, $originalLocale) {
                $recalculateSort = false;

                if (isset($data['parent_id']) && $data['parent_id'] != $originalParentId) {
                    $recalculateSort = true;
                } else if (isset($data['locale']) && $data['locale'] !== $originalLocale) {
                    $recalculateSort = true;
                    Log::warning("Locale changed for page ID {$page->id} from {$originalLocale} to {$data['locale']}");
                }

                if ($recalculateSort && (!isset($data['sort']) || is_null($data['sort']))) {
                    $maxSort = Page::query()
                        ->where('locale', $data['locale'] ?? $originalLocale)
                        ->where('parent_id', $data['parent_id'] ?? null)
                        ->where('id', '!=', $page->id)
                        ->max('sort');
                    $data['sort'] = is_null($maxSort) ? 0 : $maxSort + 1;
                }

                $page->update($data);
            });

            Log::info("Страница обновлена (ID: {$pageId})", $page->refresh()->toArray());

            return redirect()->route('admin.pages.index', ['locale' => $page->locale])
                ->with('success', __('admin/controllers/pages.updated'));

        } catch (Throwable $e) {
            Log::error("Ошибка при обновлении страницы (ID: {$pageId}): " . $e->getMessage(), ['exception' => $e, 'data' => $data]);
            return back()->withInput()->with('error', __('admin/controllers/pages.update_error'));
        }
    }

    /**
     * Удалить указанный ресурс из хранилища.
     * DELETE /admin/pages/{page}
     * Route: admin.pages.destroy
     *
     * @param Page $page
     * @return RedirectResponse
     */
    public function destroy(Page $page): RedirectResponse
    {

        // TODO: Проверка прав $this->authorize('delete-page', $page);

        $locale = $page->locale;
        $pageTitle = $page->title;
        $pageId = $page->id;

        try {
            DB::transaction(function () use ($page) {
                $page->delete();
            });

            Log::info("Страница '{$pageTitle}' (ID: {$pageId}) удалена.");
            return redirect()->route('admin.pages.index', ['locale' => $locale])
                ->with('success', __('admin/controllers/pages.deleted'));

        } catch (Throwable $e) {
            Log::error("Ошибка при удалении страницы '{$pageTitle}' (ID: {$pageId}): " . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('admin.pages.index', ['locale' => $locale])
                ->with('error', __('admin/controllers/pages.delete_error'));
        }
    }

    /**
     * Обновите статус активности ресурса.
     * PUT /admin/actions/pages/{page}/activity
     * Route: admin.actions.pages.updateActivity
     *
     * @param UpdateActivityRequest $request
     * @param Page $page
     * @return RedirectResponse
     */
    public function updateActivity(UpdateActivityRequest $request, Page $page): RedirectResponse
    {
        // authorize() в UpdateActivityRequest
        $validated = $request->validated();

        try {
            DB::beginTransaction();
            $page->activity = $validated['activity'];
            $page->save();
            DB::commit();

            $action = $page->activity ? __('admin/controllers/pages.activated') : __('admin/controllers/pages.deactivated');
            $message = __('admin/controllers/pages.activity', ['title' => $page->title, 'action' => $action]);
            Log::info("Активность страницы '{$page->title}' (ID: {$page->id}) обновлена: {$action}");

            $actionText = $page->activity ? __('admin/controllers/common.activated')
                : __('admin/controllers/common.deactivated');
            return back()
                ->with('success', __('admin/controllers/pages.activity', ['title' => $page->title, 'action' => $actionText]));

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка обновления активности страницы (ID: {$page->id}): " . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['general' => __('admin/controllers/pages.update_activity_error')]);
        }
    }

    /**
     * Обновить статус активности для нескольких страниц.
     * PUT /admin/actions/pages/bulk-activity
     * Route: admin.actions.pages.bulkUpdateActivity
     *
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     * @throws AuthorizationException
     */
    public function bulkUpdateActivity(Request $request): RedirectResponse|JsonResponse
    {
        // $this->authorize('update', Page::class); // Общее право на обновление

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:pages,id', // Проверяем, что все ID существуют
            'activity' => 'required|boolean',
        ]);

        $pageIds = $validated['ids'];
        $activity = $validated['activity'];

        if (empty($pageIds)) {
            $message = __('admin/controllers/pages.bulk_update_activity_no_selection');
            if ($request->expectsJson()) return response()->json(['message' => $message], 400);
            return back()->with('warning', $message);
        }

        try {
            // Обновляем одним запросом
            $updatedCount = Page::whereIn('id', $pageIds)->update(['activity' => $activity]);

            $action = $activity ? __('admin/controllers/pages.activated') : __('admin/controllers/pages.deactivated');
            $message = __('admin/controllers/pages.bulk_update_activity_success');
            Log::info($message, ['ids' => $pageIds, 'activity' => $activity]);

            if ($request->expectsJson()) {
                return response()->json(['message' => $message, 'updatedCount' => $updatedCount]);
            }
            return back()->with('success', $message);

        } catch (Throwable $e) {
            Log::error("Ошибка при массовом обновлении активности страниц: " . $e->getMessage(), ['exception' => $e, 'ids' => $pageIds]);
            $errorMessage = __('admin/controllers/pages.bulk_update_activity_error');
            if ($request->expectsJson()) {
                return response()->json(['message' => $errorMessage], 500);
            }
            return back()->with('error', $errorMessage);
        }
    }

    /**
     * Обновить значение сортировки для одной страницы.
     * PUT /admin/actions/pages/{page}/sort
     * Route: admin.actions.pages.updateSort
     *
     * @param Request $request
     * @param Page $page
     * @return RedirectResponse|JsonResponse
     */
    public function updateSort(Request $request, Page $page): RedirectResponse|JsonResponse
    {
        // $this->authorize('update', $page);

        $validated = $request->validate([
            'sort' => 'required|integer|min:0',
        ]);

        try {
            $originalSort = $page->sort;
            $newSort = $validated['sort'];

            // TODO: Возможно, нужна логика для "расталкивания" других элементов,
            // если просто присвоить sort, могут появиться дубликаты на одном уровне.
            // Это усложнение, часто для одиночного обновления sort не реализуется.
            $page->sort = $newSort;
            $page->save();

            Log::info("Сортировка страницы '{$page->title}' (ID: {$page->id}) изменена с {$originalSort} на {$newSort}");
            $message = __('admin/controllers/pages.order_updated');

            if ($request->expectsJson()) {
                return response()->json(['message' => $message, 'sort' => $page->sort]);
            }
            return back()->with('success', $message);

        } catch (Throwable $e) {
            Log::error("Ошибка при обновлении сортировки страницы (ID: {$page->id}): " . $e->getMessage(), ['exception' => $e]);
            $errorMessage = __('admin/controllers/pages.update_sort_error');
            if ($request->expectsJson()) {
                return response()->json(['message' => $errorMessage], 500);
            }
            return back()->with('error', $errorMessage);
        }
    }

    /**
     * Обновление порядка и родительских связей страниц после drag-and-drop.
     * PUT /admin/actions/pages/update-sort-bulk
     * Route: admin.actions.pages.updateSortBulk
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function updateSortBulk(Request $request): JsonResponse|RedirectResponse
    {
        $pagesData = $request->input('pages');
        $locale = $request->input('locale');

        if (!is_array($pagesData) || empty($pagesData) || !$locale || !in_array($locale, $this->availableLocales)) {
            $message = __('admin/controllers/pages.invalid_input');
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 400);
            }
            return redirect()->back()->with('error', $message);
        }

        foreach ($pagesData as $index => $pageItem) {
            if (!isset($pageItem['id'], $pageItem['sort']) || !is_numeric($pageItem['id']) || !is_numeric($pageItem['sort'])) {
                $message = __('admin/controllers/pages.invalid_page_ids') . "{$index}.";
                if ($request->expectsJson()) {
                    return response()->json(['message' => $message], 400);
                }
                return redirect()->back()->with('error', $message);
            }
            if (isset($pageItem['parent_id']) && !is_numeric($pageItem['parent_id']) && !is_null($pageItem['parent_id'])) {
                $message = __('admin/controllers/pages.invalid_parent_ids') . "{$index}.";
                if ($request->expectsJson()) {
                    return response()->json(['message' => $message], 400);
                }
                return redirect()->back()->with('error', $message);
            }
        }

        $pageIds = array_column($pagesData, 'id');

        try {
            DB::transaction(function () use ($pagesData, $locale, $pageIds) {
                $existingPagesCount = Page::query()->whereIn('id', $pageIds)->where('locale', $locale)->count();
                if ($existingPagesCount !== count($pageIds)) {
                    throw new \InvalidArgumentException(__('admin/controllers/pages.invalid_page_ids'));
                }

                $parentIds = array_filter(array_unique(array_column($pagesData, 'parent_id')));
                if (!empty($parentIds)) {
                    $existingParentsCount = Page::query()->whereIn('id', $parentIds)->where('locale', $locale)->count();
                    if ($existingParentsCount !== count($parentIds)) {
                        throw new \InvalidArgumentException(__('admin/controllers/pages.invalid_parent_ids'));
                    }
                    foreach ($pagesData as $pageItem) {
                        if (!is_null($pageItem['parent_id']) && $pageItem['id'] == $pageItem['parent_id']) {
                            throw new \InvalidArgumentException(__('admin/controllers/pages.parent_loop_error') . " (ID: {$pageItem['id']})");
                        }
                    }
                }

                foreach ($pagesData as $pageItem) {
                    Page::query()
                        ->where('id', $pageItem['id'])
                        ->update([
                            'sort' => (int)$pageItem['sort'],
                            'parent_id' => isset($pageItem['parent_id']) ? (int)$pageItem['parent_id'] : null,
                        ]);
                }
            });

            $message = __('admin/controllers/pages.order_updated');
            Log::info("Порядок страниц для локали '{$locale}' обновлен.", ['ids' => $pageIds]);

            if ($request->expectsJson()) {
                return response()->json(['message' => $message]);
            }
            return redirect()->back()->with('success', $message);

        } catch (\InvalidArgumentException $e) {
            $message = $e->getMessage();
            Log::warning("Ошибка валидации при обновлении порядка страниц (locale: {$locale}): {$message}", ['data' => $pagesData]);

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 400);
            }
            return redirect()->back()->with('error', $message);

        } catch (Throwable $e) {
            $message = __('admin/controllers/pages.bulk_update_sort_error');
            Log::error("Ошибка при обновлении порядка страниц (locale: {$locale}): " . $e->getMessage(), ['exception' => $e, 'data' => $pagesData]);

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 500);
            }
            return redirect()->back()->with('error', $message);
        }
    }

}
