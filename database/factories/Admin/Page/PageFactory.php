<?php

namespace Database\Factories\Admin\Page;

use App\Models\Admin\Page\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Page>
 */
class PageFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Page::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence;
        $slug = Str::slug($title);

        return [
            'sort' => $this->faker->numberBetween(0, 100),
            'title' => $title,
            'url' => $slug,
            'slug' => $slug,
            'content' => $this->faker->paragraphs(3, true),
            'meta_title' => $this->faker->sentence,
            'meta_keywords' => implode(',', $this->faker->words(5)),
            'meta_desc' => $this->faker->sentence,
            'activity' => $this->faker->boolean,
            'print_in_menu' => $this->faker->boolean,
            'without_style' => $this->faker->boolean,
            'parent_id' => null, // initially null, can be updated later if needed
        ];
    }
}
