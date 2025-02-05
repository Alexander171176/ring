<?php

namespace Database\Factories\Admin\Rubric;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin\Rubric\RubricTranslation>
 */
class RubricTranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => substr($this->faker->unique()->sentence(3), 0, 255),
            'url' => substr($this->faker->unique()->slug, 0, 255),
            'description' => substr($this->faker->text(160), 0, 255),
            'meta_title' => substr($this->faker->sentence(30), 0, 255),
            'meta_keywords' => substr($this->faker->words(2, true), 0, 255),
            'meta_desc' => substr($this->faker->text(160), 0, 255),
        ];
    }
}
