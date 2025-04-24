<?php

namespace App\Http\Middleware;

use App\Http\Resources\Admin\Article\ArticleSharedResource;
use App\Http\Resources\Admin\Banner\BannerSharedResource;
use App\Http\Resources\Admin\Plugin\PluginSharedResource;
use App\Http\Resources\Admin\Rubric\RubricSharedResource;
use App\Http\Resources\Admin\Section\SectionSharedResource;
use App\Http\Resources\Admin\Setting\SettingSharedResource;
use App\Http\Resources\Admin\User\UserSharedResource;
use App\Http\Resources\Admin\Video\VideoSharedResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Banner\Banner;
use App\Models\Admin\Plugin\Plugin;
use App\Models\Admin\Rubric\Rubric;
use App\Models\Admin\Section\Section;
use App\Models\Admin\Setting\Setting; // Убедитесь, что импортируете правильную модель Setting
use App\Models\Admin\Video\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Inertia\Middleware;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = auth()->user();
        $settings = Setting::all(); // Получение всех настроек из базы данных
        $plugins = Plugin::all(); // Получение всех плагинов из базы данных
        $rubrics = Rubric::all(); // Получение всех рубрик из базы данных
        $sections = Section::all(); // Получение всех рубрик из базы данных
        $articles = Article::all(); // Получение всех статей из базы данных
        $banners = Banner::all(); // Получение всех статей из базы данных
        $videos = Video::all(); // Получение всех видео из базы данных

//        Log::info('🌐 LOCALE DEBUG', [
//            'LaravelLocalization::getCurrentLocale()' => LaravelLocalization::getCurrentLocale(),
//            'app()->getLocale()' => app()->getLocale(),
//            'available locales' => array_keys(LaravelLocalization::getSupportedLocales()),
//            'request URI' => $request->getRequestUri(),
//        ]);

        return [
            ...parent::share($request),
            'rubrics' => fn () => RubricSharedResource::collection($rubrics)->toArray($request),
            'sections' => fn () => SectionSharedResource::collection($sections)->toArray($request),
            'articles' => fn () => ArticleSharedResource::collection($articles)->toArray($request),
            'banners' => fn () => BannerSharedResource::collection($banners)->toArray($request),
            'videos' => fn () => VideoSharedResource::collection($videos)->toArray($request),
            'plugins' => fn () => PluginSharedResource::collection($plugins)->toArray($request),
            'settings' => fn () => SettingSharedResource::collection($settings)->toArray($request),
            'user' => fn () => $user ? (new UserSharedResource($user))->toArray($request) : null,
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'locale' => LaravelLocalization::getCurrentLocale(),

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info'    => fn () => $request->session()->get('info'),
                // Добавьте другие ключи flash, если используете
            ],
        ];
    }
}
