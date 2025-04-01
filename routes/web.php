<?php

use App\Models\Admin\Setting\Setting;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
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

// Получаем значениz параметров системы, по умолчанию
$localePrefix = config('site_settings.locale', 'ru');
$siteLayout = config('site_settings.siteLayout', 'Default');

// Маршрут очищает весь кэш
Route::post('/admin/cache/clear', [App\Http\Controllers\Admin\System\SystemController::class, 'clearCache'])->name('cache.clear');

Route::fallback(function (Request $request) {
    if (config('site_settings.downtimeSite', 'false') === 'true') {
        return Inertia::render('Maintenance');
    }
    // Возвращаем компонент NotFound.vue с HTTP-статусом 404
    return Inertia::render('NotFound')->toResponse($request)->setStatusCode(404);
});

Route::middleware([\App\Http\Middleware\CheckDowntime::class])->group(function () use ($siteLayout) {

// Главная страница
    Route::get('/', fn() => Inertia::render('Welcome'));


// Отображение конкретной рубрики
    $publicRubricController = "App\\Http\\Controllers\\Public\\{$siteLayout}\\RubricController";
    Route::get('/rubrics/{url}', [$publicRubricController, 'show'])
        ->where('url', '.*');

// Отображение конкретной статьи
    $publicArticleController = "App\\Http\\Controllers\\Public\\{$siteLayout}\\ArticleController";
    Route::get('/articles/{url}', [$publicArticleController, 'show'])
        ->where('url', '.*');

// Лайк статьи
    Route::post('/articles/{article}/like', [$publicArticleController, 'like'])
        ->name('articles.like');

// Отображение конкретного тега
    $publicTagController = "App\\Http\\Controllers\\Public\\{$siteLayout}\\TagController";
    Route::get('/tags/{url}', [$publicTagController, 'show'])
        ->where('url', '.*');

});

// Профиль Пользователя
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })
        ->name('dashboard');
});


// Главная Панели Администратора
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {
    Route::get('/admin', function () {
        return Inertia::render('Admin');
    })
        ->name('admin');
});

// Все маршруты Административной части
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    ->prefix('admin')
    ->group(function () {

        // количество сущностей на странице Index
        // Определяем кастомный маршрут для обновления AdminCountRubrics перед ресурсным маршрутом
        Route::put('/settings/update-admin-count-rubrics',
            [\App\Http\Controllers\Admin\Setting\SettingController::class, 'updateAdminCountRubrics'])
            ->name('settings.updateAdminCountRubrics');
        Route::put('/settings/update-admin-count-sections',
            [\App\Http\Controllers\Admin\Setting\SettingController::class, 'updateAdminCountSections'])
            ->name('settings.updateAdminCountSections');
        Route::put('/settings/update-admin-count-articles',
            [\App\Http\Controllers\Admin\Setting\SettingController::class, 'updateAdminCountArticles'])
            ->name('settings.updateAdminCountArticles');
        Route::put('/settings/update-admin-count-tags',
            [\App\Http\Controllers\Admin\Setting\SettingController::class, 'updateAdminCountTags'])
            ->name('settings.updateAdminCountTags');
        Route::put('/settings/update-admin-count-tags',
            [\App\Http\Controllers\Admin\Setting\SettingController::class, 'updateAdminCountComments'])
            ->name('settings.updateAdminCountComments');
        Route::put('/settings/update-admin-count-banners',
            [\App\Http\Controllers\Admin\Setting\SettingController::class, 'updateAdminCountBanners'])
            ->name('settings.updateAdminCountBanners');
        Route::put('/settings/update-admin-count-users',
            [\App\Http\Controllers\Admin\Setting\SettingController::class, 'updateAdminCountUsers'])
            ->name('settings.updateAdminCountUsers');
        Route::put('/settings/update-admin-count-roles',
            [\App\Http\Controllers\Admin\Setting\SettingController::class, 'updateAdminCountRoles'])
            ->name('settings.updateAdminCountRoles');
        Route::put('/settings/update-admin-count-permissions',
            [\App\Http\Controllers\Admin\Setting\SettingController::class, 'updateAdminCountPermissions'])
            ->name('settings.updateAdminCountPermissions');
        Route::put('/settings/update-admin-count-settings',
            [\App\Http\Controllers\Admin\Setting\SettingController::class, 'updateAdminCountSettings'])
            ->name('settings.updateAdminCountSettings');
        Route::put('/settings/update-admin-count-plugins',
            [\App\Http\Controllers\Admin\Setting\SettingController::class, 'updateAdminCountPlugins'])
            ->name('settings.updateAdminCountPlugins');

        // Ресурсный маршрут для настроек – ограничиваем параметр id только числами без конфликтов
        Route::resource('/settings', \App\Http\Controllers\Admin\Setting\SettingController::class)
            ->where(['setting' => '[0-9]+']);

        Route::put('/admin/settings/{id}', [\App\Http\Controllers\Admin\Setting\SettingController::class, 'update'])
            ->name('settings.update');

        Route::resource('/parameters', \App\Http\Controllers\Admin\Parameter\ParameterController::class);

        Route::resource('/users', \App\Http\Controllers\Admin\User\UserController::class);
        Route::resource('/roles', \App\Http\Controllers\Admin\Role\RoleController::class);
        Route::resource('/permissions', \App\Http\Controllers\Admin\Permission\PermissionController::class);
        Route::resource('/rubrics', \App\Http\Controllers\Admin\Rubric\RubricController::class);
        Route::resource('/sections', \App\Http\Controllers\Admin\Section\SectionController::class);
        Route::resource('/articles', \App\Http\Controllers\Admin\Article\ArticleController::class);
        Route::resource('/tags', \App\Http\Controllers\Admin\Tag\TagController::class);
        Route::resource('/banners', \App\Http\Controllers\Admin\Banner\BannerController::class);
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

        Route::delete('/sections/{section}/banners/{banner}',
            \App\Http\Controllers\Admin\Invokable\RemoveBannerFromSectionController::class)
            ->name('sections.banners.destroy');

        Route::delete('/articles/{article}/tags/{tag}',
            \App\Http\Controllers\Admin\Invokable\RemoveArticleFromTagController::class)
            ->name('articles.tags.destroy');

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

        Route::put('/banners/{id}/updateActivity',
            [\App\Http\Controllers\Admin\Banner\BannerController::class, 'updateActivity'])
            ->name('banners.updateActivity');

        Route::put('/settings/{id}/updateActivity',
            [\App\Http\Controllers\Admin\Parameter\ParameterController::class, 'updateActivity'])
            ->name('settings.updateActivity');

        Route::put('/plugins/{id}/updateActivity',
            [\App\Http\Controllers\Admin\Plugin\PluginController::class, 'updateActivity'])
            ->name('plugins.updateActivity');

        Route::put('/comments/{id}/updateActivity',
            [\App\Http\Controllers\Admin\Comment\CommentController::class, 'updateActivity'])
            ->name('comments.updateActivity');

        // Группа маршрутов для переключения Статей как главными и в левом и правом сайдбарах
        Route::put('/articles/{id}/updateLeft',
            [\App\Http\Controllers\Admin\Article\ArticleController::class, 'updateLeft'])
            ->name('articles.updateLeft');

        Route::put('/articles/{id}/updateMain',
            [\App\Http\Controllers\Admin\Article\ArticleController::class, 'updateMain'])
            ->name('articles.updateMain');

        Route::put('/articles/{id}/updateRight',
            [\App\Http\Controllers\Admin\Article\ArticleController::class, 'updateRight'])
            ->name('articles.updateRight');

        // Группа маршрутов для переключения Статей в левом и правом сайдбарах
        Route::put('/banners/{id}/updateLeft',
            [\App\Http\Controllers\Admin\Banner\BannerController::class, 'updateLeft'])
            ->name('banners.updateLeft');

        Route::put('/banners/{id}/updateRight',
            [\App\Http\Controllers\Admin\Banner\BannerController::class, 'updateRight'])
            ->name('banners.updateRight');

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

        Route::put('/banners/{banner}/updateSort',
            [\App\Http\Controllers\Admin\Banner\BannerController::class, 'updateSort'])
            ->name('banners.updateSort');

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

        Route::delete('/admin/tags/bulk-delete',
            [\App\Http\Controllers\Admin\Tag\TagController::class, 'bulkDestroy'])
            ->name('tags.bulkDestroy');

        Route::delete('/admin/banners/bulk-delete',
            [\App\Http\Controllers\Admin\Banner\BannerController::class, 'bulkDestroy'])
            ->name('banners.bulkDestroy');

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
