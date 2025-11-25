<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    /**
     * Get all categories ordered by name
     */
    public function getAllOrdered(): Collection;

    /**
     * Find or create a category by slug
     */
    public function findOrCreateBySlug(string $slug, string $name): Category;
}
