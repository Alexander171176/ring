<?php

namespace App\Models\Admin\Athlete; // Пространство имен правильное, если AthleteImage живет рядом с Athlete

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
// use App\Models\Admin\Athlete\Athlete; // Athlete уже в том же namespace, можно не импортировать, если не для type-hinting в PHPDoc

class AthleteImage extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'athlete_images';

    protected $fillable = [
        'order',
        'alt',
        'caption',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Атлеты, к которым привязана эта запись AthleteImage.
     * Хотя AthleteImage может быть концептуально "изображением атлета",
     * через belongsToMany она МОЖЕТ быть привязана ко многим атлетам.
     * Если вы хотите, чтобы ОДНА запись AthleteImage (с ее alt, caption и файлом)
     * использовалась для НЕСКОЛЬКИХ атлетов, эта связь верна.
     *
     * Если же AthleteImage всегда принадлежит ТОЛЬКО ОДНОМУ атлету,
     * тогда пивотная таблица не нужна, и связь была бы Athlete -> hasMany(AthleteImage),
     * а в AthleteImage был бы athlete_id и belongsTo(Athlete).
     *
     * Судя по пивотной таблице, вы выбрали первый вариант (M:M).
     */
    public function athletes(): BelongsToMany
    {
        return $this->belongsToMany(Athlete::class, 'athlete_has_images', 'image_id', 'athlete_id')
            ->withPivot('order') // Важно добавить, чтобы получать порядок из пивотной таблицы
            ->orderByPivot('order', 'asc'); // Автоматически сортировать при получении
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images') // Можно назвать более специфично, например 'athlete_image_file'
        ->singleFile(); // Каждая запись AthleteImage хранит один файл
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
