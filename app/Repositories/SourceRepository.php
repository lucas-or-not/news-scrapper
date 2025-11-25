<?php

namespace App\Repositories;

use App\Models\Source;
use App\Repositories\Contracts\SourceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SourceRepository implements SourceRepositoryInterface
{
    public function getActiveSources(): Collection
    {
        return Source::where('is_active', true)->get();
    }

    public function findById(int $id): ?Source
    {
        return Source::find($id);
    }

    public function findOrCreateByApiSlug(string $apiSlug, array $data): Source
    {
        return Source::firstOrCreate(
            ['api_slug' => $apiSlug],
            $data
        );
    }
}
