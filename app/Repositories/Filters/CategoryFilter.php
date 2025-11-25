<?php

namespace App\Repositories\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CategoryFilter
{
    /**
     * @param Request $request
     */
    public function __construct(private Request $request) {}

    /**
     * Filter by category slug.
     *
     * @param Builder $query
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Builder $query, Closure $next)
    {
        if ($this->request->filled('category')) {
            $slug = (string) $this->request->get('category');
            $query->whereHas('category', function (Builder $q) use ($slug) {
                $q->where('slug', $slug);
            });
        }

        return $next($query);
    }
}