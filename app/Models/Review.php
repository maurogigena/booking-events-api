<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer'
    ];

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query->when($filters['stars'] ?? null, function ($query, $stars) {
            $starsArray = array_map('intval', explode(',', $stars));
            $query->whereIn('rating', $starsArray);
        });
    }

    public function scopeSort(Builder $query, ?string $sort): Builder
    {
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
