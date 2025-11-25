<?php

namespace App\Actions\Categories;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetCategories
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function execute(Request $request): array
    {
        try {
            $categories = $this->categoryRepository->getAllOrdered();

            return [
                'success' => true,
                'data' => $categories,
            ];
        } catch (Exception $e) {
            Log::error('Failed to get categories', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to retrieve categories',
            ];
        }
    }
}
