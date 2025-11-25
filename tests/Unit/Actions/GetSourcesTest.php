<?php

namespace Tests\Unit\Actions;

use App\Actions\Sources\GetSources;
use App\Repositories\Contracts\SourceRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class GetSourcesTest extends TestCase
{
    private $sourceRepository;

    private GetSources $action;

    private Request $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sourceRepository = Mockery::mock(SourceRepositoryInterface::class);
        $this->action = new GetSources($this->sourceRepository);
        $this->request = new Request;

        // Mock Log facade to prevent file system issues
        Log::shouldReceive('error')->byDefault();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_returns_success_with_sources()
    {
        // Arrange
        $sources = new Collection([
            (object) ['id' => 1, 'name' => 'BBC News', 'is_active' => true],
            (object) ['id' => 2, 'name' => 'CNN', 'is_active' => true],
        ]);

        $this->sourceRepository
            ->shouldReceive('getActiveSources')
            ->once()
            ->andReturn($sources);

        // Act
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($sources, $result['data']);
        $this->assertCount(2, $result['data']);
    }

    public function test_execute_returns_empty_collection_when_no_sources()
    {
        // Arrange
        $sources = new Collection;

        $this->sourceRepository
            ->shouldReceive('getActiveSources')
            ->once()
            ->andReturn($sources);

        // Act
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($sources, $result['data']);
        $this->assertCount(0, $result['data']);
    }

    public function test_execute_handles_repository_exception()
    {
        // Arrange
        $this->sourceRepository
            ->shouldReceive('getActiveSources')
            ->once()
            ->andThrow(new Exception('Database connection failed'));

        // Act
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Failed to retrieve sources', $result['error']);
    }

    public function test_execute_logs_error_on_exception()
    {
        // Arrange
        $exception = new Exception('Database error');

        $this->sourceRepository
            ->shouldReceive('getActiveSources')
            ->once()
            ->andThrow($exception);

        \Log::shouldReceive('error')
            ->once()
            ->with('Failed to get sources', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

        // Act
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertFalse($result['success']);
    }
}
