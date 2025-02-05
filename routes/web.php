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

Route::get('/', function () {
    // Получаем настройку из базы данных
    $template = Setting::where('option', 'siteLayout')->value('value');

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'template' => ucfirst($template) // Передаем выбранный layout в компонент
    ]);
});

// Маршруты страниц шаблона Default
// Маршрут О Нас
Route::get('/abouts', [\App\Http\Controllers\Public\Default\AboutController::class, 'index'])->name('about');

// Маршруты страниц Блога
Route::get('/blog', [\App\Http\Controllers\Public\Default\BlogController::class, 'index'])
    ->name('blog');
Route::get('/blog/{url}', [\App\Http\Controllers\Public\Default\BlogController::class, 'show'])
    ->name('blog.show');
Route::get('/blog/rubric/{url}', [\App\Http\Controllers\Public\Default\BlogController::class, 'showRubric'])
    ->name('blog.rubric');

// Маршруты страницы Контакты
Route::get('/contacts', [\App\Http\Controllers\Public\Default\ContactController::class, 'index'])
    ->name('contacts');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {
    Route::get('/dashboard', function () {return Inertia::render('Dashboard');})
        ->name('dashboard');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {
    Route::get('/admin', function () {return Inertia::render('Admin');})
        ->name('admin');
});

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
        Route::resource('/articles', \App\Http\Controllers\Admin\Article\ArticleController::class);
        Route::resource('/tutorials', \App\Http\Controllers\Admin\Tutorial\TutorialController::class);
        Route::resource('/guides', \App\Http\Controllers\Admin\Guide\GuideController::class);
        Route::resource('/charts', \App\Http\Controllers\Admin\Chart\ChartController::class);
        Route::resource('/reports', \App\Http\Controllers\Admin\Report\ReportController::class)
            ->only(['index']);

        // маршрут обновления структуры аккордеона категорий
        Route::post('/categories/update-structure',
            [\App\Http\Controllers\Admin\Category\CategoryController::class, 'updateCategoryStructure'])
            ->name('categories.update-structure');

        // Основные CRUD операции для комментариев
        Route::resource('/comments', \App\Http\Controllers\Admin\Comment\CommentController::class);

        Route::resource('/abouts', \App\Http\Controllers\Admin\About\SectionController::class);
        Route::resource('/contacts', \App\Http\Controllers\Admin\Contact\ContactController::class);

        Route::resource('/components', \App\Http\Controllers\Admin\Component\ComponentController::class);
        Route::post('/components/save', [\App\Http\Controllers\Admin\Component\ComponentController::class, 'save'])->name('components.save');

        Route::resource('/builders', \App\Http\Controllers\Admin\Builder\BuilderController::class);
        Route::post('/builders/save', [\App\Http\Controllers\Admin\Builder\BuilderController::class, 'save'])->name('builders.save');

        Route::resource('/diagrams', \App\Http\Controllers\Admin\Diagram\DiagramController::class);

        Route::resource('/pages', \App\Http\Controllers\Admin\Page\PageController::class);

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

        Route::delete('/rubrics/{rubric}/articles/{article}',
            \App\Http\Controllers\Admin\Invokable\RemoveArticleFromRubricController::class)
            ->name('rubrics.articles.destroy');

        Route::delete('/tutorials/{tutorial}/guides/{guide}',
            \App\Http\Controllers\Admin\Invokable\RemoveGuideFromTutorialController::class)
            ->name('tutorials.guides.destroy');

        // Группа маршрутов для клонирования сущности
        Route::post('/rubrics/clone/{id}',
            [\App\Http\Controllers\Admin\Rubric\RubricController::class, 'clone'])
            ->name('rubrics.clone');

        Route::post('/articles/clone/{id}',
            [\App\Http\Controllers\Admin\Article\ArticleController::class, 'clone'])
            ->name('articles.clone');

        Route::post('/tutorials/clone/{id}',
            [\App\Http\Controllers\Admin\Tutorial\TutorialController::class, 'clone'])
            ->name('tutorials.clone');

        Route::post('/guides/clone/{id}',
            [\App\Http\Controllers\Admin\Guide\GuideController::class, 'clone'])
            ->name('guides.clone');

        Route::post('/abouts/clone/{id}',
            [\App\Http\Controllers\Admin\About\SectionController::class, 'clone'])
            ->name('abouts.clone');

        Route::post('/pages/clone/{id}',
            [\App\Http\Controllers\Admin\Page\PageController::class, 'clone'])
            ->name('pages.clone');

        // Группа маршрутов для переключения активности
        Route::put('/rubrics/{id}/updateActivity',
            [\App\Http\Controllers\Admin\Rubric\RubricController::class, 'updateActivity'])
            ->name('rubrics.updateActivity');

        Route::put('/articles/{id}/updateActivity',
            [\App\Http\Controllers\Admin\Article\ArticleController::class, 'updateActivity'])
            ->name('articles.updateActivity');

        Route::put('/tutorials/{id}/updateActivity',
            [\App\Http\Controllers\Admin\Tutorial\TutorialController::class, 'updateActivity'])
            ->name('tutorials.updateActivity');

        Route::put('/guides/{id}/updateActivity',
            [\App\Http\Controllers\Admin\Guide\GuideController::class, 'updateActivity'])
            ->name('guides.updateActivity');

        Route::put('/abouts/{id}/updateActivity',
            [\App\Http\Controllers\Admin\About\SectionController::class, 'updateActivity'])
            ->name('abouts.updateActivity');

        Route::put('/settings/{id}/updateActivity',
            [\App\Http\Controllers\Admin\Parameter\ParameterController::class, 'updateActivity'])
            ->name('settings.updateActivity');

        Route::put('/pages/{id}/updateActivity',
            [\App\Http\Controllers\Admin\Page\PageController::class, 'updateActivity'])
            ->name('pages.updateActivity');

        Route::put('/plugins/{id}/updateActivity',
            [\App\Http\Controllers\Admin\Plugin\PluginController::class, 'updateActivity'])
            ->name('plugins.updateActivity');

        Route::put('/comments/{id}/updateActivity',
            [\App\Http\Controllers\Admin\Comment\CommentController::class, 'updateActivity'])
            ->name('comments.updateActivity');

        // маршрут для переключения показа страниц в меню
        Route::put('/pages/{id}/printInMenu',
            [\App\Http\Controllers\Admin\Page\PageController::class, 'printInMenu'])
            ->name('pages.printInMenu');

        // Группа маршрутов для обновления сортировки
        Route::put('/rubrics/{rubric}/updateSort',
            [\App\Http\Controllers\Admin\Rubric\RubricController::class, 'updateSort'])
            ->name('rubrics.updateSort');

        Route::put('/articles/{article}/updateSort',
            [\App\Http\Controllers\Admin\Article\ArticleController::class, 'updateSort'])
            ->name('articles.updateSort');

        Route::put('/tutorials/{tutorial}/updateSort',
            [\App\Http\Controllers\Admin\Tutorial\TutorialController::class, 'updateSort'])
            ->name('tutorials.updateSort');

        Route::put('/guides/{guide}/updateSort',
            [\App\Http\Controllers\Admin\Guide\GuideController::class, 'updateSort'])
            ->name('guides.updateSort');

        Route::put('/abouts/{section}/updateSort',
            [\App\Http\Controllers\Admin\About\SectionController::class, 'updateSort'])
            ->name('abouts.updateSort');

        Route::put('/pages/{page}/updateSort',
            [\App\Http\Controllers\Admin\Page\PageController::class, 'updateSort'])
            ->name('pages.updateSort');

        // Группа маршрутов для массового удаления сущностей
        Route::delete('/admin/articles/bulk-delete',
            [\App\Http\Controllers\Admin\Article\ArticleController::class, 'bulkDestroy'])
            ->name('articles.bulkDestroy');

        Route::delete('/admin/rubrics/bulk-delete',
            [\App\Http\Controllers\Admin\Rubric\RubricController::class, 'bulkDestroy'])
            ->name('rubrics.bulkDestroy');

        Route::delete('/admin/guides/bulk-delete',
            [\App\Http\Controllers\Admin\Guide\GuideController::class, 'bulkDestroy'])
            ->name('guides.bulkDestroy');

        Route::delete('/admin/tutorials/bulk-delete',
            [\App\Http\Controllers\Admin\Tutorial\TutorialController::class, 'bulkDestroy'])
            ->name('tutorials.bulkDestroy');

        Route::delete('/admin/abouts/bulk-delete',
            [\App\Http\Controllers\Admin\About\SectionController::class, 'bulkDestroy'])
            ->name('abouts.bulkDestroy');

        Route::delete('/admin/pages/bulk-delete',
            [\App\Http\Controllers\Admin\Page\PageController::class, 'bulkDestroy'])
            ->name('pages.bulkDestroy');

        Route::delete('/comments/bulk-delete',
            [\App\Http\Controllers\Admin\Comment\CommentController::class, 'bulkDestroy'])
            ->name('comments.bulkDestroy');

        // Маршрут для модерации комментария
        Route::put('/comments/{id}/approve',
            [\App\Http\Controllers\Admin\Comment\CommentController::class, 'approve'])
            ->name('comments.approve');

    });

Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
});

Route::get('/redis-test', function () {
    Cache::put('key', 'value', 10);
    return Cache::get('key');
});

// Маршрут для страниц с пропсами
Route::get('/{slug}', [\App\Http\Controllers\Admin\Page\PageController::class, 'show'])
    ->name('page.show')->defaults('canLogin', Route::has('login'))
    ->defaults('canRegister', Route::has('register'));
