<?php

namespace App\Http\Resources\Admin\Video;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoSharedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'sort'              => $this->sort,
            'activity'          => $this->activity,
            'left'              => $this->left,
            'main'              => $this->main,
            'right'             => $this->right,
            'locale'            => $this->locale,
            'title'             => $this->title,
            'url'               => $this->url,
            'short'             => $this->short,
            'description'       => $this->description,
            'author'            => $this->author,
            'published_at'      => $this->published_at,
            'duration'          => $this->duration,
            'source_type'       => $this->source_type,
            'video_url'         => $this->video_url,
            'external_video_id' => $this->external_video_id,
            'views'             => $this->views,
            'likes'             => $this->likes,
            'meta_title'        => $this->meta_title,
            'meta_keywords'     => $this->meta_keywords,
            'meta_desc'         => $this->meta_desc,
            'created_at'        => $this->created_at?->format('d-m-Y'),
            'updated_at'        => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
