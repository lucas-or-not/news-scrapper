<?php

namespace App\Repositories\Contracts;

use App\Models\Author;
use Illuminate\Database\Eloquent\Collection;

interface AuthorRepositoryInterface
{
    /**
     * Get all authors ordered by name
     */
    public function getAllOrdered(): Collection;

    /**
     * Find or create an author by canonical name
     */
    public function findOrCreateByCanonicalName(string $canonicalName, string $name): Author;
}
