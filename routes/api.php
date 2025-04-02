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

Route::get('/site-settings', [\App\Http\Controllers\Api\Setting\ApiSettingController::class, 'index']);

// Получаем значениz параметров системы, по умолчанию
$localePrefix = config('site_settings.locale', 'ru');
$siteLayout = config('site_settings.siteLayout', 'Default');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Рубрики для меню (API маршрут)
$publicRubricController = "App\\Http\\Controllers\\Public\\{$siteLayout}\\RubricController";
Route::get('/menu-rubrics', [$publicRubricController, 'menuRubrics'])->name('api.rubrics.menu');

// Просмотр всех комментариев доступных для статьи
$publicCommentController = "App\\Http\\Controllers\\Public\\{$siteLayout}\\CommentController";

Route::get('comments/{article}', [$publicCommentController, 'index'])->name('api.comments.index');
// Создание нового комментария
Route::post('comments', [$publicCommentController, 'store'])->name('api.comments.store');
// Просмотр конкретного комментария
Route::get('comments/{comment}', [$publicCommentController, 'show'])->name('api.comments.show');
// Редактирование комментария
Route::put('comments/{comment}', [$publicCommentController, 'update'])->name('api.comments.update');
// Удаление комментария
Route::delete('comments/{comment}', [$publicCommentController, 'destroy'])->name('api.comments.destroy');

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
Route::resource('users', \App\Http\Controllers\Api\User\ApiUserController::class);
Route::resource('roles', \App\Http\Controllers\Api\Role\ApiRoleController::class);
Route::resource('permissions', \App\Http\Controllers\Api\Permission\ApiPermissionController::class);
Route::resource('parameters', \App\Http\Controllers\Api\Parameter\ApiParameterController::class);
Route::resource('plugins', \App\Http\Controllers\Api\Plugin\ApiPluginController::class);

