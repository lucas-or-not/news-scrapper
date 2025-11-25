<?php

namespace App\Actions\UserPreferences;

use App\Models\UserPreference;
use App\Repositories\Contracts\UserPreferenceRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class GetPreferences
{
    public function __construct(
        private UserPreferenceRepositoryInterface $userPreferenceRepository
    ) {}

    public function execute(): UserPreference
    {
        $userId = Auth::id();
        $preferences = $this->userPreferenceRepository->getByUserId($userId);

        if (! $preferences) {
            $preferences = $this->userPreferenceRepository->create([
                'user_id' => $userId,
                'preferred_sources' => [],
                'preferred_categories' => [],
                'preferred_authors' => [],
            ]);
        }

        return $preferences;
    }
}
