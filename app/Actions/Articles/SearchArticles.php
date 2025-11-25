<?php

namespace App\Actions\Articles;

use App\Repositories\Contracts\ArticleRepositoryInterface;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SearchArticles
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository
    ) {}

    public function execute(Request $request): LengthAwarePaginator
    {
        try {
            $request->validate([
                'keyword' => 'nullable|string|max:255',
                'source_id' => 'nullable|integer',
                'category_id' => 'nullable|integer',
                'author_id' => 'nullable|integer',
                'from_date' => 'nullable|date',
                'to_date' => 'nullable|date|after_or_equal:from_date',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            return $this->articleRepository->search($request);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Error searching articles', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);
            throw new Exception('Search failed. Please try again later.');
        }
    }
}
