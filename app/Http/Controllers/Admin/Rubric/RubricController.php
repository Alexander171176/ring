<?php

namespace App\Http\Controllers\Admin\Rubric;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Rubric\RubricRequest; // реквест для store, update

// Реквесты для простых действий
use App\Http\Requests\Admin\UpdateSortEntityRequest;
use App\Http\Requests\Admin\UpdateActivityRequest;

use App\Http\Resources\Admin\Rubric\RubricResource;
use App\Models\Admin\Rubric\Rubric;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; // Оставляем для bulkDestroy
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable; // Для обработки исключений в транзакциях

/**
 * Контроллер для управления Рубриками в административной панели.
 *
 * Предоставляет CRUD операции, а также дополнительные действия:
 * - Массовое удаление
 * - Обновление активности и сортировки (одиночное и массовое)
 * - Клонирование
 *
 * @version 1.1 (Улучшен с RMB, транзакциями, Form Requests)
 * @author Александр Косолапов <kosolapov1976@gmail.com>
 * @see \App\Models\Admin\Rubric\Rubric Модель Рубрики
 * @see \App\Http\Requests\Admin\Rubric\RubricRequest Запрос для создания/обновления
 */
class RubricController extends Controller
{
    /**
     * Отображение списка всех Рубрик.
     * Загружает пагинированный список с сортировкой по настройкам.
     * Передает данные для отображения и настройки пагинации/сортировки.
     * Пагинация и сортировка выполняются на фронтенде.
     *
     * @return Response
     */
    public function index(): Response
    {
        // TODO: Проверка прав $this->authorize('view-rubric', Rubric::class);

        // Получаем настройки для фронтенда (дефолтные значения)
        $adminCountRubrics = config('site_settings.AdminCountRubrics', 15); // Для ItemsPerPageSelect
        $adminSortRubrics  = config('site_settings.AdminSortRubrics', 'idDesc'); // Для SortSelect

        try {
            // Загружаем ВСЕ рубрики с количеством секций (или без, если не нужно в таблице)
            $rubrics = Rubric::withCount('sections')->get(); // Загружаем ВСЕ

            $rubricsCount = $rubrics->count(); // Считаем из загруженной коллекции

        } catch (Throwable $e) {
            Log::error("Ошибка загрузки рубрик для Index: " . $e->getMessage());
            $rubrics = collect(); // Пустая коллекция в случае ошибки
            $rubricsCount = 0;
            session()->flash('error', 'Не удалось загрузить список рубрик.');
        }

        return Inertia::render('Admin/Rubrics/Index', [
            // Передаем ПОЛНУЮ коллекцию ресурсов
            'rubrics' => RubricResource::collection($rubrics),
            'rubricsCount' => $rubricsCount,
            'adminCountRubrics' => (int)$adminCountRubrics,
            'adminSortRubrics' => $adminSortRubrics, // Это значение прочитает SortSelect при загрузке
        ]);
    }

    /**
     * Отображение формы создания новой рубрики.
     * Передает список секций для выбора.
     *
     * @return Response
     */
    public function create(): Response
    {
        // TODO: Проверка прав доступа $this->authorize('create-rubric', Rubric::class);
        return Inertia::render('Admin/Rubrics/Create');
    }

    /**
     * Сохранение новой рубрики в базе данных.
     * Использует RubricRequest для валидации и авторизации.
     *
     * @param RubricRequest $request
     * @return RedirectResponse Редирект на список рубрик с сообщением.
     */
    public function store(RubricRequest $request): RedirectResponse
    {
        // authorize() уже выполнен в RubricRequest
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $rubric = Rubric::create($data);
            DB::commit();

            Log::info('Рубрика успешно создана: ', $rubric->toArray());
            return redirect()->route('admin.rubrics.index')->with('success', 'Рубрика успешно создана.');

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при создании рубрики: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->withErrors(['general' => 'Произошла ошибка при создании рубрики.']);
        }
    }

    /**
     * Отображение формы редактирования существующей рубрики.
     * Использует Route Model Binding для получения модели.
     *
     * @param Rubric $rubric Модель рубрики, найденная по ID из маршрута.
     * @return Response
     */
    public function edit(Rubric $rubric): Response // Используем Route Model Binding
    {
        // TODO: Проверка прав доступа $this->authorize('update-rubric', $rubric);

        return Inertia::render('Admin/Rubrics/Edit', [
            'rubric' => new RubricResource($rubric),
        ]);
    }

    /**
     * Обновление существующей рубрики в базе данных.
     * Использует RubricRequest и Route Model Binding.
     * Синхронизирует связанные секции, если они переданы.
     *
     * @param RubricRequest $request Валидированный запрос.
     * @param Rubric $rubric Модель рубрики для обновления.
     * @return RedirectResponse Редирект на список рубрик с сообщением.
     */
    public function update(RubricRequest $request, Rubric $rubric): RedirectResponse // Используем RMB
    {
        // authorize() уже выполнен в RubricRequest
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $rubric->update($data);
            DB::commit();

            Log::info('Рубрика обновлена: ', $rubric->toArray());
            return redirect()->route('admin.rubrics.index')->with('success', 'Рубрика успешно обновлена.');

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при обновлении рубрики ID {$rubric->id}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->withErrors(['general' => 'Произошла ошибка при обновлении рубрики.']);
        }
    }

    /**
     * Удаление указанной рубрики.
     * Использует Route Model Binding. Связи удаляются каскадно.
     *
     * @param Rubric $rubric Модель рубрики для удаления.
     * @return RedirectResponse Редирект на список рубрик с сообщением.
     */
    public function destroy(Rubric $rubric): RedirectResponse
    {
        // TODO: Проверка прав доступа $this->authorize('delete-rubric', $rubric);
        try {
            DB::beginTransaction();
            $rubricTitle = $rubric->title;
            $rubricId = $rubric->id;
            $rubric->delete();
            DB::commit();

            Log::info("Рубрика удалена: ID {$rubricId}, Title: {$rubricTitle}");
            return redirect()->route('admin.rubrics.index')->with('success', 'Рубрика успешно удалена.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при удалении рубрики ID {$rubric->id}: " . $e->getMessage());
            // При редиректе назад лучше использовать withErrors, а не with('error')
            return back()->withErrors(['general' => 'Произошла ошибка при удалении рубрики.']);
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
        // TODO: Проверка прав $this->authorize('delete-rubrics');
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:rubrics,id',
        ]);

        $rubricIds = $validated['ids'];
        $count = count($rubricIds); // Получаем количество для сообщения

        try {
            DB::beginTransaction(); // Оставляем транзакцию для массовой операции
            Rubric::whereIn('id', $rubricIds)->delete();
            DB::commit();
            Log::info('Рубрики удалены: ', $rubricIds);
            // Формируем сообщение об успехе
            $message = "Выбранные рубрики ({$count} шт.) успешно удалены.";
            // Редирект на индексную страницу с сообщением
            return redirect()->route('admin.rubrics.index')->with('success', $message);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при массовом удалении рубрик: " . $e->getMessage(), ['ids' => $rubricIds]);
            // Редирект назад с сообщением об ошибке
            return back()->withErrors(['general' => 'Произошла ошибка при удалении рубрик.']);
        }
    }

    /**
     * Обновление статуса активности рубрики.
     * Использует Route Model Binding и UpdateActivityRequest.
     *
     * @param UpdateActivityRequest $request Валидированный запрос с полем 'activity'.
     * @param Rubric $rubric Модель рубрики для обновления.
     * @return RedirectResponse Редирект назад с сообщением.
     */
    public function updateActivity(UpdateActivityRequest $request, Rubric $rubric): RedirectResponse
    {
        // authorize() в UpdateActivityRequest
        $validated = $request->validated();

        try {
            $rubric->activity = $validated['activity'];
            $rubric->save();
            $actionText = $rubric->activity ? 'активирована' : 'деактивирована';
            Log::info("Обновлено activity рубрики ID {$rubric->id} на {$rubric->activity}");

            // Возвращаем редирект НАЗАД с сообщением об успехе
            return back()->with('success', "Рубрика \"{$rubric->title}\" {$actionText}.");

        } catch (Throwable $e) {
            Log::error("Ошибка обновления активности рубрики ID {$rubric->id}: " . $e->getMessage());
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
            'ids.*'    => 'required|integer|exists:rubrics,id',
            'activity' => 'required|boolean',
        ]);

        Rubric::whereIn('id', $data['ids'])->update(['activity' => $data['activity']]);

        return response()->json(['success' => true]);
    }

    /**
     * Обновление значения сортировки для одной рубрики.
     * Использует Route Model Binding и UpdateSortRequest.
     * *
     * @param UpdateSortEntityRequest $request Валидированный запрос с полем 'sort'.
     * @param Rubric $rubric Модель рубрики для обновления.
     * @return RedirectResponse Редирект назад с сообщением..
     */
    public function updateSort(UpdateSortEntityRequest $request, Rubric $rubric): RedirectResponse
    {
        // authorize() в UpdateSortEntityRequest
        $validated = $request->validated();
        try {
            $rubric->sort = $validated['sort'];
            $rubric->save();
            Log::info("Обновлено sort рубрики ID {$rubric->id} на {$rubric->sort}");
            return back();

        } catch (Throwable $e) {
            Log::error("Ошибка обновления сортировки рубрики ID {$rubric->id}: " . $e->getMessage());
            return back()->withErrors(['sort' => 'Не удалось обновить сортировку.']);
        }
    }

    /**
     * Массовое обновление сортировки на основе переданного порядка ID.
     * Принимает массив объектов вида `[{id: 1, sort: 10}, {id: 5, sort: 20}]`.
     *
     * @param Request $request Запрос с массивом 'rubrics'.
     * @return RedirectResponse Редирект назад с сообщением.
     */
    public function updateSortBulk(Request $request): RedirectResponse
    {
        // TODO: Проверка прав $this->authorize('update-rubrics');

        // Валидируем входящий массив
        // (Можно вынести в отдельный FormRequest: UpdateSortBulkRequest)
        $validated = $request->validate([
            'rubrics' => 'required|array',
            'rubrics.*.id' => ['required', 'integer', 'exists:rubrics,id'],
            'rubrics.*.sort' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['rubrics'] as $rubricData) {
                // Используем update для массового обновления, если возможно, или where/update
                Rubric::where('id', $rubricData['id'])->update(['sort' => $rubricData['sort']]);
            }

            DB::commit();
            Log::info('Массово обновлена сортировка рубрик', ['count' => count($validated['rubrics'])]);

            return back();

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка массового обновления сортировки рубрик: " . $e->getMessage());

            // Возвращаем редирект назад с ошибкой
            return back()->withErrors(['general' => 'Не удалось обновить порядок рубрик.']);
        }
    }

    /**
     * Клонирование рубрики.
     * Копирует основные поля и связи с секциями.
     * Генерирует новые уникальные title и url.
     *
     * @param Request $request (Не используется, но нужен для сигнатуры маршрута)
     * @param Rubric $rubric Модель рубрики для клонирования (через RMB).
     * @return RedirectResponse Редирект на список рубрик с сообщением.
     */
    public function clone(Request $request, Rubric $rubric): RedirectResponse
    {
        // TODO: Проверка прав $this->authorize('clone-rubric', $rubric);

        DB::beginTransaction();
        try {
            $clonedRubric = $rubric->replicate();
            $clonedRubric->title = $rubric->title . '-2';
            $clonedRubric->url = $rubric->url . '-2';
            $clonedRubric->activity = false;
            $clonedRubric->views = 0;
            $clonedRubric->created_at = now();
            $clonedRubric->updated_at = now();
            $clonedRubric->save(); // Сохраняем клон

            DB::commit();

            Log::info('Рубрика ID ' . $rubric->id . ' успешно клонирована в ID ' . $clonedRubric->id);

            // Возвращаем редирект на индексную страницу с сообщением успеха
            return redirect()->route('admin.rubrics.index')->with('success', 'Рубрика успешно клонирована.');

        } catch (Throwable $e) {
            DB::rollBack();
            $errorMessage = 'Ошибка клонирования рубрики.';
            // Проверяем на ошибку уникальности
            if ($e instanceof QueryException && (str_contains($e->getMessage(),
                        'повторяющееся значение ключа нарушает ограничение уникальности') ||
                    str_contains($e->getMessage(), 'Нарушение ограничений целостности') ) ) {
                // Пытаемся определить, какое поле вызвало конфликт (зависит от СУБД и сообщения)
                if (str_contains($e->getMessage(), 'rubrics_locale_title_unique')) {
                    $errorMessage = 'Ошибка: Рубрика с таким Названием и Языком уже существует.';
                } elseif (str_contains($e->getMessage(), 'rubrics_locale_url_unique')) {
                    $errorMessage = 'Ошибка: Рубрика с таким URL и Языком уже существует.';
                } else {
                    $errorMessage = 'Ошибка клонирования: Нарушение уникальности.';
                }
                Log::warning("Ошибка уникальности при клонировании рубрики ID {$rubric->id}: " . $e->getMessage());
            } else {
                Log::error("Ошибка при клонировании рубрики ID {$rubric->id}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            }
            // Возвращаем назад с ошибкой во flash-сессии
            return back()->withInput()->withErrors(['general' => $errorMessage]);
        }
    }
}
