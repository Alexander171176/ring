<?php

use App\Http\Controllers\Admin\Log\LogController;
use App\Http\Controllers\Admin\Category\CategoryController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

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
use App\Http\Controllers\Admin\System\SystemController;

// Добавил импорт для clearCache
use App\Http\Controllers\Admin\Tag\TagController;
use App\Http\Controllers\Admin\User\UserController;
use App\Http\Controllers\Admin\Video\VideoController;
use App\Http\Middleware\CheckDowntime;

// use App\Models\Admin\Setting\Setting; // Не используется напрямую здесь
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::group([
    'prefix' => LaravelLocalization::setLocale(),        // /ru или /en
    'middleware' => [
        'localeSessionRedirect',     // перенаправляет из / на /ru или /en
        'localizationRedirect',      // сохраняет префикс в URL при смене языка
        'localeViewPath',            // подтягивает view из ресурсов по языку
        'web',                       // (необязательно, т.к. web.php уже под web-middleware)
    ]
], function () {

    // --- Глобальные настройки и публичные маршруты ---
    Route::post('/admin/cache/clear', [SystemController::class, 'clearCache']) // Исправлен неймспейс
    ->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified']) // Защищаем маршрут кэша
    ->name('cache.clear');

    // Используем замыкание для получения настроек один раз
    $siteLayout = config('site_settings.siteLayout', 'Default');

    // Добавить маршрут для страницы технических работ
    Route::get('/maintenance', function () {
        return Inertia::render('Maintenance');
    })->name('maintenance');

    // Обработка 404 и режима обслуживания вынесена до основной группы
    Route::fallback(function (Request $request) {
        if (config('site_settings.downtimeSite', 'false') === 'true'
            && !$request->is('admin/*')
            && !$request->is(app()->getLocale() . '/admin*')) {
            return Inertia::render('Maintenance');
        }
        return Inertia::render('NotFound')->toResponse($request)->setStatusCode(404);
    });

    // Публичная часть сайта
    Route::middleware([CheckDowntime::class])->group(function () use ($siteLayout) {

        Route::get('/', fn() => Inertia::render('Public/' . $siteLayout . '/Index'))->name('home'); // Добавим имя

        $publicRubricController = "App\\Http\\Controllers\\Public\\{$siteLayout}\\RubricController";
        Route::get('/rubrics/{url}', [$publicRubricController, 'show'])->where('url', '.*')->name('public.rubrics.show');

        $publicArticleController = "App\\Http\\Controllers\\Public\\{$siteLayout}\\ArticleController";
        Route::get('/articles/{url}', [$publicArticleController, 'show'])->where('url', '.*')->name('public.articles.show');
        Route::post('/articles/{article}/like', [$publicArticleController, 'like'])->name('articles.like'); // Параметр {article} уже здесь

        $publicTagController = "App\\Http\\Controllers\\Public\\{$siteLayout}\\TagController";
        Route::get('/tags/{url}', [$publicTagController, 'show'])->where('url', '.*')->name('public.tags.show');

        $publicVideoController = "App\\Http\\Controllers\\Public\\{$siteLayout}\\VideoController";
        Route::get('/videos/{url}', [$publicVideoController, 'show'])->where('url', '.*')->name('public.videos.show');

        // TODO: Добавить другие публичные маршруты (поиск, контакты и т.д.)
    });

    // --- Маршруты аутентификации и профиля пользователя ---

    // Профиль Пользователя (стандартные маршруты Jetstream/Fortify обычно регистрируются пакетами)
    Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
        Route::get('/dashboard', function () { // Пользовательский дашборд
            return Inertia::render('Dashboard');
        })->name('dashboard');

        // Могут быть и другие маршруты профиля здесь...
    });


    // --- Маршруты Панели Администратора ---
    Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])
        ->prefix('admin')->name('admin.')
        ->group(function () {

            // Главная страница админки
            Route::get('/', function () {
                return Inertia::render('Admin');
            })->name('index');

            // для показа, очистки логов и скачивания логов
            Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
            Route::delete('/logs', [LogController::class, 'clear'])->name('logs.clear');
            Route::get('/logs/download', [LogController::class, 'download'])->name('logs.download');

            // --- Настройки отображения в админке ---
            Route::prefix('settings')->name('settings.')->group(function () {

                // Количество на странице
                Route::put('/update-count/categories', [SettingController::class, 'updateAdminCountCategories'])->name('updateAdminCountCategories');
                Route::put('/update-count/rubrics', [SettingController::class, 'updateAdminCountRubrics'])->name('updateAdminCountRubrics');
                Route::put('/update-count/sections', [SettingController::class, 'updateAdminCountSections'])->name('updateAdminCountSections');
                Route::put('/update-count/articles', [SettingController::class, 'updateAdminCountArticles'])->name('updateAdminCountArticles');
                Route::put('/update-count/tags', [SettingController::class, 'updateAdminCountTags'])->name('updateAdminCountTags');
                // Дублирование имени маршрута для comments, исправим
                Route::put('/update-count/comments', [SettingController::class, 'updateAdminCountComments'])->name('updateAdminCountComments'); // Исправлено имя
                Route::put('/update-count/banners', [SettingController::class, 'updateAdminCountBanners'])->name('updateAdminCountBanners');
                Route::put('/update-count/videos', [SettingController::class, 'updateAdminCountVideos'])->name('updateAdminCountVideos');
                Route::put('/update-count/users', [SettingController::class, 'updateAdminCountUsers'])->name('updateAdminCountUsers');
                Route::put('/update-count/roles', [SettingController::class, 'updateAdminCountRoles'])->name('updateAdminCountRoles');
                Route::put('/update-count/permissions', [SettingController::class, 'updateAdminCountPermissions'])->name('updateAdminCountPermissions');
                Route::put('/update-count/plugins', [SettingController::class, 'updateAdminCountPlugins'])->name('updateAdminCountPlugins');
                Route::put('/update-count/settings', [SettingController::class, 'updateAdminCountSettings'])->name('updateAdminCountSettings');

                // Тип сортировки
                Route::put('/update-sort/categories', [SettingController::class, 'updateAdminSortCategories'])->name('updateAdminSortCategories');
                Route::put('/update-sort/rubrics', [SettingController::class, 'updateAdminSortRubrics'])->name('updateAdminSortRubrics');
                Route::put('/update-sort/sections', [SettingController::class, 'updateAdminSortSections'])->name('updateAdminSortSections');
                Route::put('/update-sort/articles', [SettingController::class, 'updateAdminSortArticles'])->name('updateAdminSortArticles');
                Route::put('/update-sort/tags', [SettingController::class, 'updateAdminSortTags'])->name('updateAdminSortTags');
                Route::put('/update-sort/comments', [SettingController::class, 'updateAdminSortComments'])->name('updateAdminSortComments');
                Route::put('/update-sort/banners', [SettingController::class, 'updateAdminSortBanners'])->name('updateAdminSortBanners');
                Route::put('/update-sort/videos', [SettingController::class, 'updateAdminSortVideos'])->name('updateAdminSortVideos');
                Route::put('/update-sort/users', [SettingController::class, 'updateAdminSortUsers'])->name('updateAdminSortUsers');
                Route::put('/update-sort/roles', [SettingController::class, 'updateAdminSortRoles'])->name('updateAdminSortRoles');
                Route::put('/update-sort/permissions', [SettingController::class, 'updateAdminSortPermissions'])->name('updateAdminSortPermissions');
                Route::put('/update-sort/plugins', [SettingController::class, 'updateAdminSortPlugins'])->name('updateAdminSortPlugins');
                Route::put('/update-sort/settings', [SettingController::class, 'updateAdminSortSettings'])->name('updateAdminSortSettings');
            });

            // --- Основные CRUD Ресурсы ---
            Route::resource('/settings', SettingController::class);
            Route::resource('/parameters', ParameterController::class);
            Route::resource('/users', UserController::class);
            Route::resource('/roles', RoleController::class);
            Route::resource('/permissions', PermissionController::class);
            Route::resource('/categories', CategoryController::class);
            Route::resource('/rubrics', RubricController::class);
            Route::resource('/sections', SectionController::class);
            Route::resource('/articles', ArticleController::class);
            Route::resource('/tags', TagController::class);
            Route::resource('/banners', BannerController::class);
            Route::resource('/videos', VideoController::class);
            Route::resource('/charts', ChartController::class)->except(['show']);
            Route::resource('/reports', ReportController::class)->only(['index']);
            Route::resource('/comments', CommentController::class)->except(['create', 'store', 'show']); // Админ обычно не создает комменты с нуля
            Route::resource('/components', ComponentController::class);
            Route::post('/components/save', [ComponentController::class, 'save'])->name('components.save'); // Выносим отдельно, т.к. не ресурсный
            Route::resource('/diagrams', DiagramController::class);
            Route::resource('/plugins', PluginController::class);
            Route::get('/reports/download', [ReportController::class, 'download'])->name('reports.download'); // Выносим отдельно

            // --- Маршруты удаления связей ManyToMany ---
            Route::delete('/roles/{role}/permissions/{permission}', RemovePermissionFromRoleController::class)->name('roles.permissions.destroy');
            Route::delete('/users/{user}/roles/{role}', RemoveRoleFromUserController::class)->name('users.roles.destroy');
            Route::delete('/users/{user}/permissions/{permission}', RemovePermissionFromUserController::class)->name('users.permissions.destroy');
            Route::delete('/rubrics/{rubric}/sections/{section}', RemoveRubricFromSectionController::class)->name('rubrics.sections.destroy');
            Route::delete('/sections/{section}/articles/{article}', RemoveArticleFromSectionController::class)->name('sections.articles.destroy');
            Route::delete('/sections/{section}/banners/{banner}', RemoveBannerFromSectionController::class)->name('sections.banners.destroy');
            Route::delete('/articles/{article}/tags/{tag}', RemoveArticleFromTagController::class)->name('articles.tags.destroy');
            Route::delete('/sections/{section}/videos/{video}', RemoveSectionFromVideoController::class)->name('sections.videos.destroy');
            Route::delete('/articles/{article}/videos/{video}', RemoveArticleFromVideoController::class)->name('articles.videos.destroy');

            // --- Маршруты для дополнительных действий ---
            Route::prefix('actions')->name('actions.')->group(function () { // Группируем доп. действия

                // Обновление только value настройки
                Route::put('/settings/{setting}/value', [SettingController::class, 'updateValue'])->name('settings.updateValue');

                // Клонирование (Используем имена моделей для параметров RMB)
                Route::post('/rubrics/{rubric}/clone', [RubricController::class, 'clone'])->name('rubrics.clone');
                Route::post('/sections/{section}/clone', [SectionController::class, 'clone'])->name('sections.clone');
                Route::post('/articles/{article}/clone', [ArticleController::class, 'clone'])->name('articles.clone');

                // Переключение активности (Используем имена моделей для параметров RMB)
                Route::put('/categories/{category}/activity', [CategoryController::class, 'updateActivity'])->name('categories.updateActivity');
                Route::put('/rubrics/{rubric}/activity', [RubricController::class, 'updateActivity'])->name('rubrics.updateActivity');
                Route::put('/sections/{section}/activity', [SectionController::class, 'updateActivity'])->name('sections.updateActivity');
                Route::put('/articles/{article}/activity', [ArticleController::class, 'updateActivity'])->name('articles.updateActivity');
                Route::put('/tags/{tag}/activity', [TagController::class, 'updateActivity'])->name('tags.updateActivity');
                Route::put('/banners/{banner}/activity', [BannerController::class, 'updateActivity'])->name('banners.updateActivity');
                Route::put('/videos/{video}/activity', [VideoController::class, 'updateActivity'])->name('videos.updateActivity');
                Route::put('/settings/{setting}/activity', [ParameterController::class, 'updateActivity'])->name('settings.updateActivity');
                Route::put('/plugins/{plugin}/activity', [PluginController::class, 'updateActivity'])->name('plugins.updateActivity');
                Route::put('/comments/{comment}/activity', [CommentController::class, 'updateActivity'])->name('comments.updateActivity');

                // Переключение активности массово
                Route::put('/admin/actions/categories/bulk-activity', [CategoryController::class, 'bulkUpdateActivity'])
                    ->name('categories.bulkUpdateActivity');
                Route::put('/admin/actions/rubrics/bulk-activity', [RubricController::class, 'bulkUpdateActivity'])
                    ->name('rubrics.bulkUpdateActivity');
                Route::put('/admin/actions/sections/bulk-activity', [SectionController::class, 'bulkUpdateActivity'])
                    ->name('sections.bulkUpdateActivity');
                Route::put('/admin/actions/articles/bulk-activity', [ArticleController::class, 'bulkUpdateActivity'])
                    ->name('articles.bulkUpdateActivity');
                Route::put('/admin/actions/tags/bulk-activity', [TagController::class, 'bulkUpdateActivity'])
                    ->name('tags.bulkUpdateActivity');
                Route::put('/admin/actions/banners/bulk-activity', [BannerController::class, 'bulkUpdateActivity'])
                    ->name('banners.bulkUpdateActivity');
                Route::put('/admin/actions/videos/bulk-activity', [VideoController::class, 'bulkUpdateActivity'])
                    ->name('videos.bulkUpdateActivity');
                Route::put('/admin/actions/plugins/bulk-activity', [PluginController::class, 'bulkUpdateActivity'])
                    ->name('plugins.bulkUpdateActivity');
                Route::put('/admin/actions/settings/bulk-activity', [ParameterController::class, 'bulkUpdateActivity'])
                    ->name('settings.bulkUpdateActivity');

                // Переключение Left/Main/Right (Используем имена моделей для параметров RMB)
                Route::put('/articles/{article}/left', [ArticleController::class, 'updateLeft'])->name('articles.updateLeft');
                Route::put('/articles/{article}/main', [ArticleController::class, 'updateMain'])->name('articles.updateMain');
                Route::put('/articles/{article}/right', [ArticleController::class, 'updateRight'])->name('articles.updateRight');
                Route::put('/banners/{banner}/left', [BannerController::class, 'updateLeft'])->name('banners.updateLeft');
                Route::put('/banners/{banner}/right', [BannerController::class, 'updateRight'])->name('banners.updateRight');
                Route::put('/videos/{video}/left', [VideoController::class, 'updateLeft'])->name('videos.updateLeft');
                Route::put('/videos/{video}/main', [VideoController::class, 'updateMain'])->name('videos.updateMain');
                Route::put('/videos/{video}/right', [VideoController::class, 'updateRight'])->name('videos.updateRight');

                // Переключение активности в левой колонке массово
                Route::put('/admin/actions/articles/bulk-left', [ArticleController::class, 'bulkUpdateLeft'])
                    ->name('articles.bulkUpdateLeft');
                Route::put('/admin/actions/banners/bulk-left', [BannerController::class, 'bulkUpdateLeft'])
                    ->name('banners.bulkUpdateLeft');
                Route::put('/admin/actions/videos/bulk-left', [VideoController::class, 'bulkUpdateLeft'])
                    ->name('videos.bulkUpdateLeft');

                // Переключение активности в главном массово
                Route::put('/admin/actions/articles/bulk-main', [ArticleController::class, 'bulkUpdateMain'])
                    ->name('articles.bulkUpdateMain');
                Route::put('/admin/actions/videos/bulk-main', [VideoController::class, 'bulkUpdateMain'])
                    ->name('videos.bulkUpdateMain');

                // Переключение активности в правой колонке массово
                Route::put('/admin/actions/articles/bulk-right', [ArticleController::class, 'bulkUpdateRight'])
                    ->name('articles.bulkUpdateRight');
                Route::put('/admin/actions/banners/bulk-right', [BannerController::class, 'bulkUpdateRight'])
                    ->name('banners.bulkUpdateRight');
                Route::put('/admin/actions/videos/bulk-right', [VideoController::class, 'bulkUpdateRight'])
                    ->name('videos.bulkUpdateRight');

                // Обновление сортировки для Drag and Drop
                Route::put('/categories/update-sort-bulk', [CategoryController::class, 'updateSortBulk'])->name('categories.updateSortBulk');
                Route::put('/rubrics/update-sort-bulk', [RubricController::class, 'updateSortBulk'])->name('rubrics.updateSortBulk');
                Route::put('/sections/update-sort-bulk', [SectionController::class, 'updateSortBulk'])->name('sections.updateSortBulk');
                Route::put('/articles/update-sort-bulk', [ArticleController::class, 'updateSortBulk'])->name('articles.updateSortBulk');
                Route::put('/tags/update-sort-bulk', [TagController::class, 'updateSortBulk'])->name('tags.updateSortBulk');
                Route::put('/banners/update-sort-bulk', [BannerController::class, 'updateSortBulk'])->name('banners.updateSortBulk');
                Route::put('/videos/update-sort-bulk', [VideoController::class, 'updateSortBulk'])->name('videos.updateSortBulk');
                Route::put('/plugins/update-sort-bulk', [PluginController::class, 'updateSortBulk'])->name('plugins.updateSortBulk');
                Route::put('/settings/update-sort-bulk', [ParameterController::class, 'updateSortBulk'])->name('settings.updateSortBulk');

                // Обновление сортировки (Имена параметров уже были правильные)
                Route::put('/categories/{category}/sort', [CategoryController::class, 'updateSort'])->name('categories.updateSort');
                Route::put('/rubrics/{rubric}/sort', [RubricController::class, 'updateSort'])->name('rubrics.updateSort');
                Route::put('/sections/{section}/sort', [SectionController::class, 'updateSort'])->name('sections.updateSort');
                Route::put('/tags/{tag}/sort', [TagController::class, 'updateSort'])->name('tags.updateSort');
                Route::put('/videos/{video}/sort', [VideoController::class, 'updateSort'])->name('videos.updateSort');
                Route::put('/banners/{banner}/sort', [BannerController::class, 'updateSort'])->name('banners.updateSort');
                Route::put('/videos/{video}/sort', [VideoController::class, 'updateSort'])->name('videos.updateSort');
                Route::put('/plugins/{plugin}/sort', [PluginController::class, 'updateSort'])->name('plugins.updateSort');
                Route::put('/parameters/{parameter}/sort', [ParameterController::class, 'updateSort'])->name('parameters.updateSort');

                // Одобрение комментария (Используем имя модели для параметра RMB)
                Route::put('/comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve');

                // Массовое удаление
                Route::delete('/rubrics/bulk-delete', [RubricController::class, 'bulkDestroy'])->name('rubrics.bulkDestroy');
                Route::delete('/sections/bulk-delete', [SectionController::class, 'bulkDestroy'])->name('sections.bulkDestroy');
                Route::delete('/articles/bulk-delete', [ArticleController::class, 'bulkDestroy'])->name('articles.bulkDestroy');
                Route::delete('/tags/bulk-delete', [TagController::class, 'bulkDestroy'])->name('tags.bulkDestroy');
                Route::delete('/banners/bulk-delete', [BannerController::class, 'bulkDestroy'])->name('banners.bulkDestroy');
                Route::delete('/videos/bulk-delete', [VideoController::class, 'bulkDestroy'])->name('videos.bulkDestroy');
                Route::delete('/comments/bulk-delete', [CommentController::class, 'bulkDestroy'])->name('comments.bulkDestroy');
            }); // Конец группы actions

        }); // Конец группы admin

    // --- Остальные маршруты (Filemanager, Redis test) ---
    Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
        \UniSharp\LaravelFilemanager\Lfm::routes();
    });

    Route::get('/redis-test', function () {
        try {
            Cache::put('redis_test_key', 'redis_test_value', 10);
            $value = Cache::get('redis_test_key');
            if ($value === 'redis_test_value') {
                return 'Redis connection successful!';
            } else {
                return 'Could not retrieve value from Redis.';
            }
        } catch (Throwable $e) {
            return 'Redis connection failed: ' . $e->getMessage();
        }
    });

});
