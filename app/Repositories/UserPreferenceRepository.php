<?php

namespace App\Repositories;

use App\Models\Article;
use App\Models\UserPreference;
use App\Repositories\Contracts\UserPreferenceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class UserPreferenceRepository implements UserPreferenceRepositoryInterface
{
    /**
     * Retrieve preferences by user ID.
     *
     * @param int $userId
     * @return UserPreference|null
     */
    public function getByUserId(int $userId): ?UserPreference
    {
        return UserPreference::where('user_id', $userId)->first();
    }

    /**
     * Create a new preferences record.
     *
     * @param array $data
     * @return UserPreference
     */
    public function create(array $data): UserPreference
    {
        return UserPreference::create([
            'user_id' => $data['user_id'],
            'preferred_sources' => $data['preferred_sources'] ?? [],
            'preferred_categories' => $data['preferred_categories'] ?? [],
            'preferred_authors' => $data['preferred_authors'] ?? [],
        ]);
    }

    /**
     * Update preferences for a user, creating if missing.
     *
     * @param int $userId
     * @param array $data
     * @return UserPreference
     */
    public function update(int $userId, array $data): UserPreference
    {
        $preferences = $this->getByUserId($userId);

        if (! $preferences) {
            return $this->create(array_merge($data, ['user_id' => $userId]));
        }

        $preferences->update([
            'preferred_sources' => $data['preferred_sources'] ?? $preferences->preferred_sources,
            'preferred_categories' => $data['preferred_categories'] ?? $preferences->preferred_categories,
            'preferred_authors' => $data['preferred_authors'] ?? $preferences->preferred_authors,
        ]);

        return $preferences->fresh();
    }

    /**
     * Get a personalized feed based on user preferences.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPersonalizedFeed(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        $preferences = $this->getByUserId($userId);

        $query = Article::select([
                'id',
                'source_id',
                'source_article_id',
                'title',
                'slug',
                'excerpt',
                'content',
                'url',
                'image_url',
                'author_id',
                'category_id',
                'language',
                'created_at',
                'updated_at',
            ])
            ->with([
                'source:id,name',
                'author:id,name,canonical_name',
                'category:id,name'
            ]);

        if ($preferences) {
            $query->where(function ($q) use ($preferences) {
                $this->applyPreferenceFilter(
                    $q,
                    $preferences->preferred_sources ?? [],
                    'articles.source_id',
                    'source',
                    'api_slug'
                );

                $this->applyPreferenceFilter(
                    $q,
                    $preferences->preferred_categories ?? [],
                    'articles.category_id',
                    'category',
                    'slug'
                );

                $this->applyPreferenceFilter(
                    $q,
                    $preferences->preferred_authors ?? [],
                    'articles.author_id',
                    'author',
                    ['name', 'canonical_name']
                );
            });
        }

        return $query->orderBy('articles.published_at', 'desc')->paginate($perPage);
    }

    /**
     * Apply OR-combined filters for IDs and slugs/names to the query.
     *
     * @param Builder $q
     * @param array $values
     * @param string $column
     * @param string $relation
     * @param string|array $slugFields
     * @return void
     */
    private function applyPreferenceFilter(Builder $q, array $values, string $column, string $relation, $slugFields): void
    {
        $values = collect($values);

        $ids = $values->filter(fn ($v) => is_numeric($v))->map(fn ($v) => (int) $v)->all();
        $slugs = $values->filter(fn ($v) => is_string($v))->all();

        $q->where(function ($sub) use ($ids, $slugs, $column, $relation, $slugFields) {
            if (!empty($ids)) {
                $sub->orWhereIn($column, $ids);
            }

            if (!empty($slugs)) {
                $sub->orWhereHas($relation, function ($r) use ($slugs, $slugFields) {
                    if (is_array($slugFields)) {
                        foreach ($slugFields as $field) {
                            $r->orWhereIn($field, $slugs);
                        }
                    } else {
                        $r->whereIn($slugFields, $slugs);
                    }
                });
            }
        });
    }


    /**
     * Delete preferences for a user.
     *
     * @param int $userId
     * @return bool
     */
    public function delete(int $userId): bool
    {
        $preferences = $this->getByUserId($userId);

        if (! $preferences) {
            return false;
        }

        return $preferences->delete();
    }
}
