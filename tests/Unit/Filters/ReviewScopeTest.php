<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Review;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewScopeTest extends TestCase
{
    use RefreshDatabase;

    private Event $event;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->event = Event::factory()->create([
            'reservation_deadline' => now()->addDays(1)
        ]);
        $this->user = User::factory()->create();
    }

    public function test_filter_by_single_star_rating(): void
    {
        Review::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'rating' => 5
        ]);
        Review::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'rating' => 3
        ]);

        $reviews = Review::query()
            ->filter(['stars' => '5'])
            ->get();

        $this->assertCount(1, $reviews);
        $this->assertEquals(5, $reviews->first()->rating);
    }

    public function test_filter_by_multiple_star_ratings(): void
    {
        Review::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'rating' => 5
        ]);
        Review::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'rating' => 4
        ]);
        Review::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'rating' => 3
        ]);

        $reviews = Review::query()
            ->filter(['stars' => '4,5'])
            ->get();

        $this->assertCount(2, $reviews);
        $this->assertTrue($reviews->every(fn($review) => in_array($review->rating, [4, 5])));
    }

    public function test_sort_reviews_by_rating(): void
    {
        Review::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'rating' => 2
        ]);
        Review::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'rating' => 5
        ]);

        // Test ascending rating sort
        $reviews = Review::query()->sort('rating')->get();
        $this->assertEquals(2, $reviews->first()->rating);
        $this->assertEquals(5, $reviews->last()->rating);

        // Test descending rating sort
        $reviews = Review::query()->sort('-rating')->get();
        $this->assertEquals(5, $reviews->first()->rating);
        $this->assertEquals(2, $reviews->last()->rating);
    }

    public function test_sort_reviews_using_stars_alias(): void
    {
        Review::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'rating' => 2
        ]);
        Review::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'rating' => 5
        ]);

        // Test ascending stars sort
        $reviews = Review::query()->sort('stars')->get();
        $this->assertEquals(2, $reviews->first()->rating);
        $this->assertEquals(5, $reviews->last()->rating);

        // Test descending stars sort
        $reviews = Review::query()->sort('-stars')->get();
        $this->assertEquals(5, $reviews->first()->rating);
        $this->assertEquals(2, $reviews->last()->rating);
    }
} 