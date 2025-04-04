<?php

namespace App\Http\Controllers\Admin\Video;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Video\VideoResource;
use App\Models\Admin\Video\Video;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $videos = Video::with(['sections', 'articles', 'images'])->get();
        $videosCount = Video::count();

        // Получаем значение параметра из конфигурации (оно загружается через AppServiceProvider)
        $adminCountVideos = config('site_settings.AdminCountVideos', 10);
        $adminSortVideos  = config('site_settings.AdminSortVideos', 'idDesc');

        return Inertia::render('Admin/Videos/Index', [
            'videos' => VideoResource::collection($videos),
            'videosCount' => $videosCount,
            'adminCountVideos' => (int)$adminCountVideos,
            'adminSortVideos' => $adminSortVideos,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
