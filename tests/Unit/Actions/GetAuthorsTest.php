<?php

namespace Tests\Unit\Actions;

use App\Actions\Authors\GetAuthors;
use App\Repositories\Contracts\AuthorRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class GetAuthorsTest extends TestCase
{
    private $authorRepository;

    private GetAuthors $action;

    private Request $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authorRepository = Mockery::mock(AuthorRepositoryInterface::class);
        $this->action = new GetAuthors($this->authorRepository);
        $this->request = new Request;

        // Mock Log facade to prevent file system issues
        Log::shouldReceive('error')->byDefault();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_returns_success_with_authors()
    {
        // Arrange
        $authors = new Collection([
            (object) ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            (object) ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
        ]);

        $this->authorRepository
            ->shouldReceive('getAllOrdered')
            ->once()
            ->andReturn($authors);

        // Act
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($authors, $result['data']);
        $this->assertCount(2, $result['data']);
    }

    public function test_execute_returns_empty_collection_when_no_authors()
    {
        // Arrange
        $authors = new Collection;

        $this->authorRepository
            ->shouldReceive('getAllOrdered')
            ->once()
            ->andReturn($authors);

        // Act
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($authors, $result['data']);
        $this->assertCount(0, $result['data']);
    }

    public function test_execute_handles_repository_exception()
    {
        // Arrange
        $this->authorRepository
            ->shouldReceive('getAllOrdered')
            ->once()
            ->andThrow(new Exception('Database connection failed'));

        // Act
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Failed to retrieve authors', $result['error']);
    }

    public function test_execute_logs_error_on_exception()
    {
        // Arrange
        $exception = new Exception('Database error');

        $this->authorRepository
            ->shouldReceive('getAllOrdered')
            ->once()
            ->andThrow($exception);

        Log::shouldReceive('error')
            ->once()
            ->with('Failed to get authors', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

        // Act
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertFalse($result['success']);
    }
}
