<?php

namespace App\Actions\Articles;

use App\Repositories\Contracts\ArticleRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShowArticle
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository
    ) {}

    public function execute(Request $request, int $articleId): array
    {
        try {
            $article = $this->articleRepository->findWithRelations($articleId, ['source', 'author', 'category']);

            if (! $article) {
                throw new Exception('Article not found');
            }

            return [
                'success' => true,
                'data' => $article
            ];
        } catch (Exception $e) {
            Log::error('Failed to show article', [
                'article_id' => $articleId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($e->getMessage() === 'Article not found') {
                return [
                    'success' => false,
                    'error' => 'Article not found',
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to retrieve article',
            ];
        }
    }
}
