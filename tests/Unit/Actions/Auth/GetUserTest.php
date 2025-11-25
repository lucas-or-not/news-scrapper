<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\GetUser;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class GetUserTest extends TestCase
{
    private $userRepository;

    private GetUser $action;

    private $request;

    protected function setUp(): void
    {
        parent::setUp();
        Log::shouldReceive('error')->byDefault();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->request = Mockery::mock(Request::class);
        $this->action = new GetUser($this->userRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_returns_success_with_authenticated_user()
    {
        // Arrange
        $user = new User;
        $user->id = 1;
        $user->name = 'John Doe';
        $user->email = 'john@example.com';

        $this->request
            ->shouldReceive('user')
            ->once()
            ->andReturn($user);

        // Act
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($user, $result['data']);
        $this->assertEquals('John Doe', $result['data']->name);
        $this->assertEquals('john@example.com', $result['data']->email);
    }

    public function test_execute_returns_error_when_user_not_authenticated()
    {
        // Arrange
        $this->request
            ->shouldReceive('user')
            ->once()
            ->andReturn(null);

        // Act
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('User not authenticated', $result['error']);
    }

    public function test_execute_handles_exception_during_user_retrieval()
    {
        // Arrange
        $this->request
            ->shouldReceive('user')
            ->once()
            ->andThrow(new Exception('Authentication service unavailable'));

        // Act
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Failed to retrieve user information', $result['error']);
    }

    public function test_execute_logs_error_on_exception()
    {
        // Arrange
        $exception = new Exception('Authentication error');

        $this->request
            ->shouldReceive('user')
            ->once()
            ->andThrow($exception);

        Log::shouldReceive('error')
            ->once()
            ->with('Failed to get user', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

        // Act
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertFalse($result['success']);
    }

    public function test_execute_with_user_id_retrieval_from_repository()
    {
        // Arrange
        $user = new User;
        $user->id = 1;
        $user->name = 'Jane Smith';
        $user->email = 'jane@example.com';

        $this->request
            ->shouldReceive('user')
            ->once()
            ->andReturn($user);

        $this->userRepository
            ->shouldReceive('findById')
            ->with(1)
            ->once()
            ->andReturn($user);

        // Act - testing with additional repository call
        $freshUser = $this->userRepository->findById($user->id);
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals($user, $result['data']);
        $this->assertEquals($user, $freshUser);
    }
}
