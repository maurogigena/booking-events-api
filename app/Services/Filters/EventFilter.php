<?php

namespace App\Services\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class EventFilter
{
    public static function apply(Builder $query, Request $request): Builder
    {
        // Filter by title
        if ($title = $request->input('filter.title')) {
            $query->where('title', 'like', "%$title%");
        }
        // Filter by minimum price
        if ($min = $request->input('filter.price_min')) {
            $query->where('price', '>=', $min);
        }
        // Filter by maximum price
        if ($max = $request->input('filter.price_max')) {
            $query->where('price', '<=', $max);
        }
        // Filter by minimum date
        if ($dateMin = $request->input('filter.date_min')) {
            $query->where('date_time', '>=', $dateMin);
        }
        // Filter by maximum date
        if ($dateMax = $request->input('filter.date_max')) {
            $query->where('date_time', '<=', $dateMax);
        }

        // Only available events (active and with seats)
        $query->where('reservation_deadline', '>', now())
            ->withCount('attendees')
            ->havingRaw('attendees_count < attendee_limit');

        // Sorting
        $sort = $request->query('sort');
        if ($sort) {
            $direction = 'asc';
            $column = $sort;
            if (str_starts_with($sort, '-')) {
                $direction = 'desc';
                $column = ltrim($sort, '-');
            }
            if (in_array($column, ['date_time', 'price', 'title'])) {
                $query->orderBy($column, $direction);
            }
        }

        return $query;
    }
} 