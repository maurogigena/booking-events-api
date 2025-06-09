<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'date_time',
        'location',
        'price',
        'attendee_limit',
        'reservation_deadline',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'reservation_deadline' => 'datetime',
        'price' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::addGlobalScope('available', function (Builder $builder) {
            $builder->where('reservation_deadline', '>', now())
                   ->withCount('attendees')
                   ->havingRaw('attendees_count < attendee_limit');
        });
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query->when($filters['title'] ?? null, function ($query, $title) {
                $query->where('title', 'like', "%{$title}%");
            })
            ->when($filters['price_min'] ?? null, function ($query, $min) {
                $query->where('price', '>=', $min);
            })
            ->when($filters['price_max'] ?? null, function ($query, $max) {
                $query->where('price', '<=', $max);
            })
            ->when($filters['date_min'] ?? null, function ($query, $dateMin) {
                $query->where('date_time', '>=', $dateMin);
            })
            ->when($filters['date_max'] ?? null, function ($query, $dateMax) {
                $query->where('date_time', '<=', $dateMax);
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

            if (in_array($column, ['date_time', 'price', 'title'])) {
                $query->orderBy($column, $direction);
            }
        }

        return $query;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_user')
                    ->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
