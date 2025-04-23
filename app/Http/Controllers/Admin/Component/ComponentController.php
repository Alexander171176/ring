<?php

namespace App\Http\Controllers\Admin\Component;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; // Оставляем Request для save, т.к. FormRequest здесь сложнее из-за динамики
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log; // Для логирования попыток доступа
use Illuminate\Validation\Rule; // Для валидации разрешенных файлов
use Inertia\Inertia;
use Inertia\Response as InertiaResponse; // Используем псевдоним
use Throwable; // Для обработки ошибок файловой системы

class ComponentController extends Controller
{
    // Определяем РАЗРЕШЕННЫЕ для редактирования директории и файлы
    // Ключ - отображаемое имя, значение - путь относительно resource_path()
    protected array $editableDirectories = [
        'Components'  => 'js/Components',
        'Layouts'     => 'js/Layouts',
        'locales'     => 'js/locales', // Редактировать JS локали? Опасно. Лучше через спец. интерфейс.
        'PartialsUser'=> 'js/Partials/User',
        // 'PartialsAdmin'=> 'js/Partials/Admin', // Пример
    ];

    // Явный список разрешенных для редактирования файлов
    protected array $editableFiles = [
        // Используем путь относительно resource_path() как ключ и значение
        'js/Pages/Admin/Components/Welcome.vue' => 'js/Pages/Admin/Components/Welcome.vue',
        // 'js/Pages/Dashboard.vue' => 'js/Pages/Dashboard.vue', // Редактировать основные страницы?
        // 'js/Pages/Index.vue' => 'js/Pages/Index.vue', // Редактировать главную?
    ];

    /**
     * Отображение списка редактируемых компонентов.
     */
    public function index(): InertiaResponse
    {
        // TODO: Проверка прав $this->authorize('view components');
        $fileContents = $this->getEditableFilesContent();

        return Inertia::render('Admin/Components/Index', [
            'fileContents' => $fileContents, // Передаем единый массив
        ]);
    }

    /**
     * Получает содержимое всех РАЗРЕШЕННЫХ файлов.
     */
    protected function getEditableFilesContent(): array
    {
        $allFilesData = [];

        // Обработка директорий
        foreach ($this->editableDirectories as $displayName => $relativePath) {
            $fullDirectoryPath = resource_path(ltrim($relativePath, '/'));
            $filesData = [];
            if (File::isDirectory($fullDirectoryPath)) {
                // Получаем только файлы с нужными расширениями
                $files = File::glob($fullDirectoryPath . '/*.{vue,js}', GLOB_BRACE); // Ищем .vue и .js
                foreach ($files as $fullFilePath) {
                    // Получаем относительный путь для использования как ключ/идентификатор
                    $fileKey = $this->getRelativePath($fullFilePath);
                    // Проверяем, что файл действительно находится ВНУТРИ разрешенной директории (защита от ../)
                    if ($fileKey && str_starts_with(realpath($fullFilePath), realpath($fullDirectoryPath))) {
                        try {
                            $filesData[$fileKey] = File::get($fullFilePath);
                        } catch (Throwable $e) {
                            Log::error("Ошибка чтения файла компонента: {$fileKey}", ['exception' => $e]);
                            $filesData[$fileKey] = "Ошибка чтения файла.";
                        }
                    }
                }
            }
            // Добавляем группу файлов
            if (!empty($filesData)) {
                $allFilesData[$displayName] = $filesData;
            }
        }

        // Обработка отдельных файлов
        $singleFilesData = [];
        foreach ($this->editableFiles as $fileKey => $relativePath) {
            $fullFilePath = resource_path(ltrim($relativePath, '/'));
            // Проверяем, что путь действителен и указывает на файл
            if (File::isFile($fullFilePath) && $this->isPathAllowed($fullFilePath)) { // Доп. проверка пути
                try {
                    $singleFilesData[$fileKey] = File::get($fullFilePath);
                } catch (Throwable $e) {
                    Log::error("Ошибка чтения файла компонента: {$fileKey}", ['exception' => $e]);
                    $singleFilesData[$fileKey] = "Ошибка чтения файла.";
                }
            } else {
                $singleFilesData[$fileKey] = "Файл не найден или не разрешен.";
                Log::warning("Попытка доступа к неразрешенному файлу: {$fileKey}");
            }
        }
        // Добавляем группу отдельных файлов, если есть
        if (!empty($singleFilesData)) {
            $allFilesData['Files'] = $singleFilesData; // Как было у вас
        }

        return $allFilesData;
    }

    /**
     * Сохранение содержимого файла компонента.
     */
    public function save(Request $request): RedirectResponse
    {
        // TODO: Проверка прав $this->authorize('edit components');

        // 1. Валидация входных данных
        $validated = $request->validate([
            // Проверяем, что переданный 'fileName' ЕСТЬ в наших разрешенных списках
            'fileName' => [
                'required',
                'string',
                Rule::in(array_keys($this->getAllAllowedFilePaths())) // Проверяем по списку разрешенных ключей
            ],
            // Содержимое может быть пустым
            'fileContent' => 'nullable|string|max:5242880', // Лимит ~5MB, можно настроить
        ]);

        $fileKey = $validated['fileName']; // Ключ (относительный путь из нашего списка)
        $newContent = $validated['fileContent'] ?? ''; // Берем контент или пустую строку
        // Получаем реальный путь из наших списков по ключу
        $relativePath = $this->getAllAllowedFilePaths()[$fileKey] ?? null;
        $fullFilePath = $relativePath ? resource_path(ltrim($relativePath, '/')) : null;

        // 2. Дополнительная проверка безопасности пути
        if (!$fullFilePath || !File::isWritable(dirname($fullFilePath)) || !$this->isPathAllowed($fullFilePath)) {
            Log::error("Попытка записи в неразрешенный или несуществующий файл", ['key' => $fileKey, 'path' => $fullFilePath]);
            return redirect()->route('admin.components.index')->with('error', 'Ошибка: Недопустимый файл для сохранения.');
        }

        // 3. Сохранение файла
        try {
            // Используем LOCK_EX для предотвращения гонки записи
            File::put($fullFilePath, $newContent, true);
            Log::info("Файл компонента успешно сохранен", ['path' => $fileKey]);
            // TODO: Рассмотреть очистку OPCache или перезапуск php-fpm, если они используются, чтобы изменения PHP увидели (для JS/Vue это не нужно)
            // if (function_exists('opcache_invalidate')) { opcache_invalidate($fullFilePath, true); }
            return redirect()->route('admin.components.index')->with('success', 'Файл "' . basename($fileKey) . '" успешно сохранен.');
        } catch (Throwable $e) {
            Log::error("Ошибка сохранения файла компонента: {$fileKey}", ['exception' => $e]);
            return redirect()->route('admin.components.index')->with('error', 'Ошибка сохранения файла "' . basename($fileKey) . '".');
        }
    }

    /**
     * Получает все разрешенные пути к файлам из директорий и отдельных файлов.
     * Ключ - относительный путь, который используется в запросе/ответе.
     * Значение - относительный путь от resource_path().
     */
    private function getAllAllowedFilePaths(): array
    {
        $allowed = $this->editableFiles; // Начинаем с отдельных файлов

        foreach ($this->editableDirectories as $relativePath) {
            $fullDirectoryPath = resource_path(ltrim($relativePath, '/'));
            if (File::isDirectory($fullDirectoryPath)) {
                $files = File::glob($fullDirectoryPath . '/*.{vue,js}', GLOB_BRACE);
                foreach ($files as $fullFilePath) {
                    $fileKey = $this->getRelativePath($fullFilePath);
                    // Проверка на выход за пределы директории
                    if ($fileKey && str_starts_with(realpath($fullFilePath), realpath($fullDirectoryPath))) {
                        $allowed[$fileKey] = $fileKey; // Используем относительный путь как ключ и значение
                    }
                }
            }
        }
        return $allowed;
    }

    /**
     * Проверяет, находится ли путь внутри разрешенных директорий или списков.
     * Базовая защита от выхода за пределы (`../`).
     */
    private function isPathAllowed(string $fullPath): bool
    {
        $realFullPath = realpath($fullPath);
        if ($realFullPath === false) return false; // Путь не существует

        // Проверяем по директориям
        foreach ($this->editableDirectories as $relativePath) {
            $realAllowedDir = realpath(resource_path(ltrim($relativePath, '/')));
            if ($realAllowedDir !== false && str_starts_with($realFullPath, $realAllowedDir . DIRECTORY_SEPARATOR)) {
                return true;
            }
        }

        // Проверяем по списку файлов
        foreach ($this->editableFiles as $relativeFilePath) {
            $realAllowedFile = realpath(resource_path(ltrim($relativeFilePath, '/')));
            if ($realAllowedFile !== false && $realFullPath === $realAllowedFile) {
                return true;
            }
        }

        return false; // Путь не разрешен
    }

    /**
     * Получает относительный путь от resource_path().
     */
    private function getRelativePath(string $fullPath): ?string
    {
        $resourcePath = realpath(resource_path());
        $realFullPath = realpath($fullPath);

        if ($resourcePath === false || $realFullPath === false) return null;

        // Убедимся, что файл внутри resource_path
        if (str_starts_with($realFullPath, $resourcePath)) {
            // Заменяем разделители и убираем начальный слеш
            return ltrim(str_replace($resourcePath, '', $realFullPath), DIRECTORY_SEPARATOR);
        }
        return null;
    }

    // Методы create, store (ресурсный), show, edit, update (ресурсный), destroy (ресурсный) не нужны для этого контроллера
}
