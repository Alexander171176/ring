<?php

namespace App\Models\Admin\Tournament;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TournamentImage extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'tournament_images';

    protected $fillable = [
        'order',
        'alt',
        'caption',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Турниры, к которым привязана эта запись TournamentImage.
     * Хотя TournamentImage может быть концептуально "изображением турнира",
     * через belongsToMany она МОЖЕТ быть привязана ко многим турнирам.
     */
    public function tournaments(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class, 'tournament_has_images', 'image_id', 'tournament_id')
            ->withPivot('order') // Важно добавить, чтобы получать порядок из пивотной таблицы
            ->orderByPivot('order', 'asc'); // Автоматически сортировать при получении
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images') // Можно назвать более специфично, например 'tournament_image_file'
        ->singleFile(); // Каждая запись TournamentImage хранит один файл
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Конверсия в WebP, и для этого WebP будут сгенерированы адаптивные версии
        $this->addMediaConversion('webp_responsive') // Дал немного другое имя для ясности
        ->format('webp')
            ->quality(80)
            ->withResponsiveImages() // <--- Вот эта строка включает генерацию адаптивных изображений
            ->performOnCollections('images'); // Применяем к коллекции 'images'

        // Отдельная конверсия для маленького превью (thumbnail)
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            // Для thumb обычно не создают адаптивные версии, но технически это возможно
            ->performOnCollections('images');

        // Эта конверсия создаст адаптивные версии из ОРИГИНАЛЬНОГО файла
        // (например, если загружен JPG, будут JPG разных размеров).
        // Если это то, что нужно, оставляем.
        // Если вы хотите адаптивные изображения и для оригинального формата (например, JPG),
        // вы можете создать "пустую" конверсию и применить к ней withResponsiveImages:
        $this->addMediaConversion('original_responsive')
            ->withResponsiveImages()
            ->performOnCollections('images');
    }

    // Аксессоры
    public function getImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('images');
    }

    public function getWebpResponsiveUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('images', 'webp_responsive');
    }

    public function getWebpResponsiveImageTagAttribute(): ?string
    {
        $mediaItem = $this->getFirstMedia('images');
        return $mediaItem ? $mediaItem->img('webp_responsive')->toHtml() : null;
    }

    // Аксессор для адаптивных изображений из оригинального файла
    public function getOriginalResponsiveImageTagAttribute(): ?string
    {
        $mediaItem = $this->getFirstMedia('images');
        // 'original_responsive' - имя вашей конверсии
        return $mediaItem ? $mediaItem->img('original_responsive')->toHtml() : null;
    }

    public function getThumbUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('images', 'thumb');
    }
}
