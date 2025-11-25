<?php

namespace Tests\Unit\Actions\UserPreferences;

use App\Actions\UserPreferences\GetFeed;
use App\Repositories\Contracts\UserPreferenceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class GetFeedTest extends TestCase
{
    private $userPreferenceRepository;

    private GetFeed $action;

    private $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userPreferenceRepository = Mockery::mock(UserPreferenceRepositoryInterface::class);
        $this->request = Mockery::mock(Request::class);
        $this->action = new GetFeed($this->userPreferenceRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_returns_personalized_feed_with_default_pagination()
    {
        // Arrange
        $userId = 123;
        $page = 1;
        $perPage = 10;
        $paginatedFeed = Mockery::mock(LengthAwarePaginator::class);

        $this->request
            ->shouldReceive('validate')
            ->once()
            ->with([
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:100',
            ]);

        $this->request
            ->shouldReceive('get')
            ->once()
            ->with('page', 1)
            ->andReturn($page);

        $this->request
            ->shouldReceive('get')
            ->once()
            ->with('per_page', 10)
            ->andReturn($perPage);

        Auth::shouldReceive('id')
            ->once()
            ->andReturn($userId);

        $this->userPreferenceRepository
            ->shouldReceive('getPersonalizedFeed')
            ->once()
            ->with($userId, $perPage)
            ->andReturn($paginatedFeed);

        // Act
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertSame($paginatedFeed, $result);
    }

    public function test_execute_returns_personalized_feed_with_custom_pagination()
    {
        // Arrange
        $userId = 123;
        $page = 2;
        $perPage = 25;
        $paginatedFeed = Mockery::mock(LengthAwarePaginator::class);

        $this->request
            ->shouldReceive('validate')
            ->once()
            ->with([
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:100',
            ]);

        $this->request
            ->shouldReceive('get')
            ->once()
            ->with('page', 1)
            ->andReturn($page);

        $this->request
            ->shouldReceive('get')
            ->once()
            ->with('per_page', 10)
            ->andReturn($perPage);

        Auth::shouldReceive('id')
            ->once()
            ->andReturn($userId);

        $this->userPreferenceRepository
            ->shouldReceive('getPersonalizedFeed')
            ->once()
            ->with($userId, $perPage)
            ->andReturn($paginatedFeed);

        // Act
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertSame($paginatedFeed, $result);
    }
}
