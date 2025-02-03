<?php

namespace App\Http\Controllers\Admin\Component;

use App\Http\Controllers\Controller;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class ComponentController extends Controller
{
    use CacheTimeTrait;

    protected $directories = [
        'Components' => 'js/Components',
        'Layouts' => 'js/Layouts',
        'locales' => 'js/locales',
        'Pages' => 'js/Pages/templates/Default/pages',
        'PartialsUser' => 'js/Partials/User',
    ];

    protected $customFiles = [
        'js/Pages/Index.vue',
        'js/Pages/Dashboard.vue',
        'js/Pages/Admin/Components/Index.vue',
    ];

    public function index(Request $request): \Inertia\Response
    {
        $fileContents = $this->getFilesWithContents();
        $customFileContents = $this->getCustomFilesWithContents();

        $fileContents['Files'] = $customFileContents;

        return Inertia::render('Admin/Components/Index', [
            'fileContents' => $fileContents,
        ]);
    }

    protected function getFilesWithContents(): array
    {
        $cacheTime = $this->getCacheTime();

        return Cache::store('redis')->remember('files_with_contents', $cacheTime, function () {
            $fileContents = [];

            foreach ($this->directories as $key => $directory) {
                $path = resource_path($directory);
                $files = File::files($path);

                $fileContents[$key] = [];

                foreach ($files as $file) {
                    $relativePath = str_replace(resource_path(), '', $file->getPathname());
                    $fileContents[$key][$relativePath] = File::get($file);
                }
            }

            return $fileContents;
        });
    }

    protected function getCustomFilesWithContents(): array
    {
        $cacheTime = $this->getCacheTime();

        return Cache::store('redis')->remember('custom_files_with_contents', $cacheTime, function () {
            $customFileContents = [];

            foreach ($this->customFiles as $filePath) {
                $fullPath = resource_path($filePath);
                $customFileContents[$filePath] = File::exists($fullPath) ? File::get($fullPath) : 'Файл не найден';
            }

            return $customFileContents;
        });
    }

    public function save(Request $request): \Illuminate\Http\RedirectResponse
    {
        $fileName = $request->input('fileName');
        $newContent = $request->input('fileContent');
        $filePath = resource_path($fileName);

        if (File::exists($filePath)) {
            File::put($filePath, $newContent);

            // Очистка кэша Redis после сохранения файла
            $this->clearCache(['files_with_contents', 'custom_files_with_contents']);

            return redirect()->route('components.index')->with('success', 'Файл успешно сохранен');
        }

        return redirect()->route('components.index')->with('error', 'Файл не найден');
    }
}
