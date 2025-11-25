<?php

namespace Tests\Unit\Actions\UserPreferences;

use App\Actions\UserPreferences\GetPreferences;
use App\Models\UserPreference;
use App\Repositories\Contracts\UserPreferenceRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class GetPreferencesTest extends TestCase
{
    private $userPreferenceRepository;

    private GetPreferences $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userPreferenceRepository = Mockery::mock(UserPreferenceRepositoryInterface::class);
        $this->action = new GetPreferences($this->userPreferenceRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_returns_existing_preferences()
    {
        // Arrange
        $userId = 123;
        $preferences = new UserPreference;
        $preferences->user_id = $userId;
        $preferences->preferred_sources = ['source1', 'source2'];
        $preferences->preferred_categories = ['category1'];
        $preferences->preferred_authors = ['author1'];

        Auth::shouldReceive('id')
            ->once()
            ->andReturn($userId);

        $this->userPreferenceRepository
            ->shouldReceive('getByUserId')
            ->once()
            ->with($userId)
            ->andReturn($preferences);

        // Act
        $result = $this->action->execute();

        // Assert
        $this->assertSame($preferences, $result);
    }

    public function test_execute_creates_default_preferences_when_none_exist()
    {
        // Arrange
        $userId = 123;
        $newPreferences = new UserPreference;
        $newPreferences->user_id = $userId;
        $newPreferences->preferred_sources = [];
        $newPreferences->preferred_categories = [];
        $newPreferences->preferred_authors = [];

        Auth::shouldReceive('id')
            ->once()
            ->andReturn($userId);

        $this->userPreferenceRepository
            ->shouldReceive('getByUserId')
            ->once()
            ->with($userId)
            ->andReturn(null);

        $this->userPreferenceRepository
            ->shouldReceive('create')
            ->once()
            ->with([
                'user_id' => $userId,
                'preferred_sources' => [],
                'preferred_categories' => [],
                'preferred_authors' => [],
            ])
            ->andReturn($newPreferences);

        // Act
        $result = $this->action->execute();

        // Assert
        $this->assertSame($newPreferences, $result);
    }
}
