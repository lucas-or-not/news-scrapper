<?php

namespace Tests\Unit\Actions;

use App\Actions\Categories\GetCategories;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class GetCategoriesTest extends TestCase
{
    private $categoryRepository;

    private GetCategories $action;

    private Request $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
        $this->action = new GetCategories($this->categoryRepository);
        $this->request = new Request;

        // Mock Log facade to prevent file system issues
        Log::shouldReceive('error')->byDefault();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_returns_success_with_categories()
    {
        // Arrange
        $categories = new Collection([
            (object) ['id' => 1, 'name' => 'Technology', 'slug' => 'technology'],
            (object) ['id' => 2, 'name' => 'Sports', 'slug' => 'sports'],
        ]);

        $this->categoryRepository
            ->shouldReceive('getAllOrdered')
            ->once()
            ->andReturn($categories);

        // Act
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($categories, $result['data']);
        $this->assertCount(2, $result['data']);
    }

    public function test_execute_returns_empty_collection_when_no_categories()
    {
        // Arrange
        $categories = new Collection;

        $this->categoryRepository
            ->shouldReceive('getAllOrdered')
            ->once()
            ->andReturn($categories);

        // Act
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($categories, $result['data']);
        $this->assertCount(0, $result['data']);
    }

    public function test_execute_handles_repository_exception()
    {
        // Arrange
        $this->categoryRepository
            ->shouldReceive('getAllOrdered')
            ->once()
            ->andThrow(new Exception('Database connection failed'));

        // Act
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Failed to retrieve categories', $result['error']);
    }

    public function test_execute_logs_error_on_exception()
    {
        // Arrange
        $exception = new Exception('Database error');

        $this->categoryRepository
            ->shouldReceive('getAllOrdered')
            ->once()
            ->andThrow($exception);

        Log::shouldReceive('error')
            ->once()
            ->with('Failed to get categories', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

        // Act
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertFalse($result['success']);
    }
}
