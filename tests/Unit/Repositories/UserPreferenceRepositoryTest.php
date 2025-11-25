<?php

namespace Tests\Unit\Repositories;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use App\Models\User;
use App\Models\UserPreference;
use App\Repositories\UserPreferenceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPreferenceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserPreferenceRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserPreferenceRepository;
    }

    public function test_get_by_user_id_returns_user_preferences()
    {
        // Arrange
        $user = User::factory()->create();
        $preferences = UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => [1, 2],
            'preferred_categories' => [3, 4],
            'preferred_authors' => [5, 6],
        ]);

        // Act
        $result = $this->repository->getByUserId($user->id);

        // Assert
        $this->assertInstanceOf(UserPreference::class, $result);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals([1, 2], $result->preferred_sources);
        $this->assertEquals([3, 4], $result->preferred_categories);
        $this->assertEquals([5, 6], $result->preferred_authors);
    }

    public function test_get_by_user_id_returns_null_when_no_preferences_exist()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $result = $this->repository->getByUserId($user->id);

        // Assert
        $this->assertNull($result);
    }

    public function test_create_creates_new_user_preferences()
    {
        // Arrange
        $user = User::factory()->create();
        $data = [
            'user_id' => $user->id,
            'preferred_sources' => [1, 2],
            'preferred_categories' => [3, 4],
            'preferred_authors' => [5, 6],
        ];

        // Act
        $result = $this->repository->create($data);

        // Assert
        $this->assertInstanceOf(UserPreference::class, $result);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals([1, 2], $result->preferred_sources);
        $this->assertEquals([3, 4], $result->preferred_categories);
        $this->assertEquals([5, 6], $result->preferred_authors);
        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
        ]);
    }

    public function test_create_with_empty_preferences_uses_defaults()
    {
        // Arrange
        $user = User::factory()->create();
        $data = ['user_id' => $user->id];

        // Act
        $result = $this->repository->create($data);

        // Assert
        $this->assertInstanceOf(UserPreference::class, $result);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals([], $result->preferred_sources);
        $this->assertEquals([], $result->preferred_categories);
        $this->assertEquals([], $result->preferred_authors);
    }

    public function test_update_updates_existing_preferences()
    {
        // Arrange
        $user = User::factory()->create();
        $preferences = UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => [1],
            'preferred_categories' => [2],
            'preferred_authors' => [3],
        ]);

        $updateData = [
            'preferred_sources' => [4, 5],
            'preferred_categories' => [6, 7],
            'preferred_authors' => [8, 9],
        ];

        // Act
        $result = $this->repository->update($user->id, $updateData);

        // Assert
        $this->assertInstanceOf(UserPreference::class, $result);
        $this->assertEquals([4, 5], $result->preferred_sources);
        $this->assertEquals([6, 7], $result->preferred_categories);
        $this->assertEquals([8, 9], $result->preferred_authors);
    }

    public function test_update_creates_preferences_when_none_exist()
    {
        // Arrange
        $user = User::factory()->create();
        $updateData = [
            'preferred_sources' => [1, 2],
            'preferred_categories' => [3, 4],
            'preferred_authors' => [5, 6],
        ];

        // Act
        $result = $this->repository->update($user->id, $updateData);

        // Assert
        $this->assertInstanceOf(UserPreference::class, $result);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals([1, 2], $result->preferred_sources);
        $this->assertEquals([3, 4], $result->preferred_categories);
        $this->assertEquals([5, 6], $result->preferred_authors);
        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
        ]);
    }

    public function test_update_partial_data_preserves_existing_values()
    {
        // Arrange
        $user = User::factory()->create();
        $preferences = UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => [1, 2],
            'preferred_categories' => [3, 4],
            'preferred_authors' => [5, 6],
        ]);

        $updateData = ['preferred_sources' => [7, 8]];

        // Act
        $result = $this->repository->update($user->id, $updateData);

        // Assert
        $this->assertEquals([7, 8], $result->preferred_sources);
        $this->assertEquals([3, 4], $result->preferred_categories); // Preserved
        $this->assertEquals([5, 6], $result->preferred_authors); // Preserved
    }

    public function test_get_personalized_feed_returns_paginated_articles()
    {
        // Arrange
        $user = User::factory()->create();
        $source = Source::factory()->create();
        $category = Category::factory()->create();
        $author = Author::factory()->create();

        UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => [$source->id],
            'preferred_categories' => [$category->id],
            'preferred_authors' => [$author->id],
        ]);

        Article::factory()->create([
            'source_id' => $source->id,
            'category_id' => $category->id,
            'author_id' => $author->id,
        ]);

        // Act
        $result = $this->repository->getPersonalizedFeed($user->id, 10);

        // Assert
        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
        $this->assertEquals(1, $result->total());
    }

    public function test_get_personalized_feed_returns_all_articles_when_no_preferences()
    {
        // Arrange
        $user = User::factory()->create();
        $source = Source::factory()->create();
        $category = Category::factory()->create();
        $author = Author::factory()->create();

        Article::factory()->create([
            'source_id' => $source->id,
            'category_id' => $category->id,
            'author_id' => $author->id,
        ]);

        // Act
        $result = $this->repository->getPersonalizedFeed($user->id, 10);

        // Assert
        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
        $this->assertEquals(1, $result->total());
    }

    public function test_delete_removes_user_preferences()
    {
        // Arrange
        $user = User::factory()->create();
        $preferences = UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => [1, 2],
            'preferred_categories' => [3, 4],
            'preferred_authors' => [5, 6],
        ]);

        // Act
        $result = $this->repository->delete($user->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('user_preferences', [
            'user_id' => $user->id,
        ]);
    }

    public function test_delete_returns_false_when_no_preferences_exist()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $result = $this->repository->delete($user->id);

        // Assert
        $this->assertFalse($result);
    }
}
