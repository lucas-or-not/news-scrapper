<?php

namespace App\Repositories;

use App\Models\Author;
use App\Repositories\Contracts\AuthorRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AuthorRepository implements AuthorRepositoryInterface
{
    public function getAllOrdered(): Collection
    {
        return Author::orderBy('name')->get();
    }

    public function findOrCreateByCanonicalName(string $canonicalName, string $name): Author
    {
        return Author::firstOrCreate(
            ['canonical_name' => $canonicalName],
            ['name' => $name]
        );
    }
}
