<?php

namespace App\Services\NewsFetchers;

use App\Enums\NewsFetcherType;
use App\Models\Source;
use Illuminate\Support\Facades\Log;

class NewsFetcherFactory
{
    public static function create(Source $source): ?NewsFetcherInterface
    {
        $slug = $source->api_slug;

        $fetcherType = NewsFetcherType::fromString($slug);
        if (! $fetcherType) {
            Log::error("No fetcher found for source slug: {$slug}");
            return null;
        }

        $fetcherClass = $fetcherType->getFetcherClass();

        try {
            return new $fetcherClass($source);
        } catch (\Exception $e) {
            Log::error("Failed to create fetcher for {$slug}: {$e->getMessage()}");
            return null;
        }
    }
}
