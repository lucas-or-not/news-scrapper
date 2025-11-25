<?php

namespace App\Repositories\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class KeywordFilter
{
    /**
     * @param Request $request
     */
    public function __construct(private Request $request) {}

    /**
     * Apply keyword filtering across text columns.
     *
     * @param Builder $query
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Builder $query, Closure $next)
    {
        $keyword = (string) ($this->request->get('q', $this->request->get('keyword', '')));

        if ($keyword !== '') {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('title', 'like', '%'.$keyword.'%')
                  ->orWhere('excerpt', 'like', '%'.$keyword.'%')
                  ->orWhere('content', 'like', '%'.$keyword.'%');
            });
        }

        return $next($query);
    }
}