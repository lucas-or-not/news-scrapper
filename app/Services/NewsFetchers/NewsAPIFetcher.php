<?php

namespace App\Services\NewsFetchers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NewsAPIFetcher extends AbstractNewsFetcher
{
    public function fetchArticles(): array
    {
        $apiKey = config('news.api_keys.newsapi');
        if (! $apiKey) {
            Log::error("No API key configured for NewsAPI source: {$this->source->name}");

            return [];
        }

        $language = $this->config['language'] ?? 'en';
        $query = $this->config['q'] ?? 'news OR technology OR science';

       
        $articles = [];

        // 1) Top headlines when category present
        if (isset($this->config['category'])) {
            $topHeadlinesParams = [
                'apiKey' => $apiKey,
                'category' => $this->config['category'],
                'country' => $this->config['country'] ?? 'us',
                'q' => $query,
                'pageSize' => 50,
            ];
            $headlines = $this->makeRequest('https://newsapi.org/v2/top-headlines', $topHeadlinesParams);
            if (! empty($headlines['articles'])) {
                $articles = array_merge($articles, $headlines['articles']);
            }
        }

        $days = (int) ($this->config['days'] ?? 30);
        $fromDate = now()->subDays(max(1, $days))->toDateString();
        $toDate = now()->toDateString();

        $everythingParams = [
            'apiKey' => $apiKey,
            'language' => $language,
            'q' => $query,
            'from' => $fromDate,
            'to' => $toDate,
            'sortBy' => 'publishedAt',
            'pageSize' => 50,
        ];
        $everything = $this->makeRequest('https://newsapi.org/v2/everything', $everythingParams);
        if (! empty($everything['articles'])) {
            $articles = array_merge($articles, $everything['articles']);
        }

        if (empty($articles)) {
            return [];
        }

        // Deduplicate by URL
        $seen = [];
        $deduped = [];
        foreach ($articles as $a) {
            $u = $a['url'] ?? null;
            if ($u && ! isset($seen[$u])) {
                $seen[$u] = true;
                $deduped[] = $a;
            }
        }

        return array_map([$this, 'normalizeArticle'], $deduped);
    }

    public function getSourceSlug(): string
    {
        return 'newsapi';
    }

    protected function extractSourceArticleId(array $rawArticle): string
    {
        return $rawArticle['url'] ?? uniqid('newsapi_');
    }

    protected function extractTitle(array $rawArticle): string
    {
        return $rawArticle['title'] ?? 'No title';
    }

    protected function extractContent(array $rawArticle): string
    {
        return $rawArticle['content'] ?? $rawArticle['description'] ?? '';
    }

    protected function extractExcerpt(array $rawArticle): ?string
    {
        return $rawArticle['description'] ?? null;
    }

    protected function extractUrl(array $rawArticle): string
    {
        return $rawArticle['url'] ?? '';
    }

    protected function extractAuthor(array $rawArticle): ?string
    {
        return $rawArticle['author'] ?? ($rawArticle['source']['name'] ?? null);
    }

    protected function extractCategory(array $rawArticle): ?string
    {
        if (isset($this->config['category'])) {
            return $this->config['category'];
        }

        $category = \App\Models\Category::firstOrCreate(
            ['slug' => 'general'],
            ['name' => 'General']
        );

        return $category->name;
    }

    protected function extractPublishedAt(array $rawArticle): string
    {
        try {
            return Carbon::parse($rawArticle['publishedAt'])->toDateTimeString();
        } catch (\Exception $e) {
            return now()->toDateTimeString();
        }
    }

    protected function extractImageUrl(array $rawArticle): ?string
    {
        return $rawArticle['urlToImage'] ?? null;
    }
}
