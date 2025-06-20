<?php

namespace App\Http\Controllers\Public\Default;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Video\VideoResource;
use App\Models\Admin\Video\Video;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VideoController extends Controller
{
    /**
     * Страница показа видео
     */
    public function show(string $url): Response
    {
        $video = Video::where('url', $url)
            ->where('activity', 1)
            ->with([
                'images' => fn($q) => $q->orderBy('order'),
                'relatedVideos' => function ($query) {
                    $query->where('activity', 1);
                },
            ])
            ->firstOrFail();

        return Inertia::render('Public/Default/Videos/Show', [
            'video' => new VideoResource($video),
            'recommendedVideos' => VideoResource::collection($video->relatedVideos),
        ]);
    }

}
