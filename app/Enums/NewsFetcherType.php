<?php

namespace App\Enums;

use App\Services\NewsFetchers\GuardianFetcher;
use App\Services\NewsFetchers\NewsAPIFetcher;
use App\Services\NewsFetchers\NYTimesFetcher;

enum NewsFetcherType: string
{
    case NEWSAPI = 'newsapi';
    case GUARDIAN = 'guardian';
    case NYTIMES = 'nytimes';

    public function getFetcherClass(): string
    {
        return match ($this) {
            self::NEWSAPI => NewsAPIFetcher::class,
            self::GUARDIAN => GuardianFetcher::class,
            self::NYTIMES => NYTimesFetcher::class,
        };
    }

    public static function fromString(string $value): ?self
    {
        return self::tryFrom($value);
    }

    public function getDisplayName(): string
    {
        return match ($this) {
            self::NEWSAPI => 'News API',
            self::GUARDIAN => 'The Guardian',
            self::NYTIMES => 'New York Times',
        };
    }
}