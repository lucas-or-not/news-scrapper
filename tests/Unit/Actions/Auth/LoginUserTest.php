<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\LoginUser;
use App\Repositories\Contracts\UserRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class LoginUserTest extends TestCase
{
    private $userRepository;

    private $loginUser;

    protected function setUp(): void
    {
        parent::setUp();
        Log::shouldReceive('error')->byDefault();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->loginUser = new LoginUser($this->userRepository);
    }

    public function test_successful_login()
    {
        $request = Request::create('/', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $mockUser = (object) [
            'id' => 1,
            'email' => 'test@example.com',
            'name' => 'Test User',
        ];

        $this->userRepository
            ->shouldReceive('findByCredentials')
            ->with('test@example.com', 'password123')
            ->once()
            ->andReturn($mockUser);

        $this->userRepository
            ->shouldReceive('createToken')
            ->with($mockUser)
            ->once()
            ->andReturn('test-token');

        $result = $this->loginUser->execute($request);

        $this->assertTrue(str_contains($result['message'], 'User logged in successfully'));
        $this->assertEquals('test-token', $result['data']['token']);
        $this->assertEquals($mockUser, $result['data']['user']);
    }

    public function test_login_with_invalid_credentials()
    {
        $request = Request::create('/', 'POST', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->userRepository
            ->shouldReceive('findByCredentials')
            ->with('test@example.com', 'wrongpassword')
            ->once()
            ->andReturn(null);

        $this->expectException(ValidationException::class);
        $this->loginUser->execute($request);
    }

    public function test_login_with_missing_email()
    {
        $request = Request::create('/', 'POST', [
            'password' => 'password123',
        ]);

        $this->expectException(ValidationException::class);
        $this->loginUser->execute($request);
    }

    public function test_login_with_repository_exception()
    {
        $request = Request::create('/', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->userRepository
            ->shouldReceive('findByCredentials')
            ->andThrow(new Exception('Database error'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Login failed. Please try again later.');
        $this->loginUser->execute($request);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
