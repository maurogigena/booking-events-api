<?php

namespace Tests\Unit\Filters;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Review;
use App\Services\Filters\ReviewFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class ReviewFilterTest extends TestCase
{
    use RefreshDatabase;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->event = Event::factory()->create([
            'reservation_deadline' => now()->addDays(1),
            'date_time' => now()->addDays(2)
        ]);
    }

    public function test_filter_by_rating(): void
    {
        Review::create([
            'user_id' => User::factory()->create()->id,
            'event_id' => $this->event->id,
            'rating' => 5,
            'comment' => 'Excellent!'
        ]);

        Review::create([
            'user_id' => User::factory()->create()->id,
            'event_id' => $this->event->id,
            'rating' => 3,
            'comment' => 'Average'
        ]);

        $request = new Request(['filter' => ['stars' => '5']]);
        $filteredReviews = ReviewFilter::apply(Review::query(), $request)->get();

        $this->assertCount(1, $filteredReviews);
        $this->assertEquals(5, $filteredReviews->first()->rating);
    }

    public function test_filter_by_multiple_ratings(): void
    {
        Review::create([
            'user_id' => User::factory()->create()->id,
            'event_id' => $this->event->id,
            'rating' => 5,
            'comment' => 'Excellent!'
        ]);

        Review::create([
            'user_id' => User::factory()->create()->id,
            'event_id' => $this->event->id,
            'rating' => 3,
            'comment' => 'Average'
        ]);

        Review::create([
            'user_id' => User::factory()->create()->id,
            'event_id' => $this->event->id,
            'rating' => 1,
            'comment' => 'Poor'
        ]);

        $request = new Request(['filter' => ['stars' => '3,5']]);
        $filteredReviews = ReviewFilter::apply(Review::query(), $request)->get();

        $this->assertCount(2, $filteredReviews);
        $this->assertTrue($filteredReviews->pluck('rating')->contains(3));
        $this->assertTrue($filteredReviews->pluck('rating')->contains(5));
    }

    public function test_sort_reviews(): void
    {
        Review::create([
            'user_id' => User::factory()->create()->id,
            'event_id' => $this->event->id,
            'rating' => 5,
            'comment' => 'Later review',
            'created_at' => now()->addDay()
        ]);

        Review::create([
            'user_id' => User::factory()->create()->id,
            'event_id' => $this->event->id,
            'rating' => 3,
            'comment' => 'Earlier review',
            'created_at' => now()
        ]);

        // Test descending order (default)
        $request = new Request();
        $reviews = ReviewFilter::apply(Review::query(), $request)->get();
        $this->assertEquals(5, $reviews->first()->rating);

        // Test ascending order
        $request = new Request(['sort' => 'rating']);
        $reviews = ReviewFilter::apply(Review::query(), $request)->get();
        $this->assertEquals(3, $reviews->first()->rating);
    }
} 