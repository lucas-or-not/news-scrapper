<?php

namespace Tests\Unit\Actions;

use App\Actions\Articles\SearchArticles;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class SearchArticlesTest extends TestCase
{
    private $articleRepository;

    private $searchArticles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->articleRepository = Mockery::mock(ArticleRepositoryInterface::class);
        $this->searchArticles = new SearchArticles($this->articleRepository);
        Log::shouldReceive('error')->byDefault();
    }

    public function test_successful_search_with_keyword()
    {
        $request = Request::create('/', 'GET', [
            'keyword' => 'technology',
            'page' => 1,
            'per_page' => 10,
        ]);

        $mockArticles = new LengthAwarePaginator(
            [
                ['id' => 1, 'title' => 'Tech Article 1'],
                ['id' => 2, 'title' => 'Tech Article 2'],
            ],
            2, // total
            10, // per page
            1, // current page
            ['path' => request()->url()]
        );

        $this->articleRepository
            ->shouldReceive('search')
            ->with($request)
            ->once()
            ->andReturn($mockArticles);

        $result = $this->searchArticles->execute($request);

        $this->assertEquals($mockArticles, $result);
    }

    public function test_search_with_filters()
    {
        $request = Request::create('/', 'GET', [
            'keyword' => 'news',
            'source_id' => 1,
            'category_id' => 2,
            'author_id' => 3,
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
        ]);

        $expectedFilters = [
            'keyword' => 'news',
            'source_id' => 1,
            'category_id' => 2,
            'author_id' => 3,
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'page' => 1,
            'per_page' => 20,
        ];

        $mockResult = new LengthAwarePaginator(
            [],
            0,
            20,
            1,
            ['path' => request()->url()]
        );

        $this->articleRepository
            ->shouldReceive('search')
            ->with($request)
            ->once()
            ->andReturn($mockResult);

        $result = $this->searchArticles->execute($request);

        $this->assertEquals($mockResult, $result);
    }

    public function test_search_with_invalid_date_format()
    {
        $request = Request::create('/', 'GET', [
            'keyword' => 'news',
            'from_date' => 'invalid-date',
        ]);

        $this->expectException(ValidationException::class);
        $this->searchArticles->execute($request);
    }

    public function test_search_with_repository_exception()
    {
        $request = Request::create('/', 'GET', [
            'keyword' => 'technology',
        ]);

        $this->articleRepository
            ->shouldReceive('search')
            ->andThrow(new Exception('Database error'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Search failed. Please try again later.');
        $this->searchArticles->execute($request);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
