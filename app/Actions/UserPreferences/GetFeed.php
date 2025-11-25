<?php

namespace App\Actions\UserPreferences;

use App\Repositories\Contracts\UserPreferenceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;

class GetFeed
{
    /**
     * @param UserPreferenceRepositoryInterface $userPreferenceRepository
     */
    public function __construct(
        private UserPreferenceRepositoryInterface $userPreferenceRepository
    ) {}

    /**
     * Retrieve the personalized feed for the authenticated user.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function execute(Request $request): LengthAwarePaginator
    {
        $request->validate([
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
        ]);

        $userId = Auth::id();
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 10);

        Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });

        return $this->userPreferenceRepository->getPersonalizedFeed($userId, $perPage);
    }
}
