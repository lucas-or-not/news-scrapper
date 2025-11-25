<?php

namespace App\Jobs;

use App\Models\Source;
use App\Services\NewsFetchers\NewsFetcherFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchSourceArticlesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Source $source
    ) {
        $this->onQueue('fetch-articles');
    }

    public function handle(NewsFetcherFactory $factory = null): void
    {
        Log::info("Starting to fetch articles from: {$this->source->name}");

        $factory = $factory ?: app(NewsFetcherFactory::class);
        $fetcher = $factory->create($this->source);
        if (! $fetcher) {
            Log::error("Failed to create fetcher for: {$this->source->name}");
            return;
        }

        try {
            $articles = $fetcher->fetchArticles();
            Log::info('Found '.count($articles)." articles from {$this->source->name}");

            foreach ($articles as $articleData) {
                ProcessArticleJob::dispatch($articleData, $this->source->id);
            }

            Log::info("Successfully queued ".count($articles)." articles from {$this->source->name}");

        } catch (\Exception $e) {
            Log::error("Error fetching from {$this->source->name}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("FetchSourceArticlesJob failed for {$this->source->name}", [
            'source_id' => $this->source->id,
            'error' => $exception->getMessage(),
        ]);
    }
}