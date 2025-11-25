<?php

namespace Tests\Unit\Actions\UserPreferences;

use App\Actions\UserPreferences\UpdatePreferences;
use App\Models\UserPreference;
use App\Repositories\Contracts\UserPreferenceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class UpdatePreferencesTest extends TestCase
{
    private $userPreferenceRepository;

    private UpdatePreferences $action;

    private $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userPreferenceRepository = Mockery::mock(UserPreferenceRepositoryInterface::class);
        $this->request = Mockery::mock(Request::class);
        $this->action = new UpdatePreferences($this->userPreferenceRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_updates_preferences_successfully()
    {
        // Arrange
        $userId = 123;
        $preferredSources = ['source1', 'source2'];
        $preferredCategories = ['category1'];
        $preferredAuthors = ['author1', 'author2'];

        $updatedPreferences = new UserPreference;
        $updatedPreferences->user_id = $userId;
        $updatedPreferences->preferred_sources = $preferredSources;
        $updatedPreferences->preferred_categories = $preferredCategories;
        $updatedPreferences->preferred_authors = $preferredAuthors;

        $this->request
            ->shouldReceive('validate')
            ->once()
            ->with([
                'preferred_sources' => 'array',
                'preferred_categories' => 'array',
                'preferred_authors' => 'array',
            ]);

        $this->request->preferred_sources = $preferredSources;
        $this->request->preferred_categories = $preferredCategories;
        $this->request->preferred_authors = $preferredAuthors;

        Auth::shouldReceive('id')
            ->once()
            ->andReturn($userId);

        $this->userPreferenceRepository
            ->shouldReceive('update')
            ->once()
            ->with($userId, [
                'preferred_sources' => $preferredSources,
                'preferred_categories' => $preferredCategories,
                'preferred_authors' => $preferredAuthors,
            ])
            ->andReturn($updatedPreferences);

        // Act
        $result = $this->action->execute($this->request);

        // Assert
        $this->assertSame($updatedPreferences, $result);
    }
}
