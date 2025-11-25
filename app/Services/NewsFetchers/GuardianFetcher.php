<?php

namespace App\Services\NewsFetchers;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GuardianFetcher extends AbstractNewsFetcher
{
    public function fetchArticles(): array
    {
        $apiKey = config('news.api_keys.guardian');
        if (! $apiKey) {
            Log::error("No API key configured for Guardian source: {$this->source->name}");

            return [];
        }

        $sections = $this->config['sections'] ?? ['technology', 'business', 'science', 'sports', 'entertainment'];
        $allArticles = [];

        foreach ($sections as $section) {
            $articles = $this->fetchTopStories($section);
            $allArticles = array_merge($allArticles, $articles);
        }

        return $allArticles;
    }

    protected function fetchTopStories(string $section): array
    {
        $apiKey = config('news.api_keys.guardian');
        $url = 'https://content.guardianapis.com/search';
        $params = [
            'api-key' => $apiKey,
            'show-fields' => 'headline,byline,body,thumbnail,short-url',
            'show-tags' => 'contributor',
            'page-size' => 50,
            'order-by' => 'newest',
            'section' => $section,
        ];

        $response = $this->makeRequest($url, $params);

        if (empty($response['response']['results'])) {
            return [];
        }

        return array_map(function ($article) use ($section) {
            $article['_section'] = $section; // Add section info to article data

            return $this->normalizeArticle($article);
        }, $response['response']['results']);
    }

    public function getSourceSlug(): string
    {
        return 'guardian';
    }

    protected function extractSourceArticleId(array $rawArticle): string
    {
        return $rawArticle['id'] ?? uniqid('guardian_');
    }

    protected function extractTitle(array $rawArticle): string
    {
        return $rawArticle['fields']['headline'] ?? $rawArticle['webTitle'] ?? 'No title';
    }

    protected function extractContent(array $rawArticle): string
    {
        return $rawArticle['fields']['body'] ?? '';
    }

    protected function extractExcerpt(array $rawArticle): ?string
    {
        $body = $rawArticle['fields']['body'] ?? '';
        if ($body) {
            $stripped = strip_tags($body);

            return strlen($stripped) > 200 ? substr($stripped, 0, 200).'...' : $stripped;
        }

        return null;
    }

    protected function extractUrl(array $rawArticle): string
    {
        return $rawArticle['fields']['short-url'] ?? $rawArticle['webUrl'] ?? '';
    }

    protected function extractAuthor(array $rawArticle): ?string
    {
        if (! empty($rawArticle['fields']['byline'])) {
            return $rawArticle['fields']['byline'];
        }

        if (! empty($rawArticle['tags'])) {
            $contributors = array_filter($rawArticle['tags'], function ($tag) {
                return $tag['type'] === 'contributor';
            });

            if (! empty($contributors)) {
                return reset($contributors)['webTitle'];
            }
        }

        return null;
    }

    protected function extractCategory(array $rawArticle): ?string
    {
        $section = $rawArticle['_section'] ?? $rawArticle['sectionName'] ?? $this->config['section'] ?? 'general';

        $category = Category::firstOrCreate(
            ['slug' => strtolower($section)],
            ['name' => ucfirst($section)]
        );

        return $category->name;
    }

    protected function extractPublishedAt(array $rawArticle): string
    {
        try {
            return Carbon::parse($rawArticle['webPublicationDate'])->toDateTimeString();
        } catch (\Exception $e) {
            return now()->toDateTimeString();
        }
    }

    protected function extractImageUrl(array $rawArticle): ?string
    {
        return $rawArticle['fields']['thumbnail'] ?? null;
    }
}
