<?php

namespace App\Http\Resources\Admin\Page;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ресурс для передачи базовой информации о странице,
 * обычно используется для списков выбора (например, parent_id).
 */
class PageSharedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Получаем экземпляр модели
        $page = $this->resource;

        // Возвращаем только самые необходимые данные для идентификации и отображения в списке
        return [
            'id' => $page->id,
            'title' => $page->title,
            // --- Опционально, но может быть полезно для отображения иерархии в списке ---
            // Можно добавить parent_id, если на фронтенде будет логика построения дерева/отступов
            'parent_id' => $page->parent_id,
            // Можно добавить locale, если список смешанный (хотя обычно он фильтруется заранее)
            // 'locale' => $page->locale,
        ];
    }
}
