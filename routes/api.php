<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Маршрут проверки лайка статьи пользователем
Route::get('/articles/{id}/is-liked', [\App\Http\Controllers\Public\Default\BlogController::class, 'isLiked'])
    ->name('articles.isLiked');

// Маршрут лайка статьи
Route::post('/articles/{id}/like', [\App\Http\Controllers\Public\Default\BlogController::class, 'like'])
    ->name('articles.like');

// Просмотр всех комментариев доступных для статьи
Route::get('comments/{article}', [\App\Http\Controllers\Public\CommentController::class, 'index'])->name('api.comments.index');
// Создание нового комментария
Route::post('comments', [\App\Http\Controllers\Public\CommentController::class, 'store'])->name('api.comments.store');
// Просмотр конкретного комментария
Route::get('comments/{comment}', [\App\Http\Controllers\Public\CommentController::class, 'show'])->name('api.comments.show');
// Редактирование комментария
Route::put('comments/{comment}', [\App\Http\Controllers\Public\CommentController::class, 'update'])->name('api.comments.update');

// Удаление комментария
Route::delete('comments/{comment}', [\App\Http\Controllers\Public\CommentController::class, 'destroy'])->name('api.comments.destroy');

// получение настройки выключения сайта на тех.работы
Route::get('/settings/downtimeSite',
    [\App\Http\Controllers\Admin\Setting\SettingController::class, 'getDowntimeSiteSetting']);

// запись, получение настройки цвета и прозрачности
Route::get('/settings/sidebar', [\App\Http\Controllers\Admin\Setting\SettingController::class, 'getSidebarSettings']);
Route::post('/settings/sidebar', [\App\Http\Controllers\Admin\Setting\SettingController::class, 'updateSidebarSettings']);
Route::get('/settings/widget-panel', [\App\Http\Controllers\Admin\Setting\SettingController::class, 'getWidgetPanelSettings']);
Route::post('/settings/widget-panel', [\App\Http\Controllers\Admin\Setting\SettingController::class, 'updateWidgetPanelSettings']);

// получение значения активности модуля
Route::get('/plugins/active', [\App\Http\Controllers\Api\Plugin\ApiPluginController::class, 'getActivePlugins']);

// создание таблицы модуля и получение данных с таблицы
Route::post('/plugins/create-table', [\App\Http\Controllers\Api\Plugin\ApiPluginController::class, 'createTable']);
Route::get('/plugins/{pluginName}/data', [\App\Http\Controllers\Admin\Plugin\PluginController::class, 'getPluginData']);

// маршруты для получения и обновления настроек модуля
Route::get('/plugins/{pluginName}/settings', [\App\Http\Controllers\Admin\Plugin\PluginController::class, 'getPluginSettings']);
Route::post('/plugins/{pluginName}/update-settings', [\App\Http\Controllers\Admin\Plugin\PluginController::class, 'updatePluginSettings']);

// CRUD блоков модуля и Swagger
Route::prefix('plugins/{pluginName}/blocks')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\Plugin\ApiPluginController::class, 'indexBlocks']);    // Получить все блоки
    Route::post('/', [\App\Http\Controllers\Api\Plugin\ApiPluginController::class, 'storeBlock']);   // Создать новый блок
    Route::get('{id}', [\App\Http\Controllers\Api\Plugin\ApiPluginController::class, 'showBlock']);  // Получить конкретный блок
    Route::put('{id}', [\App\Http\Controllers\Api\Plugin\ApiPluginController::class, 'updateBlock']); // Обновить конкретный блок
    Route::delete('{id}', [\App\Http\Controllers\Api\Plugin\ApiPluginController::class, 'destroyBlock']); // Удалить конкретный блок
    Route::put('{id}/sort', [\App\Http\Controllers\Api\Plugin\ApiPluginController::class, 'updateBlockSort']); // Обновить сортировку блока
    Route::put('{id}/activity', [\App\Http\Controllers\Api\Plugin\ApiPluginController::class, 'updateBlockActivity']); // Обновить активность блока
});

// Swagger
Route::resource('rubrics', \App\Http\Controllers\Api\Rubric\ApiRubricController::class);
Route::resource('articles', \App\Http\Controllers\Api\Article\ApiArticleController::class);
Route::resource('tutorials', \App\Http\Controllers\Api\Tutorial\ApiTutorialController::class);
Route::resource('guides', \App\Http\Controllers\Api\Guide\ApiGuideController::class);
Route::resource('users', \App\Http\Controllers\Api\User\ApiUserController::class);
Route::resource('roles', \App\Http\Controllers\Api\Role\ApiRoleController::class);
Route::resource('permissions', \App\Http\Controllers\Api\Permission\ApiPermissionController::class);
Route::resource('parameters', \App\Http\Controllers\Api\Parameter\ApiParameterController::class);
Route::resource('pages', \App\Http\Controllers\Api\Page\ApiPageController::class);
Route::resource('plugins', \App\Http\Controllers\Api\Plugin\ApiPluginController::class);

// получение страниц для динамического меню
Route::get('/pages', [\App\Http\Controllers\Api\Page\ApiPageController::class, 'showMenu']);
