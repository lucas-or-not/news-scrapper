<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(6, true);

        return [
            'source_id' => Source::factory(),
            'source_article_id' => $this->faker->unique()->uuid(),
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => $this->faker->paragraph(2),
            'content' => $this->faker->paragraphs(5, true),
            'url' => $this->faker->url(),
            'image_url' => $this->faker->imageUrl(800, 600, 'news'),
            'author_id' => Author::factory(),
            'category_id' => Category::factory(),
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'scraped_at' => now(),
            'raw_payload' => [
                'original_data' => $this->faker->words(10),
            ],
            'language' => $this->faker->randomElement(['en', 'es', 'fr', 'de']),
        ];
    }

    /**
     * Indicate that the article is recent.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the article is old.
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => $this->faker->dateTimeBetween('-1 year', '-1 month'),
        ]);
    }
}
