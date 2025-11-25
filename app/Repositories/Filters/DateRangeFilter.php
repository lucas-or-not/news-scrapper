<?php

namespace App\Repositories\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DateRangeFilter
{
    /**
     * @param Request $request
     */
    public function __construct(private Request $request) {}

    /**
     * Filter by published_at date range.
     *
     * @param Builder $query
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Builder $query, Closure $next)
    {
        if ($this->request->filled('date_from') || $this->request->filled('date_to')) {
            $from = $this->request->filled('date_from') ? (string) $this->request->get('date_from') : null;
            $to = $this->request->filled('date_to') ? (string) $this->request->get('date_to') : null;

            if ($from && $to && strtotime($from) > strtotime($to)) {
                [$from, $to] = [$to, $from];
            }

            if ($from) {
                $query->whereDate('published_at', '>=', $from);
            }

            if ($to) {
                $query->whereDate('published_at', '<=', $to);
            }
        }

        return $next($query);
    }
}