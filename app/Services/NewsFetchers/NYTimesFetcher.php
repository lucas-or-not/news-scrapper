<?php

namespace App\Services\NewsFetchers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NYTimesFetcher extends AbstractNewsFetcher
{
    public function fetchArticles(): array
    {
        $apiKey = config('news.api_keys.nytimes');
        if (! $apiKey) {
            Log::error("No API key configured for NY Times source: {$this->source->name}");

            return [];
        }

        $articles = [];
        $sections = $this->config['sections'] ?? ['home'];

        // Fetch from each section
        foreach ($sections as $section) {
            // Fetch from Top Stories API for this section
            $topStoriesArticles = $this->fetchTopStories($apiKey, $section);
            if (! empty($topStoriesArticles)) {
                $articles = array_merge($articles, $topStoriesArticles);
            }
        }

        // Fetch from Most Popular API (not section-specific)
        $mostPopularArticles = $this->fetchMostPopular($apiKey);
        if (! empty($mostPopularArticles)) {
            $articles = array_merge($articles, $mostPopularArticles);
        }

        if (empty($articles)) {
            return [];
        }

        // Deduplicate by URL
        $seen = [];
        $deduped = [];
        foreach ($articles as $article) {
            $url = $article['url'] ?? null;
            if ($url && ! isset($seen[$url])) {
                $seen[$url] = true;
                $deduped[] = $article;
            }
        }

        return array_map([$this, 'normalizeArticle'], $deduped);
    }

    protected function fetchTopStories(string $apiKey, string $section = 'home'): array
    {
        $url = "https://api.nytimes.com/svc/topstories/v2/{$section}.json";

        $params = [
            'api-key' => $apiKey,
        ];

        $response = $this->makeRequest($url, $params);

        if (isset($response['results']) && is_array($response['results'])) {
            // Tag articles with source type and section for identification
            return array_map(function ($article) use ($section) {
                $article['_source_type'] = 'top_stories';
                $article['_section'] = $section;

                return $article;
            }, $response['results']);
        }

        return [];
    }

    protected function fetchMostPopular(string $apiKey): array
    {
        $period = $this->config['period'] ?? 7; // 1, 7, or 30 days
        $url = "https://api.nytimes.com/svc/mostpopular/v2/viewed/{$period}.json";

        $params = [
            'api-key' => $apiKey,
        ];

        $response = $this->makeRequest($url, $params);

        if (isset($response['results']) && is_array($response['results'])) {
            // Tag articles with source type for identification
            return array_map(function ($article) {
                $article['_source_type'] = 'most_popular';

                return $article;
            }, $response['results']);
        }

        return [];
    }

    public function getSourceSlug(): string
    {
        return 'nytimes';
    }

    protected function extractSourceArticleId(array $rawArticle): string
    {
        return $rawArticle['url'] ?? uniqid('nytimes_');
    }

    protected function extractTitle(array $rawArticle): string
    {
        return $rawArticle['title'] ?? 'No title';
    }

    protected function extractContent(array $rawArticle): string
    {
        return $rawArticle['abstract'] ?? '';
    }

    protected function extractExcerpt(array $rawArticle): ?string
    {
        return $rawArticle['abstract'] ?? null;
    }

    protected function extractUrl(array $rawArticle): string
    {
        return $rawArticle['url'] ?? '';
    }

    protected function extractAuthor(array $rawArticle): ?string
    {
        if (isset($rawArticle['byline']) && ! empty($rawArticle['byline'])) {
            return preg_replace('/^By\s+/i', '', $rawArticle['byline']);
        }

        return 'The New York Times';
    }

    protected function extractCategory(array $rawArticle): ?string
    {
        $section = $rawArticle['section'] ?? $rawArticle['_section'] ?? null;

        if ($section) {
            $category = \App\Models\Category::firstOrCreate(
                ['slug' => strtolower($section)],
                ['name' => ucfirst($section)]
            );

            return $category->name;
        }

        return null;
    }

    protected function extractPublishedAt(array $rawArticle): string
    {
        $publishedAt = $rawArticle['published_date'] ?? $rawArticle['created_date'] ?? null;

        if ($publishedAt) {
            try {
                return Carbon::parse($publishedAt)->toISOString();
            } catch (\Exception $e) {
                Log::warning("Failed to parse NY Times date: {$publishedAt}");
            }
        }

        return now()->toISOString();
    }

    protected function extractImageUrl(array $rawArticle): ?string
    {
        if (isset($rawArticle['multimedia']) && is_array($rawArticle['multimedia'])) {
            foreach ($rawArticle['multimedia'] as $media) {
                if (isset($media['url']) && isset($media['format'])) {
                    if (in_array($media['format'], ['superJumbo', 'jumbo', 'articleLarge', 'Normal'])) {
                        return $media['url'];
                    }
                }
            }

            if (! empty($rawArticle['multimedia'][0]['url'])) {
                return $rawArticle['multimedia'][0]['url'];
            }
        }

        return null;
    }
}
