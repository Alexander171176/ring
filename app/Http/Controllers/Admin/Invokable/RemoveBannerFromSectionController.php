<?php

namespace App\Http\Controllers\Admin\Invokable;

use App\Http\Controllers\Controller;
use App\Models\Admin\Banner\Banner;
use App\Models\Admin\Section\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RemoveBannerFromSectionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Banner $banner, Section $section): RedirectResponse
    {
        // Удаляем связь статьи с рубрикой
        $banner->sections()->detach($section->id);

        return back()->with('success', 'Баннер успешно удален из секции');
    }
}
