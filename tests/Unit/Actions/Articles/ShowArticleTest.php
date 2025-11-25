<?php

namespace Tests\Unit\Actions\Articles;

use App\Actions\Articles\ShowArticle;
use App\Models\Article;
use App\Models\User;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class ShowArticleTest extends TestCase
{
    private $articleRepository;

    private ShowArticle $action;

    private $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->articleRepository = Mockery::mock(ArticleRepositoryInterface::class);
        $this->request = Mockery::mock(Request::class);
        $this->action = new ShowArticle($this->articleRepository);
        Log::shouldReceive('error')->byDefault();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_returns_error_when_article_not_found()
    {
        // Arrange
        $articleId = 999;
        $userId = 123;

        $this->articleRepository
            ->shouldReceive('findWithRelations')
            ->with($articleId, ['source', 'author', 'category'])
            ->once()
            ->andReturn(null);

        $this->request
            ->shouldReceive('user')
            ->never();

        // Act
        $result = $this->action->execute($this->request, $articleId);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Article not found', $result['error']);
    }

    public function test_execute_handles_repository_exception()
    {
        // Arrange
        $articleId = 1;
        $userId = 123;

        $this->articleRepository
            ->shouldReceive('findWithRelations')
            ->with($articleId, ['source', 'author', 'category'])
            ->once()
            ->andThrow(new Exception('Database connection failed'));

        $this->request
            ->shouldReceive('user')
            ->never();

        // Act
        $result = $this->action->execute($this->request, $articleId);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Failed to retrieve article', $result['error']);
    }

    public function test_execute_logs_error_on_exception()
    {
        // Arrange
        $articleId = 1;
        $userId = 123;
        $exception = new Exception('Database error');

        $this->articleRepository
            ->shouldReceive('findWithRelations')
            ->with($articleId, ['source', 'author', 'category'])
            ->once()
            ->andThrow($exception);

        $this->request
            ->shouldReceive('user')
            ->never();

        \Log::shouldReceive('error')
            ->once()
            ->with('Failed to show article', [
                'article_id' => $articleId,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

        // Act
        $result = $this->action->execute($this->request, $articleId);

        // Assert
        $this->assertFalse($result['success']);
    }
}
