<?php

namespace App\Http\Controllers\Public\Default;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\About\SectionResource;
use App\Http\Resources\Admin\Page\PageResource;
use App\Models\Admin\About\Section;
use App\Models\Admin\Page\Page;
use App\Traits\CacheTimeTrait;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class AboutController extends Controller
{
    use CacheTimeTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $aboutsPage = Cache::store('redis')->remember('pages.abouts', $cacheTime, function () {
            return Page::where('url', 'abouts')->first();
        });

        $sections = Cache::store('redis')->remember('sections.all', $cacheTime, function () {
            return Section::all();
        });

        return Inertia::render('Templates/Default/pages/AboutUs', [
            'page' => $aboutsPage ? new PageResource($aboutsPage) : null, // Передаём страницу abouts, если она существует
            'sections' => SectionResource::collection($sections),
        ]);
    }

}
