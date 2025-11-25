<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\FetchArticlesCommand;
use App\Jobs\FetchSourceArticlesJob;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FetchArticlesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_dispatches_jobs_for_active_sources()
    {
        Queue::fake();

        $activeSource1 = Source::factory()->create(['is_active' => true, 'name' => 'Active Source 1']);
        $activeSource2 = Source::factory()->create(['is_active' => true, 'name' => 'Active Source 2']);
        $inactiveSource = Source::factory()->create(['is_active' => false, 'name' => 'Inactive Source']);

        $this->artisan('fetch:articles')
            ->expectsOutput('Starting article fetch process...')
            ->expectsOutput('Found 2 active sources.')
            ->expectsOutput('Dispatching fetch job for: Active Source 1')
            ->expectsOutput('Dispatching fetch job for: Active Source 2')
            ->expectsOutput('Dispatched 2 fetch jobs. Check the queue for processing status.')
            ->assertExitCode(0);

        // Assert that jobs were dispatched for active sources only
        Queue::assertPushed(FetchSourceArticlesJob::class, 2);
        Queue::assertPushed(FetchSourceArticlesJob::class, function ($job) use ($activeSource1) {
            return $job->source->id === $activeSource1->id;
        });
        Queue::assertPushed(FetchSourceArticlesJob::class, function ($job) use ($activeSource2) {
            return $job->source->id === $activeSource2->id;
        });
    }

    public function test_command_handles_no_active_sources()
    {
        Queue::fake();

        Source::factory()->create(['is_active' => false]);

        $this->artisan('fetch:articles')
            ->expectsOutput('Starting article fetch process...')
            ->expectsOutput('No active sources found.')
            ->assertExitCode(0);

        Queue::assertNothingPushed();
    }

    public function test_command_filters_by_source_option()
    {
        Queue::fake();

        $source1 = Source::factory()->create(['is_active' => true, 'api_slug' => 'source-1', 'name' => 'Source 1']);
        $source2 = Source::factory()->create(['is_active' => true, 'api_slug' => 'source-2', 'name' => 'Source 2']);

        $this->artisan('fetch:articles', ['--source' => 'source-1'])
            ->expectsOutput('Starting article fetch process...')
            ->expectsOutput('Found 1 active sources.')
            ->expectsOutput('Dispatching fetch job for: Source 1')
            ->expectsOutput('Dispatched 1 fetch jobs. Check the queue for processing status.')
            ->assertExitCode(0);

        // Assert that only the specified source job was dispatched
        Queue::assertPushed(FetchSourceArticlesJob::class, 1);
        Queue::assertPushed(FetchSourceArticlesJob::class, function ($job) use ($source1) {
            return $job->source->id === $source1->id;
        });
    }
}