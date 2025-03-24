<?php

use App\Models\Admin\Setting\Setting;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Маршруты для определения языка приложения
Route::get('/settings/locale', [\App\Http\Controllers\Admin\Setting\SettingController::class, 'getLocaleSetting']);
Route::post('/settings/locale', [\App\Http\Controllers\Admin\Setting\SettingController::class, 'updateLocaleSetting']);

// Маршрут очищает весь кэш
Route::post('/admin/cache/clear', [App\Http\Controllers\Admin\System\SystemController::class, 'clearCache'])->name('cache.clear');

// Главная страница
Route::get('/', fn() => Inertia::render('Welcome'));

// Отображение конкретной рубрики
Route::get('/rubrics/{url}', [\App\Http\Controllers\Public\Default\RubricController::class, 'show'])->where('url', '.*');

// Отображение конкретной статьи
Route::get('/articles/{url}', [\App\Http\Controllers\Public\Default\ArticleController::class, 'show'])->where('url', '.*');

// Профиль Пользователя
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {
    Route::get('/dashboard', function () {return Inertia::render('Dashboard');})
        ->name('dashboard');
});

// Главная Панели Администратора
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {
    Route::get('/admin', function () {return Inertia::render('Admin');})
        ->name('admin');
});

// Все маршруты Административной части
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    ->prefix('admin')
    ->group(function () {

        Route::resource('/settings', \App\Http\Controllers\Admin\Setting\SettingController::class);
        Route::put('/admin/settings/{id}', [\App\Http\Controllers\Admin\Setting\SettingController::class, 'update'])
            ->name('settings.update');

        Route::resource('/parameters', \App\Http\Controllers\Admin\Parameter\ParameterController::class);

        Route::resource('/users', \App\Http\Controllers\Admin\User\UserController::class);
        Route::resource('/roles', \App\Http\Controllers\Admin\Role\RoleController::class);
        Route::resource('/permissions', \App\Http\Controllers\Admin\Permission\PermissionController::class);
        Route::resource('/rubrics', \App\Http\Controllers\Admin\Rubric\RubricController::class);
        Route::resource('/sections', \App\Http\Controllers\Admin\Section\SectionController::class);
        Route::resource('/articles', \App\Http\Controllers\Admin\Article\ArticleController::class);
        Route::resource('/charts', \App\Http\Controllers\Admin\Chart\ChartController::class);
        Route::resource('/reports', \App\Http\Controllers\Admin\Report\ReportController::class)
            ->only(['index']);

        // Основные CRUD операции для комментариев
        Route::resource('/comments', \App\Http\Controllers\Admin\Comment\CommentController::class);

        Route::resource('/components', \App\Http\Controllers\Admin\Component\ComponentController::class);
        Route::post('/components/save', [\App\Http\Controllers\Admin\Component\ComponentController::class, 'save'])->name('components.save');

        Route::resource('/diagrams', \App\Http\Controllers\Admin\Diagram\DiagramController::class);

        Route::resource('/plugins', \App\Http\Controllers\Admin\Plugin\PluginController::class);

        // Маршрут для загрузки отчётов
        Route::get('/reports/download', [\App\Http\Controllers\Admin\Report\ReportController::class, 'download']);

        // Группа маршрутов для удаления опций у которых связь многие ко многим
        Route::delete('/roles/{role}/permissions/{permission}',
            \App\Http\Controllers\Admin\Invokable\RemovePermissionFromRoleController::class)
            ->name('roles.permissions.destroy');

        Route::delete('/users/{user}/roles/{role}',
            \App\Http\Controllers\Admin\Invokable\RemoveRoleFromUserController::class)
            ->name('users.roles.destroy');

        Route::delete('/users/{user}/permissions/{permission}',
            \App\Http\Controllers\Admin\Invokable\RemovePermissionFromUserController::class)
            ->name('users.permissions.destroy');

        Route::delete('/rubrics/{rubric}/sections/{section}',
            \App\Http\Controllers\Admin\Invokable\RemoveRubricFromSectionController::class)
            ->name('rubrics.sections.destroy');

        Route::delete('/sections/{section}/articles/{article}',
            \App\Http\Controllers\Admin\Invokable\RemoveArticleFromSectionController::class)
            ->name('sections.articles.destroy');

        // Группа маршрутов для клонирования сущности
        Route::post('/rubrics/clone/{id}',
            [\App\Http\Controllers\Admin\Rubric\RubricController::class, 'clone'])
            ->name('rubrics.clone');

        Route::post('/sections/clone/{id}',
            [\App\Http\Controllers\Admin\Section\SectionController::class, 'clone'])
            ->name('sections.clone');

        Route::post('/articles/clone/{id}',
            [\App\Http\Controllers\Admin\Article\ArticleController::class, 'clone'])
            ->name('articles.clone');

        // Группа маршрутов для переключения активности
        Route::put('/rubrics/{id}/updateActivity',
            [\App\Http\Controllers\Admin\Rubric\RubricController::class, 'updateActivity'])
            ->name('rubrics.updateActivity');

        Route::put('/sections/{id}/updateActivity',
            [\App\Http\Controllers\Admin\Section\SectionController::class, 'updateActivity'])
            ->name('sections.updateActivity');

        Route::put('/articles/{id}/updateActivity',
            [\App\Http\Controllers\Admin\Article\ArticleController::class, 'updateActivity'])
            ->name('articles.updateActivity');

        Route::put('/settings/{id}/updateActivity',
            [\App\Http\Controllers\Admin\Parameter\ParameterController::class, 'updateActivity'])
            ->name('settings.updateActivity');

        Route::put('/plugins/{id}/updateActivity',
            [\App\Http\Controllers\Admin\Plugin\PluginController::class, 'updateActivity'])
            ->name('plugins.updateActivity');

        Route::put('/comments/{id}/updateActivity',
            [\App\Http\Controllers\Admin\Comment\CommentController::class, 'updateActivity'])
            ->name('comments.updateActivity');

        // Группа маршрутов для переключения Статей как главными и в сайдбаре
        Route::put('/articles/{id}/updateMain',
            [\App\Http\Controllers\Admin\Article\ArticleController::class, 'updateMain'])
            ->name('articles.updateMain');

        Route::put('/articles/{id}/updateSidebar',
            [\App\Http\Controllers\Admin\Article\ArticleController::class, 'updateSidebar'])
            ->name('articles.updateSidebar');

        // Группа маршрутов для обновления сортировки
        Route::put('/rubrics/{rubric}/updateSort',
            [\App\Http\Controllers\Admin\Rubric\RubricController::class, 'updateSort'])
            ->name('rubrics.updateSort');

        Route::put('/sections/{section}/updateSort',
            [\App\Http\Controllers\Admin\Section\SectionController::class, 'updateSort'])
            ->name('sections.updateSort');

        Route::put('/articles/{article}/updateSort',
            [\App\Http\Controllers\Admin\Article\ArticleController::class, 'updateSort'])
            ->name('articles.updateSort');

        // Группа маршрутов для массового удаления сущностей
        Route::delete('/admin/rubrics/bulk-delete',
            [\App\Http\Controllers\Admin\Rubric\RubricController::class, 'bulkDestroy'])
            ->name('rubrics.bulkDestroy');

        Route::delete('/admin/sections/bulk-delete',
            [\App\Http\Controllers\Admin\Section\SectionController::class, 'bulkDestroy'])
            ->name('sections.bulkDestroy');

        Route::delete('/admin/articles/bulk-delete',
            [\App\Http\Controllers\Admin\Article\ArticleController::class, 'bulkDestroy'])
            ->name('articles.bulkDestroy');

        Route::delete('/comments/bulk-delete',
            [\App\Http\Controllers\Admin\Comment\CommentController::class, 'bulkDestroy'])
            ->name('comments.bulkDestroy');

        // Маршрут для модерации комментария
        Route::put('/comments/{id}/approve',
            [\App\Http\Controllers\Admin\Comment\CommentController::class, 'approve'])
            ->name('comments.approve');

    });

// Laravel File Manager для загрузки изображений
Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
});

// Проверка Redis
Route::get('/redis-test', function () {
    Cache::put('key', 'value', 10);
    return Cache::get('key');
});
