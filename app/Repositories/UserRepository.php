<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    public function create(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return User::create($data);
    }

    public function findByCredentials(string $email, string $password): ?User
    {
        $user = User::where('email', $email)->first();

        if ($user && Hash::check($password, $user->password)) {
            return $user;
        }

        return null;
    }

    public function createToken(User $user): string
    {
        return $user->createToken('auth-token')->plainTextToken;
    }

    public function revokeTokens(User $user): bool
    {
        $user->tokens()->delete();

        return true;
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }
}
