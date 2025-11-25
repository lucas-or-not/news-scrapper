<?php

namespace Tests\Unit\Repositories;

use App\Models\Author;
use App\Repositories\AuthorRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AuthorRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AuthorRepository;
    }

    public function test_get_all_ordered_returns_authors_ordered_by_name()
    {
        // Arrange
        Author::factory()->create(['name' => 'John Smith']);
        Author::factory()->create(['name' => 'Alice Johnson']);
        Author::factory()->create(['name' => 'Bob Wilson']);

        // Act
        $result = $this->repository->getAllOrdered();

        // Assert
        $this->assertCount(3, $result);
        $this->assertEquals('Alice Johnson', $result->first()->name);
        $this->assertEquals('Bob Wilson', $result->get(1)->name);
        $this->assertEquals('John Smith', $result->last()->name);
    }

    public function test_get_all_ordered_returns_empty_collection_when_no_authors()
    {
        // Act
        $result = $this->repository->getAllOrdered();

        // Assert
        $this->assertCount(0, $result);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    public function test_find_or_create_by_canonical_name_creates_new_author()
    {
        // Act
        $author = $this->repository->findOrCreateByCanonicalName('john-doe', 'John Doe');

        // Assert
        $this->assertInstanceOf(\App\Models\Author::class, $author);
        $this->assertEquals('john-doe', $author->canonical_name);
        $this->assertEquals('John Doe', $author->name);
        $this->assertDatabaseHas('authors', [
            'canonical_name' => 'john-doe',
            'name' => 'John Doe'
        ]);
    }

    public function test_find_or_create_by_canonical_name_finds_existing_author()
    {
        // Arrange
        $existingAuthor = \App\Models\Author::factory()->create([
            'canonical_name' => 'john-doe',
            'name' => 'John Doe'
        ]);

        // Act
        $author = $this->repository->findOrCreateByCanonicalName('john-doe', 'Johnny Doe');

        // Assert
        $this->assertEquals($existingAuthor->id, $author->id);
        $this->assertEquals('John Doe', $author->name); // Should keep original name
        $this->assertDatabaseCount('authors', 1);
    }
}
