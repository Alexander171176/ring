<?php

namespace App\Http\Controllers\Admin\Tag;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tag\TagRequest;
use App\Http\Resources\Admin\Tag\TagResource;
use App\Models\Admin\Tag\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $tags = Tag::all();
        $tagsCount = Tag::count();

        // Получаем значение параметра из конфигурации (оно загружается через AppServiceProvider)
        $adminCountTags = config('site_settings.AdminCountTags', 10);
        $adminSortTags  = config('site_settings.AdminSortTags', 'idAsc');

        return Inertia::render('Admin/Tags/Index', [
            'tags' => TagResource::collection($tags),
            'tagsCount' => $tagsCount,
            'adminCountTags' => (int)$adminCountTags,
            'adminSortTags' => $adminSortTags,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Tags/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TagRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Создание разрешения
        $tag = Tag::create($data);

        // Log::info('Тег создан: ', $tag->toArray());

        return redirect()->route('tags.index')->with('success', 'Тег успешно создан');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): Response
    {
        $tag = Tag::findOrFail($id);

        return Inertia::render('Admin/Tags/Edit', [
            'tag' => new TagResource($tag),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TagRequest $request, string $id): RedirectResponse
    {
        $tag = Tag::findOrFail($id);

        $data = $request->validated();

        $tag->update($data);

        // Log::info('Тег обновлен: ', $tag->toArray());

        return redirect()->route('tags.index')->with('success', 'Тег успешно обновлен');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $tag = Tag::findOrFail($id);
        $tag->delete();

        // Log::info('Разрешение удалено: ', $tag->toArray());

        return back();
    }

    /**
     * Удаление выбранных рубрик.
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tags,id',
        ]);

        $tagIds = $validated['ids'];

        Tag::whereIn('id', $tagIds)->each(function ($tag) {
            $tag->delete();
        });

        // Log::info('Теги удалены: ', $tagIds);

        return response()->json(['success' => true, 'reload' => true]);
    }

}
