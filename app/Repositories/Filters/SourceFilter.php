<?php

namespace App\Repositories\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SourceFilter
{
    /**
     * @param Request $request
     */
    public function __construct(private Request $request) {}

    /**
     * Filter by source api_slug.
     *
     * @param Builder $query
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Builder $query, Closure $next)
    {
        if ($this->request->filled('source')) {
            $slug = (string) $this->request->get('source');
            $query->whereHas('source', function (Builder $q) use ($slug) {
                $q->where('api_slug', $slug);
            });
        }

        return $next($query);
    }
}