<?php

namespace App\Http\Controllers\Admin\Invokable;

use App\Http\Controllers\Controller;
use App\Models\Admin\Section\Section;
use App\Models\Admin\Video\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RemoveSectionFromVideoController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Video $video, Section $section): RedirectResponse
    {
        // Удаляем связь видео с секцией
        $video->sections()->detach($section->id);

        return back()->with('success', 'Видео успешно удалено из секции');
    }
}
