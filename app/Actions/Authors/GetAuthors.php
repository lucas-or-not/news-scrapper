<?php

namespace App\Actions\Authors;

use App\Repositories\Contracts\AuthorRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetAuthors
{
    public function __construct(
        private AuthorRepositoryInterface $authorRepository
    ) {}

    public function execute(Request $request): array
    {
        try {
            $authors = $this->authorRepository->getAllOrdered();

            return [
                'success' => true,
                'data' => $authors,
            ];
        } catch (Exception $e) {
            Log::error('Failed to get authors', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to retrieve authors',
            ];
        }
    }
}
