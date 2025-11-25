<?php

namespace App\Repositories\Contracts;

use App\Models\Source;
use Illuminate\Database\Eloquent\Collection;

interface SourceRepositoryInterface
{
    /**
     * Get all active sources
     */
    public function getActiveSources(): Collection;

    /**
     * Find a source by ID
     */
    public function findById(int $id): ?Source;

    /**
     * Find or create a source by api_slug
     */
    public function findOrCreateByApiSlug(string $apiSlug, array $data): Source;
}
