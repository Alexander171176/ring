<?php

namespace App\Models\Admin\Video;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ImageVideo extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'image_videos';

    protected $fillable = [
        'order',   // Сортировка
        'path',    // Резервное поле, если потребуется
        'alt',     // Альтернативный текст
        'caption', // Подпись к изображению
    ];

    /**
     * Отношение многие ко многим с моделью Video
     */
    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(Video::class, 'image_has_videos', 'image_id', 'video_id');
    }

    /**
     * Автоматическая генерация WebP-версии изображения.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(80)
            ->performOnCollections('images');
    }

    /**
     * Определение медиаколлекции изображений.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->singleFile(); // одно изображение в коллекции (если требуется много, уберите singleFile())
    }
}
