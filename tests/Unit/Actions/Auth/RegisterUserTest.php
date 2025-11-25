<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\RegisterUser;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class RegisterUserTest extends TestCase
{
    private $userRepository;

    private RegisterUser $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->action = new RegisterUser($this->userRepository);
        Log::shouldReceive('error')->byDefault();
    }

    public function test_execute_creates_user_successfully()
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')->once()->andReturn(true);
        $request->name = 'John Doe';
        $request->email = 'john@example.com';
        $request->password = 'password123';

        // Mock user with createToken method
        $user = Mockery::mock(User::class);
        $user->shouldReceive('createToken')
            ->with('auth-token')
            ->andReturn((object) ['plainTextToken' => 'fake-token']);

        // Mock the repository to return the mocked user
        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->with([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'password123',
            ])
            ->andReturn($user);

        // Mock DB transaction to execute the callback
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        // Act
        $result = $this->action->execute($request);

        // Assert
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('user', $result['data']);
        $this->assertArrayHasKey('token', $result['data']);
        $this->assertEquals('User registered successfully', $result['message']);
    }

    public function test_execute_throws_validation_exception_for_invalid_data()
    {
        // Arrange
        $request = Request::create('/register', 'POST', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->action->execute($request);
    }

    public function test_execute_throws_exception_when_repository_fails()
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Create a mock request that bypasses validation
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')->once()->andReturn(true);
        $request->name = 'John Doe';
        $request->email = 'john@example.com';
        $request->password = 'password123';
        $request->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->andThrow(new Exception('Database error'));

        // Mock DB transaction to execute the callback
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to register user. Please try again later.');
        $this->action->execute($request);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
