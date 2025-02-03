<?php

namespace Database\Factories\Admin\Article;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin\Article\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sort' => $this->faker->numberBetween(1, 100),
            'title' => substr($this->faker->unique()->sentence(3), 0, 255),
            'url' => substr($this->faker->unique()->slug, 0, 255),
            'description' => substr($this->faker->text(160), 0, 255),
            'author' => substr($this->faker->name, 0, 255),
            'activity' => $this->faker->randomElement([0, 1]),
            'tags' => substr($this->faker->words(3, true), 0, 255),
            'views' => $this->faker->numberBetween(1, 100),
            'likes' => $this->faker->numberBetween(1, 100),
            'image_url' => substr($this->faker->imageUrl(), 0, 255),
            'seo_title' => substr($this->faker->sentence(10), 0, 255),
            'seo_alt' => substr($this->faker->sentence(10), 0, 255),
            'meta_title' => substr($this->faker->sentence(30), 0, 255),
            'meta_keywords' => substr($this->faker->words(3, true), 0, 255),
            'meta_desc' => substr($this->faker->text(160), 0, 255),
        ];
    }
}
