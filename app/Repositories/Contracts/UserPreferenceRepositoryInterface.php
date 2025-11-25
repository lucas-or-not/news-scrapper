<?php

namespace App\Repositories\Contracts;

use App\Models\UserPreference;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserPreferenceRepositoryInterface
{
    /**
     * Get user preferences by user ID
     */
    public function getByUserId(int $userId): ?UserPreference;

    /**
     * Create user preferences
     */
    public function create(array $data): UserPreference;

    /**
     * Update user preferences
     */
    public function update(int $userId, array $data): UserPreference;

    /**
     * Get personalized feed for user based on preferences
     */
    public function getPersonalizedFeed(int $userId, int $perPage = 20): LengthAwarePaginator;

    /**
     * Delete user preferences
     */
    public function delete(int $userId): bool;
}
