<?php

namespace App\Services\NewsFetchers;

use App\Models\Source;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class AbstractNewsFetcher implements NewsFetcherInterface
{
    protected Source $source;

    protected array $config;

    public function __construct(Source $source)
    {
        $this->source = $source;
        $this->config = $source->config ?? [];
    }

    /**
     * Make HTTP request with error handling and rate limiting
     */
    protected function makeRequest(string $url, array $params = []): array
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->get($url, $params);

            if ($response->successful()) {
                return $response->json();
            }

            if ($response->status() === 429) {
                Log::warning("Rate limit hit for source: {$this->source->name}");

                return [];
            }

            Log::error("Failed to fetch from {$this->source->name}: {$response->status()}");

            return [];

        } catch (\Exception $e) {
            Log::error("Exception fetching from {$this->source->name}: {$e->getMessage()}");

            return [];
        }
    }

    /**
     * Normalize article data to a consistent format
     */
    protected function normalizeArticle(array $rawArticle): array
    {
        return [
            'source_article_id' => $this->extractSourceArticleId($rawArticle),
            'title' => $this->extractTitle($rawArticle),
            'content' => $this->extractContent($rawArticle),
            'excerpt' => $this->extractExcerpt($rawArticle),
            'url' => $this->extractUrl($rawArticle),
            'author' => $this->extractAuthor($rawArticle),
            'category' => $this->extractCategory($rawArticle),
            'published_at' => $this->extractPublishedAt($rawArticle),
            'image_url' => $this->extractImageUrl($rawArticle),
            'raw_payload' => $rawArticle,
        ];
    }

    abstract protected function extractSourceArticleId(array $rawArticle): string;

    abstract protected function extractTitle(array $rawArticle): string;

    abstract protected function extractContent(array $rawArticle): string;

    abstract protected function extractExcerpt(array $rawArticle): ?string;

    abstract protected function extractUrl(array $rawArticle): string;

    abstract protected function extractAuthor(array $rawArticle): ?string;

    abstract protected function extractCategory(array $rawArticle): ?string;

    abstract protected function extractPublishedAt(array $rawArticle): string;

    abstract protected function extractImageUrl(array $rawArticle): ?string;
}
