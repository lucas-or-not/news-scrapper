<?php

namespace Tests\Unit\Repositories;

use App\Models\Source;
use App\Repositories\SourceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SourceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SourceRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new SourceRepository;
    }

    public function test_get_active_sources_returns_only_active_sources()
    {
        // Arrange
        Source::factory()->create(['name' => 'Active Source 1', 'is_active' => true]);
        Source::factory()->create(['name' => 'Active Source 2', 'is_active' => true]);
        Source::factory()->create(['name' => 'Inactive Source', 'is_active' => false]);

        // Act
        $result = $this->repository->getActiveSources();

        // Assert
        $this->assertCount(2, $result);
        $result->each(function ($source) {
            $this->assertTrue($source->is_active);
        });
    }

    public function test_get_active_sources_returns_empty_collection_when_no_active_sources()
    {
        // Arrange
        Source::factory()->create(['is_active' => false]);
        Source::factory()->create(['is_active' => false]);

        // Act
        $result = $this->repository->getActiveSources();

        // Assert
        $this->assertCount(0, $result);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    public function test_get_active_sources_returns_empty_collection_when_no_sources()
    {
        // Act
        $result = $this->repository->getActiveSources();

        // Assert
        $this->assertCount(0, $result);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    public function test_find_by_id_returns_source_when_exists()
    {
        // Arrange
        $source = Source::factory()->create();

        // Act
        $result = $this->repository->findById($source->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($source->id, $result->id);
    }

    public function test_find_by_id_returns_null_when_not_exists()
    {
        // Act
        $result = $this->repository->findById(999);

        // Assert
        $this->assertNull($result);
    }

    public function test_find_or_create_by_api_slug_creates_new_source()
    {
        // Arrange
        $data = [
            'name' => 'Test Source',
            'api_slug' => 'test-source',
            'is_active' => true,
            'config' => ['test' => 'value']
        ];

        // Act
        $source = $this->repository->findOrCreateByApiSlug('test-source', $data);

        // Assert
        $this->assertInstanceOf(Source::class, $source);
        $this->assertEquals('test-source', $source->api_slug);
        $this->assertEquals('Test Source', $source->name);
        $this->assertDatabaseHas('sources', [
            'api_slug' => 'test-source',
            'name' => 'Test Source'
        ]);
    }

    public function test_find_or_create_by_api_slug_finds_existing_source()
    {
        // Arrange
        $existingSource = Source::factory()->create([
            'api_slug' => 'existing-source',
            'name' => 'Existing Source'
        ]);

        $data = [
            'name' => 'Updated Source',
            'api_slug' => 'existing-source',
            'is_active' => false
        ];

        // Act
        $source = $this->repository->findOrCreateByApiSlug('existing-source', $data);

        // Assert
        $this->assertEquals($existingSource->id, $source->id);
        $this->assertEquals('Existing Source', $source->name); // Should keep original name
        $this->assertDatabaseCount('sources', 1);
    }
}
