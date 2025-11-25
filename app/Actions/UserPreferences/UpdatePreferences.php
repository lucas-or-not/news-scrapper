<?php

namespace App\Actions\UserPreferences;

use App\Models\UserPreference;
use App\Repositories\Contracts\UserPreferenceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdatePreferences
{
    /**
     * @param UserPreferenceRepositoryInterface $userPreferenceRepository
     */
    public function __construct(
        private UserPreferenceRepositoryInterface $userPreferenceRepository
    ) {}

    /**
     * Update the authenticated user's preferences.
     *
     * @param Request $request
     * @return UserPreference
     */
    public function execute(Request $request): UserPreference
    {
        $request->validate([
            'preferred_sources' => 'array',
            'preferred_categories' => 'array',
            'preferred_authors' => 'array',
        ]);

        $userId = Auth::id();

        return $this->userPreferenceRepository->update($userId, [
            'preferred_sources' => $request->preferred_sources,
            'preferred_categories' => $request->preferred_categories,
            'preferred_authors' => $request->preferred_authors,
        ]);
    }
}
