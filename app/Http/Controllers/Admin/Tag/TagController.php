<?php

namespace App\Http\Controllers\Admin\Tag;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tag\TagRequest;

// Используем

// Реквесты для простых действий
use App\Http\Requests\Admin\UpdateActivityRequest;
use App\Http\Requests\Admin\UpdateSortEntityRequest;
use App\Http\Requests\Admin\UpdateSortRequest;

// Если добавим сортировку тегов

use App\Http\Resources\Admin\Tag\TagResource;
use App\Models\Admin\Tag\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

// Для bulkDestroy
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/**
 * Контроллер для управления Тегами в административной панели.
 *
 * Предоставляет CRUD операции, а также дополнительные действия:
 * - Массовое удаление
 * - Обновление активности и сортировки (одиночное и массовое)
 *
 * @version 1.1 (Улучшен с RMB, транзакциями, Form Requests)
 * @author Александр Косолапов <kosolapov1976@gmail.com>
 * @see \App\Models\Admin\Tag\Tag Модель Тега
 * @see \App\Http\Requests\Admin\Tag\TagRequest Запрос для создания/обновления
 */
class TagController extends Controller
{
    /**
     * Отображение списка всех Тегов.
     * Загружает пагинированный список с сортировкой по настройкам.
     * Передает данные для отображения и настройки пагинации/сортировки.
     * Пагинация и сортировка выполняются на фронтенде.
     *
     * @return Response
     */
    public function index(): Response
    {
        // TODO: Проверка прав $this->authorize('view-tag', Tag::class);

        $adminCountTags = config('site_settings.AdminCountTags', 15);
        $adminSortTags = config('site_settings.AdminSortTags', 'idAsc'); // idAsc по умолчанию

        try {
            $tags = Tag::all();
            $tagsCount = Tag::count();
        } catch (Throwable $e) {
            Log::error("Ошибка загрузки тегов для Index: " . $e->getMessage());
            $tags = collect(); // Пустая коллекция в случае ошибки
            $tagsCount = 0;
            session()->flash('error', 'Не удалось загрузить список тегов.');
        }

        return Inertia::render('Admin/Tags/Index', [
            'tags' => TagResource::collection($tags),
            'tagsCount' => $tagsCount,
            'adminCountTags' => (int)$adminCountTags,
            'adminSortTags' => $adminSortTags,
        ]);
    }

    /**
     * Отображение формы создания нового тега.
     *
     * @return Response
     */
    public function create(): Response
    {
        // TODO: Проверка прав $this->authorize('create-tag', Tag::class);
        return Inertia::render('Admin/Tags/Create');
    }

    /**
     * Сохранение нового тега в базе данных.
     * Использует TagRequest для валидации и авторизации.
     *
     * @param TagRequest $request
     * @return RedirectResponse Редирект на список тегов с сообщением.
     */
    public function store(TagRequest $request): RedirectResponse
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            $tag = Tag::create($data);
            DB::commit();

            Log::info('Тег создан: ', $tag->toArray());
            return redirect()->route('admin.tags.index')->with('success', 'Тег успешно создан.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при создании тега: " . $e->getMessage());
            return back()->withInput()->withErrors(['general' => 'Произошла ошибка при создании тега.']);
        }
    }

    /**
     * Отображение формы редактирования существующего тега.
     * Использует Route Model Binding для получения модели.
     *
     * @param Tag $tag Модель тега, найденный по ID из маршрута.
     * @return Response
     */
    public function edit(Tag $tag): Response // Используем RMB
    {
        // TODO: Проверка прав $this->authorize('update-tag', $tag);

        return Inertia::render('Admin/Tags/Edit', [
            'tag' => new TagResource($tag), // Передаем ресурс тега
        ]);
    }

    /**
     * Обновление существующего тега в базе данных.
     * Использует TagRequest и Route Model Binding.
     *
     * @param TagRequest $request Валидированный запрос.
     * @param Tag $tag Модель тега для обновления.
     * @return RedirectResponse Редирект на список тегов с сообщением.
     */
    public function update(TagRequest $request, Tag $tag): RedirectResponse // Используем RMB
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            $tag->update($data);
            DB::commit();

            Log::info('Тег обновлен: ', $tag->toArray());
            return redirect()->route('admin.tags.index')->with('success', 'Тег успешно обновлен.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при обновлении тега ID {$tag->id}: " . $e->getMessage());
            return back()->withInput()->withErrors(['general' => 'Произошла ошибка при обновлении тега.']);
        }
    }

    /**
     * Удаление указанного тега.
     * Использует Route Model Binding. Связи удаляются каскадно.
     *
     * @param Tag $tag Модель тега для удаления.
     * @return RedirectResponse Редирект на список тегов с сообщением.
     */
    public function destroy(Tag $tag): RedirectResponse // Используем RMB
    {
        // TODO: Проверка прав $this->authorize('delete-tag', $tag);
        try {
            DB::beginTransaction();
            $tag->delete();
            DB::commit();

            Log::info('Тег удален: ID ' . $tag->id);
            return redirect()->route('admin.tags.index')->with('success', 'Тег успешно удален.'); // Редирект на индекс
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при удалении тега ID {$tag->id}: " . $e->getMessage());
            return back()->withErrors(['general' => 'Произошла ошибка при удалении тега.']);
        }
    }

    /**
     * Массовое удаление указанных рубрик.
     * Принимает массив ID в теле запроса.
     *
     * @param Request $request Запрос, содержащий массив 'ids'.
     * @return RedirectResponse Редирект назад с сообщением.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        // TODO: Проверка прав $this->authorize('delete-tags');
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:tags,id',
        ]);

        $tagIds = $validated['ids'];
        $count = count($tagIds); // Получаем количество для сообщения

        try {
            DB::beginTransaction(); // Оставляем транзакцию для массовой операции
            Tag::whereIn('id', $tagIds)->delete();
            DB::commit();

            Log::info('Теги удалены: ', $tagIds);
            // Формируем сообщение об успехе
            $message = "Выбранные теги ({$count} шт.) успешно удалены.";
            // Редирект на индексную страницу с сообщением
            return redirect()->route('admin.tags.index')->with('success', $message);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при массовом удалении тегов: " . $e->getMessage(), ['ids' => $tagIds]);
            // Редирект назад с сообщением об ошибке
            return back()->withErrors(['general' => 'Произошла ошибка при удалении тегов.']);
        }
    }

    /**
     * Обновление статуса активности тега.
     * Использует Route Model Binding и UpdateActivityRequest.
     *
     * @param UpdateActivityRequest $request Валидированный запрос с полем 'activity'.
     * @param Tag $tag Модель тега для обновления.
     * @return RedirectResponse Редирект назад с сообщением.
     */
    public function updateActivity(UpdateActivityRequest $request, Tag $tag): RedirectResponse
    {
        // authorize() в UpdateActivityRequest
        $validated = $request->validated();

        try {
            $tag->activity = $validated['activity'];
            $tag->save();
            $actionText = $tag->activity ? 'активирована' : 'деактивирована';
            Log::info("Обновлено activity тега ID {$tag->id} на {$tag->activity}");

            // Возвращаем редирект НАЗАД с сообщением об успехе
            return back()->with('success', "Тег \"{$tag->title}\" {$actionText}.");

        } catch (Throwable $e) {
            Log::error("Ошибка обновления активности тега ID {$tag->id}: " . $e->getMessage());
            // Возвращаем редирект НАЗАД с сообщением об ошибке
            return back()->withErrors(['general' => 'Произошла ошибка при обновлении активности.']);
        }
    }

    /**
     * Обновление статуса активности массово
     *
     * @param Request $request
     * @return JsonResponse Json ответ
     */
    public function bulkUpdateActivity(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids'      => 'required|array',
            'ids.*'    => 'required|integer|exists:tags,id',
            'activity' => 'required|boolean',
        ]);

        Tag::whereIn('id', $data['ids'])->update(['activity' => $data['activity']]);

        return response()->json(['success' => true]);
    }

    /**
     * Обновление значения сортировки для одного тега.
     * Использует Route Model Binding и UpdateSortRequest.
     * *
     * @param UpdateSortEntityRequest $request Валидированный запрос с полем 'sort'.
     * @param Tag $tag Модель тега для обновления.
     * @return RedirectResponse Редирект назад с сообщением..
     */
    public function updateSort(UpdateSortEntityRequest $request, Tag $tag): RedirectResponse
    {
        // authorize() в UpdateSortEntityRequest
        $validated = $request->validated();
        try {
            $tag->sort = $validated['sort'];
            $tag->save();
            Log::info("Обновлено sort тега ID {$tag->id} на {$tag->sort}");
            return back();

        } catch (Throwable $e) {
            Log::error("Ошибка обновления сортировки тега ID {$tag->id}: " . $e->getMessage());
            return back()->withErrors(['sort' => 'Не удалось обновить сортировку.']);
        }
    }

    /**
     * Массовое обновление сортировки на основе переданного порядка ID.
     * Принимает массив объектов вида `[{id: 1, sort: 10}, {id: 5, sort: 20}]`.
     *
     * @param Request $request Запрос с массивом 'tags'.
     * @return RedirectResponse Редирект назад с сообщением.
     */
    public function updateSortBulk(Request $request): RedirectResponse
    {
        // TODO: Проверка прав $this->authorize('update-tags');

        // Валидируем входящий массив
        // (Можно вынести в отдельный FormRequest: UpdateSortBulkRequest)
        $validated = $request->validate([
            'tags' => 'required|array',
            'tags.*.id' => ['required', 'integer', 'exists:tags,id'],
            'tags.*.sort' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['tags'] as $tagData) {
                // Используем update для массового обновления, если возможно, или where/update
                Tag::where('id', $tagData['id'])->update(['sort' => $tagData['sort']]);
            }

            DB::commit();
            Log::info('Массово обновлена сортировка тегов', ['count' => count($validated['tags'])]);

            return back();

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка массового обновления сортировки тегов: " . $e->getMessage());

            // Возвращаем редирект назад с ошибкой
            return back()->withErrors(['general' => 'Не удалось обновить порядок тегов.']);
        }
    }
}
