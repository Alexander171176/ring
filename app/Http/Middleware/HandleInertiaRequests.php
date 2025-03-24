<?php

namespace App\Http\Middleware;

use App\Http\Resources\Admin\Article\ArticleSharedResource;
use App\Http\Resources\Admin\Plugin\PluginSharedResource;
use App\Http\Resources\Admin\Rubric\RubricSharedResource;
use App\Http\Resources\Admin\Setting\SettingSharedResource;
use App\Http\Resources\Admin\User\UserSharedResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Plugin\Plugin;
use App\Models\Admin\Rubric\Rubric;
use App\Models\Admin\Setting\Setting; // Убедитесь, что импортируете правильную модель Setting
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Middleware;
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
        $articles = Article::all(); // Получение всех статей из базы данных

        return [
            ...parent::share($request),
            'rubrics' => fn () => RubricSharedResource::collection($rubrics)->toArray($request),
            'articles' => fn () => ArticleSharedResource::collection($articles)->toArray($request),
            'plugins' => fn () => PluginSharedResource::collection($plugins)->toArray($request),
            'settings' => fn () => SettingSharedResource::collection($settings)->toArray($request),
            'user' => fn () => $user ? (new UserSharedResource($user))->toArray($request) : null,
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'locale' => App::getLocale(), // Добавляем текущую локаль
        ];
    }
}
