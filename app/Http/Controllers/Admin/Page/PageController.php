<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Page\PageRequest;
use App\Http\Resources\Admin\Page\PageResource;
use App\Models\Admin\Page\Page;
use App\Models\Admin\Setting\Setting;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends Controller
{
    use CacheTimeTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $pages = Cache::store('redis')->remember('pages.all', $cacheTime, function () {
            return Page::with(['parent', 'children'])->get();
        });

        $pagesCount = Cache::store('redis')->remember('pages.count', $cacheTime, function () {
            return DB::table('pages')->count();
        });

        return Inertia::render('Admin/Pages/Index', [
            'pages' => PageResource::collection($pages),
            'pagesCount' => $pagesCount,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Inertia\Response
    {
        $pages = Page::all();

        return Inertia::render('Admin/Pages/Create', [
            'pages' => PageResource::collection($pages),
            'page' => new PageResource(new Page),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PageRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();
        $page = Page::create($data);

        $this->clearCache(['pages.all', 'pages.count']);

        return redirect()->route('pages.index')->with('success', 'Страница успешно создана');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $page = Cache::store('redis')->remember("page.$id", $cacheTime, function () use ($id) {
            return Page::findOrFail($id);
        });

        return Inertia::render('Admin/Pages/Edit', [
            'page' => new PageResource($page),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PageRequest $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $page = Page::findOrFail($id);
        $data = $request->validated();
        $page->update($data);

        $this->clearCache(['pages.all', 'pages.count', "page.$id"]);

        Log::info('Страница обновлена: ', $page->toArray());

        return redirect()->route('pages.index')->with('success', 'Страница успешно обновлена');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): \Illuminate\Http\RedirectResponse
    {
        $page = Page::findOrFail($id);
        $page->delete();

        $this->clearCache(['pages.all', 'pages.count']);

        Log::info('Страница удалена: ', $page->toArray());

        return back()->with('success', 'Страница успешно удалена');
    }

    /**
     * Bulk delete pages by selected checkboxes.
     */
    public function bulkDestroy(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:pages,id',
        ]);

        $pageIds = $validated['ids'];
        Page::whereIn('id', $pageIds)->delete();

        $this->clearCache(['pages.all', 'pages.count']);

        Log::info('Страницы удалены: ', ['ids' => $pageIds]);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Update page activity status.
     */
    public function updateActivity(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'activity' => 'required|boolean',
        ]);

        $page = Page::findOrFail($id);
        $page->activity = $validated['activity'];
        $page->save();

        $this->clearCache(['pages.all', "page.$id"]);

        Log::info("Обновлено activity страницы с ID: $id с данными: ", $validated);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Update page visibility in menu.
     */
    public function printInMenu(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'print_in_menu' => 'required|boolean',
        ]);

        $page = Page::findOrFail($id);
        $page->print_in_menu = $validated['print_in_menu'];
        $page->save();

        $this->clearCache(['pages.all', "page.$id"]);

        Log::info("Обновлено print_in_menu страницы с ID: $id с данными: ", $validated);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Update page sorting.
     */
    public function updateSort(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'sort' => 'required|integer',
        ]);

        $page = Page::findOrFail($id);
        $page->sort = $validated['sort'];
        $page->save();

        $this->clearCache(['pages.all', "page.$id"]);

        Log::info("Обновлено sort страницы с ID: $id с данными: ", $validated);

        return response()->json(['success' => true]);
    }

    /**
     * Clone a page.
     */
    public function clone(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $page = Page::findOrFail($id);
            $clonedPage = $page->replicate();
            $clonedPage->title .= ' 2';
            $clonedPage->url .= '-2';
            $clonedPage->slug .= '-2';
            $clonedPage->save();

            $this->clearCache(['pages.all', 'pages.count']);

            Log::info('Страница клонирована: ', $clonedPage->toArray());

            return response()->json(['success' => true, 'reload' => true]);
        } catch (\Exception $e) {
            Log::error('Ошибка клонирования страницы: ', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Не удалось клонировать страницу', 'reload' => true]);
        }
    }

    /**
     * Show the page dynamically on the public side.
     */
    public function show($slug): \Inertia\Response
    {
        if (in_array($slug, ['dashboard', 'admin'])) {
            throw new NotFoundHttpException();
        }

        $page = Page::where('slug', $slug)->firstOrFail();
        $siteLayout = Setting::where('option', 'siteLayout')->value('value');
        $componentPath = $siteLayout ? 'Templates/' . ucfirst($siteLayout) . '/pages/Index' : 'Index';

        return Inertia::render($componentPath, [
            'page' => new PageResource($page),
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'template' => ucfirst($siteLayout)
        ]);
    }

}
