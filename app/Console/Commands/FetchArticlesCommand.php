<?php

namespace App\Console\Commands;

use App\Jobs\FetchSourceArticlesJob;
use App\Models\Source;
use Illuminate\Console\Command;

class FetchArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:articles {--source= : Fetch from specific source}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch articles from all active news sources';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting article fetch process...');

        $sources = $this->getSources();

        if ($sources->isEmpty()) {
            $this->warn('No active sources found.');
            return;
        }

        $this->info("Found {$sources->count()} active sources.");

        foreach ($sources as $source) {
            $this->info("Dispatching fetch job for: {$source->name}");
            FetchSourceArticlesJob::dispatch($source);
        }

        $this->info("Dispatched {$sources->count()} fetch jobs. Check the queue for processing status.");
    }

    protected function getSources()
    {
        $query = Source::where('is_active', true);

        if ($sourceSlug = $this->option('source')) {
            $query->where('api_slug', $sourceSlug);
        }

        return $query->get();
    }
}
