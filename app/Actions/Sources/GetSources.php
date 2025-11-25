<?php

namespace App\Actions\Sources;

use App\Repositories\Contracts\SourceRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetSources
{
    public function __construct(
        private SourceRepositoryInterface $sourceRepository
    ) {}

    public function execute(Request $request): array
    {
        try {
            $sources = $this->sourceRepository->getActiveSources();

            return [
                'success' => true,
                'data' => $sources,
            ];
        } catch (Exception $e) {
            Log::error('Failed to get sources', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to retrieve sources',
            ];
        }
    }
}
