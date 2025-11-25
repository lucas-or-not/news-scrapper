<?php

namespace App\Actions\Auth;

use App\Repositories\Contracts\UserRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogoutUser
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

            $this->userRepository->revokeTokens($user);

            return [
                'success' => true,
                'message' => 'Logout successful',
            ];
        } catch (Exception $e) {
            Log::error('Logout failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception('Logout failed. Please try again later.');
        }
    }
}
