<?php

namespace App\Services\NewsFetchers;

interface NewsFetcherInterface
{
    /**
     * Fetch articles from the news source
     *
     * @return array Array of normalized article data
     */
    public function fetchArticles(): array;

    /**
     * Get the source identifier
     */
    public function getSourceSlug(): string;
}
