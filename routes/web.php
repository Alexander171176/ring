<?php

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationPromptController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;
use Laravel\Jetstream\Http\Controllers;
use App\Http\Controllers\Admin\Athlete\AthleteController;
use App\Http\Controllers\Admin\Log\LogController;
use App\Http\Controllers\Admin\Category\CategoryController;
use App\Http\Controllers\Admin\System\ComposerController;
use App\Http\Controllers\Admin\System\EnvController;
use App\Http\Controllers\Admin\System\PackageController;
use App\Http\Controllers\Admin\System\PhpInfoController;
use App\Http\Controllers\Admin\Tournament\TournamentController;
use App\Models\Admin\Tournament\Tournament;
use Laravel\Jetstream\Http\Controllers\CurrentTeamController;
use Laravel\Jetstream\Http\Controllers\Inertia\ApiTokenController;
use Laravel\Jetstream\Http\Controllers\Inertia\CurrentUserController;
use Laravel\Jetstream\Http\Controllers\Inertia\OtherBrowserSessionsController;
use Laravel\Jetstream\Http\Controllers\Inertia\PrivacyPolicyController;
use Laravel\Jetstream\Http\Controllers\Inertia\ProfilePhotoController;
use Laravel\Jetstream\Http\Controllers\Inertia\TeamController;
use Laravel\Jetstream\Http\Controllers\Inertia\TeamMemberController;
use Laravel\Jetstream\Http\Controllers\Inertia\TermsOfServiceController;
use Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController;
use Laravel\Jetstream\Http\Controllers\TeamInvitationController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

use App\Http\Controllers\Admin\Article\ArticleController;
use App\Http\Controllers\Admin\Banner\BannerController;
use App\Http\Controllers\Admin\Chart\ChartController;
use App\Http\Controllers\Admin\Component\ComponentController;
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

    // --- Аутентификация (Логин) Jetstream/Fortify ---
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->middleware(['guest'])
        ->name('login');

    // Публичная часть сайта
    Route::middleware([CheckDowntime::class])->group(function () use ($siteLayout) {

        // Публичное API: меню рубрик, зависит от текущего шаблона
        $publicRubricController = "App\\Http\\Controllers\\Public\\{$siteLayout}\\RubricController";
        Route::get('/api/menu-rubrics', [$publicRubricController, 'menuRubrics'])->name('api.rubrics.menu');

        Route::get('/', fn() => Inertia::render('Public/' . $siteLayout . '/Index'))->name('home'); // Добавим имя

        $publicRubricController = "App\\Http\\Controllers\\Public\\{$siteLayout}\\RubricController";
        Route::get('/rubrics/{url}', [$publicRubricController, 'show'])->where('url', '.*')->name('public.rubrics.show');

        $publicArticleController = "App\\Http\\Controllers\\Public\\{$siteLayout}\\ArticleController";
        Route::get('/articles/{url}', [$publicArticleController, 'show'])->where('url', '.*')->name('public.articles.show');

        $publicTagController = "App\\Http\\Controllers\\Public\\{$siteLayout}\\TagController";
        Route::get('/tags/{url}', [$publicTagController, 'show'])->where('url', '.*')->name('public.tags.show');

        $publicVideoController = "App\\Http\\Controllers\\Public\\{$siteLayout}\\VideoController";
        Route::get('/videos/{url}', [$publicVideoController, 'show'])->where('url', '.*')->name('public.videos.show');

        // TODO: Добавить другие публичные маршруты (поиск, контакты и т.д.)

        // --- Маршруты без аутентификации Jetstream/Fortify ---
        // --- Аутентификация (Логин) ---
        Route::post('/login', [AuthenticatedSessionController::class, 'store'])
            ->middleware(['guest'])
            ->name('login.store');

        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
            ->middleware(['auth'])
            ->name('logout');

        // --- Регистрация ---
        Route::get('/register', [RegisteredUserController::class, 'create'])
            ->middleware(['guest'])
            ->name('register');

        Route::post('/register', [RegisteredUserController::class, 'store'])
            ->middleware(['guest'])
            ->name('register.store');

        // --- Сброс пароля ---
        Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
            ->middleware(['guest'])
            ->name('password.request');

        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
            ->middleware(['guest'])
            ->name('password.email');

        Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
            ->middleware(['guest'])
            ->name('password.reset');

        Route::post('/reset-password', [NewPasswordController::class, 'store'])
            ->middleware(['guest'])
            ->name('password.update');

        // --- Подтверждение Email (если включено) ---
        Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])
            ->middleware(['auth'])
            ->name('verification.notice');

        Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
            ->middleware(['auth', 'signed', 'throttle:6,1'])
            ->name('verification.verify');

        Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware(['auth', 'throttle:6,1'])
            ->name('verification.send');
    });

    // --- Стандартные маршруты Jetstream / Fortify с поддержкой локали ---
    Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

        // Пользовательский дашборд
        Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');

        // Профиль пользователя
        Route::get('/profile', [UserProfileController::class, 'show'])->name('profile.show');

        // Страница со списком токенов
        Route::get('/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
        // Создание нового токена
        Route::post('/api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
        // Обновление прав токена
        Route::put('/api-tokens/{tokenId}', [ApiTokenController::class, 'update'])->name('api-tokens.update');
        // Удаление токена
        Route::delete('/api-tokens/{tokenId}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');

        // Удаление пользователя
        Route::delete('/user', [CurrentUserController::class, 'destroy'])->name('current-user.destroy');

        // Выход с других браузерных сессий
        Route::delete('/user/other-browser-sessions', [OtherBrowserSessionsController::class, 'destroy'])->name('other-browser-sessions.destroy');

        // Удаление фото профиля
        Route::delete('/user/profile-photo', [ProfilePhotoController::class, 'destroy'])->name('current-user-photo.destroy');

        // Обновление информации профиля
        Route::put('/user/profile-information', [\Laravel\Fortify\Http\Controllers\ProfileInformationController::class, 'update'])->name('user-profile-information.update');

        // Обновление пароля пользователя
        Route::put('/user/password', [\Laravel\Fortify\Http\Controllers\PasswordController::class, 'update'])->name('user-password.update');

        // Подтверждение пароля
        Route::get('/user/confirm-password', [\Laravel\Fortify\Http\Controllers\ConfirmablePasswordController::class, 'show'])->name('password.confirm');
        Route::post('/user/confirm-password', [\Laravel\Fortify\Http\Controllers\ConfirmablePasswordController::class, 'store'])->name('password.confirm.store');
        Route::get('/user/confirmed-password-status', [\Laravel\Fortify\Http\Controllers\ConfirmedPasswordStatusController::class, 'show'])->name('password.confirmation');

        // --- Двухфакторная аутентификация (2FA) ---
        // Включение 2FA
        Route::post('/user/two-factor-authentication', [\Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController::class, 'store'])->name('two-factor.enable');
        // Подтверждение включения 2FA
        Route::post('/user/confirmed-two-factor-authentication', [\Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController::class, 'store'])->name('two-factor.confirm');
        // Отключение 2FA
        Route::delete('/user/two-factor-authentication', [\Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController::class, 'destroy'])->name('two-factor.disable');
        // Получение QR-кода
        Route::get('/user/two-factor-qr-code', [\Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController::class, 'show'])->name('two-factor.qr-code');
        // Получение секретного ключа
        Route::get('/user/two-factor-secret-key', [\Laravel\Fortify\Http\Controllers\TwoFactorSecretKeyController::class, 'show'])->name('two-factor.secret-key');
        // Получение и генерация recovery-кодов
        Route::get('/user/two-factor-recovery-codes', [\Laravel\Fortify\Http\Controllers\RecoveryCodeController::class, 'index'])->name('two-factor.recovery-codes');
        Route::post('/user/two-factor-recovery-codes', [\Laravel\Fortify\Http\Controllers\RecoveryCodeController::class, 'store']);

        // --- Работа с командами (Teams) ---
        // Форма создания команды
        Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
        // Сохранение новой команды
        Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
        // Показ страницы команды
        Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
        // Обновление названия команды
        Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
        // Удаление команды
        Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');

        // Участники команды
        Route::post('/teams/{team}/members', [TeamMemberController::class, 'store'])->name('team-members.store');
        Route::put('/teams/{team}/members/{user}', [TeamMemberController::class, 'update'])->name('team-members.update');
        Route::delete('/teams/{team}/members/{user}', [TeamMemberController::class, 'destroy'])->name('team-members.destroy');

        // Приглашения в команду
        Route::post('/team-invitations/{invitation}', [TeamInvitationController::class, 'accept'])->name('team-invitations.accept');
        Route::delete('/team-invitations/{invitation}', [TeamInvitationController::class, 'destroy'])->name('team-invitations.destroy');

        // Переключение текущей команды
        Route::put('/current-team', [CurrentTeamController::class, 'update'])->name('current-team.update');

        // --- Политика и условия ---
        // Условия использования
        Route::get('/terms-of-service', [TermsOfServiceController::class, 'show'])->name('terms.show');
        // Политика конфиденциальности
        Route::get('/privacy-policy', [PrivacyPolicyController::class, 'show'])->name('policy.show');
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

            Route::get('/phpinfo', [PhpInfoController::class, 'index'])->name('phpinfo.index');
            Route::get('/composer', [ComposerController::class, 'index'])->name('composer.index');
            Route::get('/package', [PackageController::class, 'index'])->name('package.index');
            Route::get('/env', [EnvController::class, 'index'])->name('env.index');

            // --- Настройки отображения в админке ---
            Route::prefix('settings')->name('settings.')->group(function () {

                // Количество на странице
                Route::put('/update-count/athletes', [SettingController::class, 'updateAdminCountAthletes'])->name('updateAdminCountAthletes');
                Route::put('/update-count/tournaments', [SettingController::class, 'updateAdminCountTournaments'])->name('updateAdminCountTournaments');
                Route::put('/update-count/categories', [SettingController::class, 'updateAdminCountCategories'])->name('updateAdminCountCategories');
                Route::put('/update-count/rubrics', [SettingController::class, 'updateAdminCountRubrics'])->name('updateAdminCountRubrics');
                Route::put('/update-count/sections', [SettingController::class, 'updateAdminCountSections'])->name('updateAdminCountSections');
                Route::put('/update-count/articles', [SettingController::class, 'updateAdminCountArticles'])->name('updateAdminCountArticles');
                Route::put('/update-count/tags', [SettingController::class, 'updateAdminCountTags'])->name('updateAdminCountTags');
                Route::put('/update-count/banners', [SettingController::class, 'updateAdminCountBanners'])->name('updateAdminCountBanners');
                Route::put('/update-count/videos', [SettingController::class, 'updateAdminCountVideos'])->name('updateAdminCountVideos');
                Route::put('/update-count/users', [SettingController::class, 'updateAdminCountUsers'])->name('updateAdminCountUsers');
                Route::put('/update-count/roles', [SettingController::class, 'updateAdminCountRoles'])->name('updateAdminCountRoles');
                Route::put('/update-count/permissions', [SettingController::class, 'updateAdminCountPermissions'])->name('updateAdminCountPermissions');
                Route::put('/update-count/plugins', [SettingController::class, 'updateAdminCountPlugins'])->name('updateAdminCountPlugins');
                Route::put('/update-count/settings', [SettingController::class, 'updateAdminCountSettings'])->name('updateAdminCountSettings');

                // Тип сортировки
                Route::put('/update-sort/athletes', [SettingController::class, 'updateAdminSortAthletes'])->name('updateAdminSortAthletes');
                Route::put('/update-sort/tournaments', [SettingController::class, 'updateAdminSortTournaments'])->name('updateAdminSortTournaments');
                Route::put('/update-sort/categories', [SettingController::class, 'updateAdminSortCategories'])->name('updateAdminSortCategories');
                Route::put('/update-sort/rubrics', [SettingController::class, 'updateAdminSortRubrics'])->name('updateAdminSortRubrics');
                Route::put('/update-sort/sections', [SettingController::class, 'updateAdminSortSections'])->name('updateAdminSortSections');
                Route::put('/update-sort/articles', [SettingController::class, 'updateAdminSortArticles'])->name('updateAdminSortArticles');
                Route::put('/update-sort/tags', [SettingController::class, 'updateAdminSortTags'])->name('updateAdminSortTags');
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
            Route::resource('/athletes', AthleteController::class);
            Route::resource('/tournaments', TournamentController::class);
            Route::resource('/categories', CategoryController::class);
            Route::resource('/rubrics', RubricController::class);
            Route::resource('/sections', SectionController::class);
            Route::resource('/articles', ArticleController::class);
            Route::resource('/tags', TagController::class);
            Route::resource('/banners', BannerController::class);
            Route::resource('/videos', VideoController::class);
            Route::resource('/charts', ChartController::class)->except(['show']);
            Route::resource('/reports', ReportController::class)->only(['index']);
            Route::resource('/components', ComponentController::class);
            Route::post('/components/save', [ComponentController::class, 'save'])->name('components.save'); // Выносим отдельно, т.к. не ресурсный
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
                Route::put('/athletes/{athlete}/activity', [AthleteController::class, 'updateActivity'])->name('athletes.updateActivity');
                Route::put('/tournaments/{tournament}/activity', [TournamentController::class, 'updateActivity'])->name('tournaments.updateActivity');
                Route::put('/categories/{category}/activity', [CategoryController::class, 'updateActivity'])->name('categories.updateActivity');
                Route::put('/rubrics/{rubric}/activity', [RubricController::class, 'updateActivity'])->name('rubrics.updateActivity');
                Route::put('/sections/{section}/activity', [SectionController::class, 'updateActivity'])->name('sections.updateActivity');
                Route::put('/articles/{article}/activity', [ArticleController::class, 'updateActivity'])->name('articles.updateActivity');
                Route::put('/tags/{tag}/activity', [TagController::class, 'updateActivity'])->name('tags.updateActivity');
                Route::put('/banners/{banner}/activity', [BannerController::class, 'updateActivity'])->name('banners.updateActivity');
                Route::put('/videos/{video}/activity', [VideoController::class, 'updateActivity'])->name('videos.updateActivity');
                Route::put('/settings/{setting}/activity', [ParameterController::class, 'updateActivity'])->name('settings.updateActivity');
                Route::put('/plugins/{plugin}/activity', [PluginController::class, 'updateActivity'])->name('plugins.updateActivity');

                // Переключение активности массово
                Route::put('/admin/actions/athletes/bulk-activity', [AthleteController::class, 'bulkUpdateActivity'])
                    ->name('athletes.bulkUpdateActivity');
                Route::put('/admin/actions/tournaments/bulk-activity', [TournamentController::class, 'bulkUpdateActivity'])
                    ->name('tournaments.bulkUpdateActivity');
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
                Route::put('/athletes/update-sort-bulk', [AthleteController::class, 'updateSortBulk'])->name('athletes.updateSortBulk');
                Route::put('/tournaments/update-sort-bulk', [TournamentController::class, 'updateSortBulk'])->name('tournaments.updateSortBulk');
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
                Route::put('/athletes/{athlete}/sort', [AthleteController::class, 'updateSort'])->name('athletes.updateSort');
                Route::put('/tournaments/{tournament}/sort', [TournamentController::class, 'updateSort'])->name('tournaments.updateSort');
                Route::put('/categories/{category}/sort', [CategoryController::class, 'updateSort'])->name('categories.updateSort');
                Route::put('/rubrics/{rubric}/sort', [RubricController::class, 'updateSort'])->name('rubrics.updateSort');
                Route::put('/sections/{section}/sort', [SectionController::class, 'updateSort'])->name('sections.updateSort');
                Route::put('/tags/{tag}/sort', [TagController::class, 'updateSort'])->name('tags.updateSort');
                Route::put('/videos/{video}/sort', [VideoController::class, 'updateSort'])->name('videos.updateSort');
                Route::put('/banners/{banner}/sort', [BannerController::class, 'updateSort'])->name('banners.updateSort');
                Route::put('/videos/{video}/sort', [VideoController::class, 'updateSort'])->name('videos.updateSort');
                Route::put('/plugins/{plugin}/sort', [PluginController::class, 'updateSort'])->name('plugins.updateSort');
                Route::put('/parameters/{parameter}/sort', [ParameterController::class, 'updateSort'])->name('parameters.updateSort');

                // Массовое удаление
                Route::delete('/rubrics/bulk-delete', [RubricController::class, 'bulkDestroy'])->name('rubrics.bulkDestroy');
                Route::delete('/sections/bulk-delete', [SectionController::class, 'bulkDestroy'])->name('sections.bulkDestroy');
                Route::delete('/articles/bulk-delete', [ArticleController::class, 'bulkDestroy'])->name('articles.bulkDestroy');
                Route::delete('/tags/bulk-delete', [TagController::class, 'bulkDestroy'])->name('tags.bulkDestroy');
                Route::delete('/banners/bulk-delete', [BannerController::class, 'bulkDestroy'])->name('banners.bulkDestroy');
                Route::delete('/videos/bulk-delete', [VideoController::class, 'bulkDestroy'])->name('videos.bulkDestroy');
            }); // Конец группы actions

        }); // Конец группы admin

    // --- Остальные маршруты (Filemanager, Redis test) ---
    Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
        \UniSharp\LaravelFilemanager\Lfm::routes();
    });

});

