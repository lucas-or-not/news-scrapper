<?php

namespace App\Repositories\Contracts;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface ArticleRepositoryInterface
{
    /**
     * Find an article by ID with relationships
     */
    public function findWithRelations(int $id): ?Article;

    /**
     * Search articles with filters and pagination
     */
    public function search(Request $request): LengthAwarePaginator;

    /**
     * Find existing article by source and source article ID
     */
    public function findBySourceAndSourceArticleId(int $sourceId, string $sourceArticleId): ?Article;

    /**
     * Create a new article
     */
    public function create(array $data): Article;
}
