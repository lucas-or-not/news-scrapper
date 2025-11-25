<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\LogoutUser;
use App\Repositories\Contracts\UserRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class LogoutUserTest extends TestCase
{
    private $userRepository;

    private $logoutUser;

    protected function setUp(): void
    {
        parent::setUp();
        Log::shouldReceive('error')->byDefault();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->logoutUser = new LogoutUser($this->userRepository);
    }

    public function test_successful_logout()
    {
        $mockUser = (object) [
            'id' => 1,
            'email' => 'test@example.com',
            'name' => 'Test User',
        ];

        $request = Request::create('/', 'POST');
        $request->setUserResolver(function () use ($mockUser) {
            return $mockUser;
        });

        $this->userRepository
            ->shouldReceive('revokeTokens')
            ->with($mockUser)
            ->once()
            ->andReturn(true);

        $result = $this->logoutUser->execute($request);

        $this->assertTrue($result['success']);
        $this->assertEquals('Logout successful', $result['message']);
    }

    public function test_logout_with_repository_exception()
    {
        $mockUser = (object) [
            'id' => 1,
            'email' => 'test@example.com',
            'name' => 'Test User',
        ];

        $request = Request::create('/', 'POST');
        $request->setUserResolver(function () use ($mockUser) {
            return $mockUser;
        });

        $this->userRepository
            ->shouldReceive('revokeTokens')
            ->andThrow(new Exception('Database error'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Logout failed. Please try again later.');
        $this->logoutUser->execute($request);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
