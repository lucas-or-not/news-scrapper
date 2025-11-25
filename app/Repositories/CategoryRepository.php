<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function getAllOrdered(): Collection
    {
        return Category::orderBy('name')->get();
    }

    public function findOrCreateBySlug(string $slug, string $name): Category
    {
        return Category::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name]
        );
    }
}
