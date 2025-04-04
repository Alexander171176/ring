<?php

use App\Http\Controllers\Admin\Article\ArticleController;
use App\Http\Controllers\Admin\Banner\BannerController;
use App\Http\Controllers\Admin\Chart\ChartController;
use App\Http\Controllers\Admin\Comment\CommentController;
use App\Http\Controllers\Admin\Component\ComponentController;
use App\Http\Controllers\Admin\Diagram\DiagramController;
use App\Http\Controllers\Admin\Invokable\RemoveArticleFromSectionController;
use App\Http\Controllers\Admin\Invokable\RemoveArticleFromTagController;
use App\Http\Controllers\Admin\Invokable\RemoveArticleFromVideoController;
use App\Http\Controllers\Admin\Invokable\RemoveBannerFromSectionController;
use App\Http\Controllers\Admin\Invokable\RemovePermissionFromRoleController;
use App\Http\Controllers\Admin\Invokable\RemovePermissionFromUserController;
use App\Http\Controllers\Admin\Invokable\RemoveRoleFromUserController;
use App\Http\Controllers\Admin\Invokable\RemoveRubricFromSectionController;
use App\Http\Controllers\Admin\Invokable\RemoveSectionFromVideoController;
use App\Http\Controllers\Admin\Parameter\ParameterController;
use App\Http\Controllers\Admin\Permission\PermissionController;
use App\Http\Controllers\Admin\Plugin\PluginController;
use App\Http\Controllers\Admin\Report\ReportController;
use App\Http\Controllers\Admin\Role\RoleController;
use App\Http\Controllers\Admin\Rubric\RubricController;
use App\Http\Controllers\Admin\Section\SectionController;
use App\Http\Controllers\Admin\Setting\SettingController;
use App\Http\Controllers\Admin\Tag\TagController;
use App\Http\Controllers\Admin\User\UserController;
use App\Http\Controllers\Admin\Video\VideoController;
use App\Http\Middleware\CheckDowntime;
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
Route::get('/settings/locale', [SettingController::class, 'getLocaleSetting']);
Route::post('/settings/locale', [SettingController::class, 'updateLocaleSetting']);

// Получаем значения параметров системы, по умолчанию
$localePrefix = config('site_settings.locale', 'ru');
$siteLayout = config('site_settings.siteLayout', 'Default');

// Маршрут очищает весь кэш
Route::post('/admin/cache/clear', [App\Http\Controllers\Admin\System\SystemController::class, 'clearCache'])
    ->name('cache.clear');

Route::fallback(function (Request $request) {
    if (config('site_settings.downtimeSite', 'false') === 'true') {
        return Inertia::render('Maintenance');
    }
    // Возвращаем компонент NotFound.vue с HTTP-статусом 404
    return Inertia::render('NotFound')->toResponse($request)->setStatusCode(404);
});

Route::middleware([CheckDowntime::class])->group(function () use ($siteLayout) {

// Главная страница
    Route::get('/', fn() => Inertia::render('Public/' . $siteLayout . '/Index'));


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

// Отображение конкретного видео
$publicVideoController = "App\\Http\\Controllers\\Public\\{$siteLayout}\\VideoController";
Route::get('/videos/{url}', [$publicVideoController, 'show'])
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
        Route::put('/settings/update-admin-count-rubrics',
            [SettingController::class, 'updateAdminCountRubrics'])
            ->name('settings.updateAdminCountRubrics');
        Route::put('/settings/update-admin-count-sections',
            [SettingController::class, 'updateAdminCountSections'])
            ->name('settings.updateAdminCountSections');
        Route::put('/settings/update-admin-count-articles',
            [SettingController::class, 'updateAdminCountArticles'])
            ->name('settings.updateAdminCountArticles');
        Route::put('/settings/update-admin-count-tags',
            [SettingController::class, 'updateAdminCountTags'])
            ->name('settings.updateAdminCountTags');
        Route::put('/settings/update-admin-count-tags',
            [SettingController::class, 'updateAdminCountComments'])
            ->name('settings.updateAdminCountComments');
        Route::put('/settings/update-admin-count-banners',
            [SettingController::class, 'updateAdminCountBanners'])
            ->name('settings.updateAdminCountBanners');
        Route::put('/settings/update-admin-count-videos',
            [SettingController::class, 'updateAdminCountVideos'])
            ->name('settings.updateAdminCountVideos');
        Route::put('/settings/update-admin-count-users',
            [SettingController::class, 'updateAdminCountUsers'])
            ->name('settings.updateAdminCountUsers');
        Route::put('/settings/update-admin-count-roles',
            [SettingController::class, 'updateAdminCountRoles'])
            ->name('settings.updateAdminCountRoles');
        Route::put('/settings/update-admin-count-permissions',
            [SettingController::class, 'updateAdminCountPermissions'])
            ->name('settings.updateAdminCountPermissions');
        Route::put('/settings/update-admin-count-plugins',
            [SettingController::class, 'updateAdminCountPlugins'])
            ->name('settings.updateAdminCountPlugins');
        Route::put('/settings/update-admin-count-settings',
            [SettingController::class, 'updateAdminCountSettings'])
            ->name('settings.updateAdminCountSettings');

        // тип сортировки сущностей на странице Index
        Route::put('/settings/update-admin-sort-rubrics',
            [SettingController::class, 'updateAdminSortRubrics'])
            ->name('settings.updateAdminSortRubrics');
        Route::put('/settings/update-admin-sort-sections',
            [SettingController::class, 'updateAdminSortSections'])
            ->name('settings.updateAdminSortSections');
        Route::put('/settings/update-admin-sort-articles',
            [SettingController::class, 'updateAdminSortArticles'])
            ->name('settings.updateAdminSortArticles');
        Route::put('/settings/update-admin-sort-tags',
            [SettingController::class, 'updateAdminSortTags'])
            ->name('settings.updateAdminSortTags');
        Route::put('/settings/update-admin-sort-comments',
            [SettingController::class, 'updateAdminSortComments'])
            ->name('settings.updateAdminSortComments');
        Route::put('/settings/update-admin-sort-banners',
            [SettingController::class, 'updateAdminSortBanners'])
            ->name('settings.updateAdminSortBanners');
        Route::put('/settings/update-admin-sort-videos',
            [SettingController::class, 'updateAdminSortVideos'])
            ->name('settings.updateAdminSortVideos');
        Route::put('/settings/update-admin-sort-users',
            [SettingController::class, 'updateAdminSortUsers'])
            ->name('settings.updateAdminSortUsers');
        Route::put('/settings/update-admin-sort-roles',
            [SettingController::class, 'updateAdminSortRoles'])
            ->name('settings.updateAdminSortRoles');
        Route::put('/settings/update-admin-sort-permissions',
            [SettingController::class, 'updateAdminSortPermissions'])
            ->name('settings.updateAdminSortPermissions');
        Route::put('/settings/update-admin-sort-plugins',
            [SettingController::class, 'updateAdminSortPlugins'])
            ->name('settings.updateAdminSortPlugins');
        Route::put('/settings/update-admin-sort-settings',
            [SettingController::class, 'updateAdminSortSettings'])
            ->name('settings.updateAdminSortSettings');

        // Ресурсный маршрут для настроек – ограничиваем параметр id только числами без конфликтов
        Route::resource('/settings', SettingController::class)
            ->where(['setting' => '[0-9]+']);

        Route::put('/admin/settings/{id}', [SettingController::class, 'update'])
            ->name('settings.update');

        Route::resource('/parameters', ParameterController::class);

        Route::resource('/users', UserController::class);
        Route::resource('/roles', RoleController::class);
        Route::resource('/permissions', PermissionController::class);
        Route::resource('/rubrics', RubricController::class);
        Route::resource('/sections', SectionController::class);
        Route::resource('/articles', ArticleController::class);
        Route::resource('/tags', TagController::class);
        Route::resource('/banners', BannerController::class);
        Route::resource('/videos', VideoController::class);
        Route::resource('/charts', ChartController::class);
        Route::resource('/reports', ReportController::class)
            ->only(['index']);

        // Основные CRUD операции для комментариев
        Route::resource('/comments', CommentController::class);
        // Основные CRUD операции для компонентов
        Route::resource('/components', ComponentController::class);
        Route::post('/components/save', [ComponentController::class, 'save'])->name('components.save');
        // Основные CRUD операции для диаграмм
        Route::resource('/diagrams', DiagramController::class);
        // Основные CRUD операции для модулей
        Route::resource('/plugins', PluginController::class);
        // Маршрут для загрузки отчётов
        Route::get('/reports/download', [ReportController::class, 'download']);

        // Группа маршрутов для удаления опций у которых связь многие ко многим
        Route::delete('/roles/{role}/permissions/{permission}',
            RemovePermissionFromRoleController::class)
            ->name('roles.permissions.destroy');

        Route::delete('/users/{user}/roles/{role}',
            RemoveRoleFromUserController::class)
            ->name('users.roles.destroy');

        Route::delete('/users/{user}/permissions/{permission}',
            RemovePermissionFromUserController::class)
            ->name('users.permissions.destroy');

        Route::delete('/rubrics/{rubric}/sections/{section}',
            RemoveRubricFromSectionController::class)
            ->name('rubrics.sections.destroy');

        Route::delete('/sections/{section}/articles/{article}',
            RemoveArticleFromSectionController::class)
            ->name('sections.articles.destroy');

        Route::delete('/sections/{section}/banners/{banner}',
            RemoveBannerFromSectionController::class)
            ->name('sections.banners.destroy');

        Route::delete('/articles/{article}/tags/{tag}',
            RemoveArticleFromTagController::class)
            ->name('articles.tags.destroy');

        Route::delete('/sections/{section}/videos/{video}',
            RemoveSectionFromVideoController::class)
            ->name('sections.videos.destroy');

        Route::delete('/articles/{article}/videos/{video}',
            RemoveArticleFromVideoController::class)
            ->name('articles.videos.destroy');

        // Группа маршрутов для клонирования сущности
        Route::post('/rubrics/clone/{id}',
            [RubricController::class, 'clone'])
            ->name('rubrics.clone');

        Route::post('/sections/clone/{id}',
            [SectionController::class, 'clone'])
            ->name('sections.clone');

        Route::post('/articles/clone/{id}',
            [ArticleController::class, 'clone'])
            ->name('articles.clone');

        // Группа маршрутов для переключения активности
        Route::put('/rubrics/{id}/updateActivity',
            [RubricController::class, 'updateActivity'])
            ->name('rubrics.updateActivity');

        Route::put('/sections/{id}/updateActivity',
            [SectionController::class, 'updateActivity'])
            ->name('sections.updateActivity');

        Route::put('/articles/{id}/updateActivity',
            [ArticleController::class, 'updateActivity'])
            ->name('articles.updateActivity');

        Route::put('/banners/{id}/updateActivity',
            [BannerController::class, 'updateActivity'])
            ->name('banners.updateActivity');

        Route::put('/videos/{id}/updateActivity',
            [VideoController::class, 'updateActivity'])
            ->name('videos.updateActivity');

        Route::put('/settings/{id}/updateActivity',
            [ParameterController::class, 'updateActivity'])
            ->name('settings.updateActivity');

        Route::put('/plugins/{id}/updateActivity',
            [PluginController::class, 'updateActivity'])
            ->name('plugins.updateActivity');

        Route::put('/comments/{id}/updateActivity',
            [CommentController::class, 'updateActivity'])
            ->name('comments.updateActivity');

        // Группа маршрутов для переключения Статей как главными и в левом и правом сайдбарах
        Route::put('/articles/{id}/updateLeft',
            [ArticleController::class, 'updateLeft'])
            ->name('articles.updateLeft');

        Route::put('/articles/{id}/updateMain',
            [ArticleController::class, 'updateMain'])
            ->name('articles.updateMain');

        Route::put('/articles/{id}/updateRight',
            [ArticleController::class, 'updateRight'])
            ->name('articles.updateRight');

        // Группа маршрутов для переключения Статей в левом и правом сайдбарах
        Route::put('/banners/{id}/updateLeft',
            [BannerController::class, 'updateLeft'])
            ->name('banners.updateLeft');

        Route::put('/banners/{id}/updateRight',
            [BannerController::class, 'updateRight'])
            ->name('banners.updateRight');

        // Группа маршрутов для переключения Видео в центре, левом и правом сайдбарах
        Route::put('/videos/{id}/updateLeft',
            [VideoController::class, 'updateLeft'])
            ->name('videos.updateLeft');

        Route::put('/videos/{id}/updateMain',
            [VideoController::class, 'updateMain'])
            ->name('videos.updateMain');

        Route::put('/videos/{id}/updateRight',
            [VideoController::class, 'updateRight'])
            ->name('videos.updateRight');

        // Группа маршрутов для обновления сортировки
        Route::put('/rubrics/{rubric}/updateSort',
            [RubricController::class, 'updateSort'])
            ->name('rubrics.updateSort');

        Route::put('/sections/{section}/updateSort',
            [SectionController::class, 'updateSort'])
            ->name('sections.updateSort');

        Route::put('/articles/{article}/updateSort',
            [ArticleController::class, 'updateSort'])
            ->name('articles.updateSort');

        Route::put('/banners/{banner}/updateSort',
            [BannerController::class, 'updateSort'])
            ->name('banners.updateSort');

        Route::put('/videos/{video}/updateSort',
            [VideoController::class, 'updateSort'])
            ->name('videos.updateSort');

        // Группа маршрутов для массового удаления сущностей
        Route::delete('/admin/rubrics/bulk-delete',
            [RubricController::class, 'bulkDestroy'])
            ->name('rubrics.bulkDestroy');

        Route::delete('/admin/sections/bulk-delete',
            [SectionController::class, 'bulkDestroy'])
            ->name('sections.bulkDestroy');

        Route::delete('/admin/articles/bulk-delete',
            [ArticleController::class, 'bulkDestroy'])
            ->name('articles.bulkDestroy');

        Route::delete('/admin/tags/bulk-delete',
            [TagController::class, 'bulkDestroy'])
            ->name('tags.bulkDestroy');

        Route::delete('/admin/banners/bulk-delete',
            [BannerController::class, 'bulkDestroy'])
            ->name('banners.bulkDestroy');

        Route::delete('/admin/videos/bulk-delete',
            [VideoController::class, 'bulkDestroy'])
            ->name('videos.bulkDestroy');

        Route::delete('/comments/bulk-delete',
            [CommentController::class, 'bulkDestroy'])
            ->name('comments.bulkDestroy');

        // Маршрут для модерации комментария
        Route::put('/comments/{id}/approve',
            [CommentController::class, 'approve'])
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
