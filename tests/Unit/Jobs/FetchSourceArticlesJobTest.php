<?php

namespace Tests\Unit\Jobs;

use App\Jobs\FetchSourceArticlesJob;
use App\Jobs\ProcessArticleJob;
use App\Models\Source;
use App\Services\NewsFetchers\NewsFetcherFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FetchSourceArticlesJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_dispatches_process_article_jobs_for_fetched_articles()
    {
        Queue::fake();

        $source = Source::factory()->create([
            'name' => 'Test Source',
            'api_slug' => 'test-source',
            'is_active' => true,
        ]);

        // Mock the fetcher to return test articles
        $mockFetcher = $this->createMock(\App\Services\NewsFetchers\NewsFetcherInterface::class);
        $mockFetcher->method('fetchArticles')->willReturn([
            ['title' => 'Article 1', 'url' => 'https://example.com/1'],
            ['title' => 'Article 2', 'url' => 'https://example.com/2'],
        ]);

        // Mock the factory to return our mock fetcher
        $mockFactory = $this->mock(NewsFetcherFactory::class, function ($mock) use ($mockFetcher) {
            $mock->shouldReceive('create')->once()->andReturn($mockFetcher);
        });

        // Dispatch the job
        $job = new FetchSourceArticlesJob($source);
        $job->handle($mockFactory);

        // Assert that ProcessArticleJob was dispatched for each article
        Queue::assertPushed(ProcessArticleJob::class, 2);
    }

    public function test_job_handles_fetcher_creation_failure()
    {
        Queue::fake();

        $source = Source::factory()->create();

        // Mock the factory to return null (failure)
        $mockFactory = $this->mock(NewsFetcherFactory::class, function ($mock) {
            $mock->shouldReceive('create')->once()->andReturn(null);
        });

        $job = new FetchSourceArticlesJob($source);
        $job->handle($mockFactory);

        // Assert that no ProcessArticleJob was dispatched
        Queue::assertNotPushed(ProcessArticleJob::class);
    }

    public function test_job_handles_fetch_exception()
    {
        Queue::fake();

        $source = Source::factory()->create();

        // Mock the fetcher to throw an exception
        $mockFetcher = $this->createMock(\App\Services\NewsFetchers\NewsFetcherInterface::class);
        $mockFetcher->method('fetchArticles')->willThrowException(new \Exception('Fetch failed'));

        $mockFactory = $this->mock(NewsFetcherFactory::class, function ($mock) use ($mockFetcher) {
            $mock->shouldReceive('create')->once()->andReturn($mockFetcher);
        });

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Fetch failed');

        $job = new FetchSourceArticlesJob($source);
        $job->handle($mockFactory);
    }
}