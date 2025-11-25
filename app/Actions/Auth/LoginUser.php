<?php

namespace App\Actions\Auth;

use App\Repositories\Contracts\UserRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(Request $request): array
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = $this->userRepository->findByCredentials(
                $request->email,
                $request->password
            );

            if (! $user) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $token = $this->userRepository->createToken($user);

            return [
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
                'message' => 'User logged in successfully',
            ];
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Login failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception('Login failed. Please try again later.');
        }
    }
}
