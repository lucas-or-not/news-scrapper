<?php

namespace App\Repositories\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AuthorFilter
{
    /**
     * @param Request $request
     */
    public function __construct(private Request $request) {}

    /**
     * Filter by author name.
     *
     * @param Builder $query
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Builder $query, Closure $next)
    {
        if ($this->request->filled('author')) {
            $name = (string) $this->request->get('author');
            $query->whereHas('author', function (Builder $q) use ($name) {
                $q->where('name', $name);
            });
        }

        return $next($query);
    }
}