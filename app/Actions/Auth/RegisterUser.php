<?php

namespace App\Actions\Auth;

use App\Repositories\Contracts\UserRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RegisterUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(Request $request): array
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            return DB::transaction(function () use ($request) {
                $user = $this->userRepository->create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => $request->password,
                ]);

                $token = $user->createToken('auth-token')->plainTextToken;

                return [
                    'data' => [
                        'user' => $user,
                        'token' => $token,
                    ],
                    'message' => 'User registered successfully',
                ];
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Error registering user', [
                'error' => $e->getMessage(),
                'email' => $request->email ?? 'unknown',
            ]);
            throw new Exception('Unable to register user. Please try again later.');
        }
    }
}
