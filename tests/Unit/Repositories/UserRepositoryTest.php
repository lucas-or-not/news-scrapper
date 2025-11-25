<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new UserRepository;
    }

    public function test_create_user_successfully()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $user = $this->userRepository->create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function test_find_by_credentials_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $foundUser = $this->userRepository->findByCredentials('test@example.com', 'password123');

        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($user->id, $foundUser->id);
        $this->assertEquals('test@example.com', $foundUser->email);
    }

    public function test_find_by_credentials_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $foundUser = $this->userRepository->findByCredentials('test@example.com', 'wrongpassword');

        $this->assertNull($foundUser);
    }

    public function test_find_by_credentials_with_nonexistent_email()
    {
        $foundUser = $this->userRepository->findByCredentials('nonexistent@example.com', 'password123');

        $this->assertNull($foundUser);
    }

    public function test_create_token_for_user()
    {
        $user = User::factory()->create();

        $token = $this->userRepository->createToken($user);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function test_revoke_tokens_for_user()
    {
        $user = User::factory()->create();
        $user->createToken('test-token');
        $user->createToken('another-token');

        $this->assertDatabaseCount('personal_access_tokens', 2);

        $result = $this->userRepository->revokeTokens($user);

        $this->assertTrue($result);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_find_by_id()
    {
        $user = User::factory()->create();

        $foundUser = $this->userRepository->findById($user->id);

        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($user->id, $foundUser->id);
    }

    public function test_find_by_id_with_nonexistent_id()
    {
        $foundUser = $this->userRepository->findById(999);

        $this->assertNull($foundUser);
    }
}
