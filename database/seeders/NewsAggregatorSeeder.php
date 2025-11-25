<?php

namespace Database\Seeders;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\SourceRepositoryInterface;
use Illuminate\Database\Seeder;

class NewsAggregatorSeeder extends Seeder
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private SourceRepositoryInterface $sourceRepository
    ) {}

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Technology',
            'Business',
            'Health',
            'Science',
            'Sports',
            'Entertainment',
            'Politics',
            'World',
        ];

        foreach ($categories as $category) {
            $this->categoryRepository->findOrCreateBySlug(
                strtolower($category),
                $category
            );
        }

        $categories = ['technology', 'business', 'science', 'sports', 'entertainment'];

        // NewsAPI - supports multiple categories
        $this->sourceRepository->findOrCreateByApiSlug('newsapi', [
            'name' => 'NewsAPI',
            'api_slug' => 'newsapi',
            'is_active' => true,
            'config' => [
                'categories' => $categories,
                'language' => 'en',
                'days' => 30,
            ],
        ]);

        // The Guardian - single source for all sections
        $this->sourceRepository->findOrCreateByApiSlug('guardian', [
            'name' => 'The Guardian',
            'api_slug' => 'guardian',
            'is_active' => true,
            'config' => [
                'sections' => $categories,
            ],
        ]);

        // BBC RSS sources removed - using main API sources instead

        // NY Times - single source for all sections
        $this->sourceRepository->findOrCreateByApiSlug('nytimes', [
            'name' => 'The New York Times',
            'api_slug' => 'nytimes',
            'is_active' => true,
            'config' => [
                'sections' => ['home', 'technology', 'business', 'science', 'sports', 'arts', 'world', 'politics'],
                'period' => 7, // For most popular API (1, 7, or 30 days)
            ],
        ]);
    }
}
