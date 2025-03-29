<?php

namespace App\PathGenerators;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        // Если медиа привязано к сущности Article (напрямую)
        if ($media->model_type === 'App\\Models\\Admin\\Article\\Article') {
            return 'articles/' . $media->model_id . '/';
        }

        // Если медиа привязано к сущности ArticleImage
        if ($media->model_type === 'App\\Models\\Admin\\Article\\ArticleImage') {
            return 'article_images/' . $media->model_id . '/';
        }

        // Если медиа привязано к сущности Banner (напрямую)
        if ($media->model_type === 'App\\Models\\Admin\\Banner\\Banner') {
            return 'banners/' . $media->model_id . '/';
        }

        // Если медиа привязано к сущности BannerImage
        if ($media->model_type === 'App\\Models\\Admin\\Banner\\BannerImage') {
            return 'banner_images/' . $media->model_id . '/';
        }

        // Дефолтный путь для остальных случаев
        return 'media/' . $media->model_id . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive/';
    }
}
