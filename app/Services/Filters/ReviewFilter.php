<?php

namespace App\Services\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReviewFilter
{
    public static function apply(Builder $query, Request $request): Builder
    {
        // Filter by stars
        $stars = $request->input('filter.stars');
        if ($stars) {
            $starsArray = array_map('intval', explode(',', $stars));
            $query->whereIn('rating', $starsArray);
        }

        // Sorting by stars
        $sort = $request->query('sort');
        if ($sort) {
            $direction = 'asc';
            $column = $sort;
            if (str_starts_with($sort, '-')) {
                $direction = 'desc';
                $column = ltrim($sort, '-');
            }
            if ($column === 'stars' || $column === 'rating') {
                $query->orderBy('rating', $direction);
            }
        }

        return $query;
    }
} 