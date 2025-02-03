<?php

namespace App\Http\Controllers\Admin\Builder;

use App\Http\Controllers\Controller;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class BuilderController extends Controller
{
    use CacheTimeTrait;

    protected $directories = [
        'Default' => 'js/Pages/Templates/Default',
        'DigitalPro' => 'js/Pages/Templates/DigitalPro',
        'Pulsar' => 'js/Pages/Templates/Pulsar',
        'Pages' => 'js/Pages/templates/Default/pages',
    ];

    protected $customFiles = [
        'js/Pages/Templates/Default/Index.vue',
        'js/Pages/Templates/DigitalPro/Index.vue',
        'js/Pages/Templates/Pulsar/Index.vue',
    ];

    public function index(Request $request): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $fileContents = Cache::store('redis')->remember('file_contents', $cacheTime, function () {
            return $this->getFilesWithContents();
        });

        $customFileContents = Cache::store('redis')->remember('custom_file_contents', $cacheTime, function () {
            return $this->getCustomFilesWithContents();
        });

        $fileContents['Files'] = $customFileContents;

        return Inertia::render('Admin/Builders/Index', [
            'fileContents' => $fileContents,
        ]);
    }

    protected function getFilesWithContents(): array
    {
        $fileContents = [];
        foreach ($this->directories as $key => $directory) {
            $path = resource_path($directory);
            $files = File::allFiles($path);
            $fileContents[$key] = [];
            foreach ($files as $file) {
                $relativePath = str_replace(resource_path(), '', $file->getPathname());
                $fileContents[$key][$relativePath] = File::get($file);
            }
        }
        return $fileContents;
    }

    protected function getCustomFilesWithContents(): array
    {
        $customFileContents = [];
        foreach ($this->customFiles as $filePath) {
            $fullPath = resource_path($filePath);
            $customFileContents[$filePath] = File::exists($fullPath) ? File::get($fullPath) : 'Файл не найден';
        }
        return $customFileContents;
    }

    public function save(Request $request): \Illuminate\Http\RedirectResponse
    {
        $fileName = $request->input('fileName');
        $newContent = $request->input('fileContent');
        $filePath = resource_path($fileName);

        if (File::exists($filePath)) {
            File::put($filePath, $newContent);

            // Очистка кэша через трейт после сохранения файла
            $this->clearCache(['file_contents', 'custom_file_contents']);

            return redirect()->route('builders.index')->with('success', 'Файл успешно сохранен');
        }

        return redirect()->route('builders.index')->with('error', 'Файл не найден');
    }
}
