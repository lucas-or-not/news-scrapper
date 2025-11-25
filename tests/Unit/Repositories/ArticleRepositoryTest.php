<?php

namespace Tests\Unit\Repositories;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use App\Models\User;
use App\Repositories\ArticleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ArticleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ArticleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ArticleRepository;
    }

    public function test_find_with_relations_returns_article_with_relationships()
    {
        // Arrange
        $article = Article::factory()->create();

        // Act
        $result = $this->repository->findWithRelations($article->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($article->id, $result->id);
        $this->assertTrue($result->relationLoaded('source'));
        $this->assertTrue($result->relationLoaded('author'));
        $this->assertTrue($result->relationLoaded('category'));
    }

    public function test_find_with_relations_returns_null_for_nonexistent_article()
    {
        // Act
        $result = $this->repository->findWithRelations(999);

        // Assert
        $this->assertNull($result);
    }

    public function test_search_returns_paginated_results()
    {
        // Arrange
        Article::factory()->count(5)->create();
        $request = new Request(['per_page' => 3]);

        // Act
        $result = $this->repository->search($request);

        // Assert
        $this->assertEquals(3, $result->perPage());
        $this->assertLessThanOrEqual(3, $result->count());
    }

    public function test_find_by_source_and_source_article_id_finds_existing_article()
    {
        // Arrange
        $source = Source::factory()->create();
        $article = Article::factory()->create([
            'source_id' => $source->id,
            'source_article_id' => 'test-123'
        ]);

        // Act
        $result = $this->repository->findBySourceAndSourceArticleId($source->id, 'test-123');

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($article->id, $result->id);
    }

    public function test_find_by_source_and_source_article_id_returns_null_when_not_found()
    {
        // Arrange
        $source = Source::factory()->create();

        // Act
        $result = $this->repository->findBySourceAndSourceArticleId($source->id, 'nonexistent');

        // Assert
        $this->assertNull($result);
    }

    public function test_create_creates_new_article()
    {
        // Arrange
        $source = Source::factory()->create();
        $articleData = [
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => 'Test content',
            'url' => 'https://example.com/test',
            'source_id' => $source->id,
            'source_article_id' => 'test-456',
            'published_at' => now(),
            'scraped_at' => now(),
        ];

        // Act
        $article = $this->repository->create($articleData);

        // Assert
        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals('Test Article', $article->title);
        $this->assertEquals($source->id, $article->source_id);
        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article',
            'source_id' => $source->id,
            'source_article_id' => 'test-456'
        ]);
    }
}
