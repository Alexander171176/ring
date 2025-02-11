<?php

namespace App\Providers;

use App\Models\Admin\Setting\Setting;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        // Принудительно установим локаль из настроек базы данных
        $localeSetting = Setting::where('option', 'locale')->first();
        $locale = $localeSetting ? $localeSetting->value : config('app.locale');

        App::setLocale($locale);

        // Передача текущей локали во все Inertia-компоненты
        Inertia::share([
            'locale' => App::getLocale(),
        ]);
    }
}
