<?php

namespace App\Repositories;

use App\Models\Article;
use App\Models\User;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Filters\AuthorFilter;
use App\Repositories\Filters\CategoryFilter;
use App\Repositories\Filters\DateRangeFilter;
use App\Repositories\Filters\KeywordFilter;
use App\Repositories\Filters\SourceFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class ArticleRepository implements ArticleRepositoryInterface
{
    /**
     * Default pagination size.
     */
    private const DEFAULT_PER_PAGE = 20;

    /**
     * Find an article by ID with relations.
     *
     * @param int $id
     * @return Article|null
     */
    public function findWithRelations(int $id): ?Article
    {
        return Article::with(['source', 'author', 'category'])->find($id);
    }

    /**
     * Search articles using Eloquent filters and pagination.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function search(Request $request): LengthAwarePaginator
    {
        $perPage = (int) $request->get('per_page', self::DEFAULT_PER_PAGE);

        $query = Article::query()->with(['source', 'author', 'category']);

        $query = app(Pipeline::class)
            ->send($query)
            ->through([
                new KeywordFilter($request),
                new SourceFilter($request),
                new CategoryFilter($request),
                new AuthorFilter($request),
                new DateRangeFilter($request),
            ])
            ->thenReturn();

        return $query->orderByDesc('published_at')->paginate($perPage);
    }

    /**
     * Find article by source and external source article ID.
     *
     * @param int $sourceId
     * @param string $sourceArticleId
     * @return Article|null
     */
    public function findBySourceAndSourceArticleId(int $sourceId, string $sourceArticleId): ?Article
    {
        return Article::where('source_id', $sourceId)
            ->where('source_article_id', $sourceArticleId)
            ->first();
    }

    /**
     * Create a new article record.
     *
     * @param array $data
     * @return Article
     */
    public function create(array $data): Article
    {
        return Article::create($data);
    }
}
