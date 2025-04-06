<?php

namespace App\Http\Resources\Admin\Video;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\Section\SectionResource; // Убедитесь, что импорты есть
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Video\VideoImageResource;

class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sort' => $this->sort,
            'locale' => $this->locale,
            'title' => $this->title,
            'url' => $this->url,
            'short' => $this->short,
            'description' => $this->description,
            'author' => $this->author,
            'published_at' => $this->published_at ? $this->published_at->toDateString() : null, // Форматируем дату
            'duration' => $this->duration,
            'source_type' => $this->source_type,

            // --- ЛОГИКА ДЛЯ URL ВИДЕО ---
            'video_url' => $this->when(
                $this->source_type === 'local', // Условие
                fn () => $this->getFirstMediaUrl('videos'), // Получаем URL из Spatie
                $this->video_url // В противном случае используем значение из БД (для code)
            ),
            // --- КОНЕЦ ЛОГИКИ ---

            'external_video_id' => $this->external_video_id,
            'views' => $this->views,
            'likes' => $this->likes,
            'meta_title' => $this->meta_title,
            'meta_keywords' => $this->meta_keywords,
            'meta_desc' => $this->meta_desc,
            'activity' => $this->activity,
            'left' => $this->left,
            'main' => $this->main,
            'right' => $this->right,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,

            // Связанные данные (убедитесь, что они загружены через with() в контроллере)
            'sections' => SectionResource::collection($this->whenLoaded('sections')),
            'articles' => ArticleResource::collection($this->whenLoaded('articles')),
            'images' => VideoImageResource::collection($this->whenLoaded('images')),
            'related_videos' => VideoResource::collection($this->whenLoaded('relatedVideos')), // Самоссылающийся ресурс
        ];
    }
}
