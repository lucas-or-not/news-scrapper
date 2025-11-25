<?php

namespace App\Actions\Auth;

use App\Repositories\Contracts\UserRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(Request $request): array
    {
        try {
            $user = $request->user();

            if (! $user) {
                return [
                    'success' => false,
                    'error' => 'User not authenticated',
                ];
            }

            return [
                'success' => true,
                'data' => $user,
            ];
        } catch (Exception $e) {
            Log::error('Failed to get user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to retrieve user information',
            ];
        }
    }
}
