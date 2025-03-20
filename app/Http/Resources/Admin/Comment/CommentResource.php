<?php

namespace App\Http\Resources\Admin\Comment;

use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Section\SectionResource;
use App\Http\Resources\Admin\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'user_id' => $this->user_id,
            'article_id' => $this->article_id,
            'section_id' => $this->section_id,
            'parent_id' => $this->parent_id,
            'content' => $this->content,
            'status' => $this->status,
            'activity' => $this->activity,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'user' => new UserResource($this->whenLoaded('user')), // Включаем данные пользователя
            'article' => new ArticleResource($this->whenLoaded('article')), // Включаем данные статьи
            'section' => new SectionResource($this->whenLoaded('section')), // Включаем данные рубрики
        ];
    }
}
