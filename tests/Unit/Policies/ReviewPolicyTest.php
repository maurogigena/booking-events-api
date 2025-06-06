<?php

namespace Tests\Unit\Policies;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Review;
use App\Policies\ReviewPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewPolicyTest extends TestCase
{
    use RefreshDatabase;

    private ReviewPolicy $policy;
    private User $user;
    private Event $event;
    private Review $review;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ReviewPolicy();
        $this->user = User::factory()->create();
        $this->event = Event::factory()->create([
            'reservation_deadline' => now()->subDays(1),
            'date_time' => now()->subHours(2)
        ]);

        $this->review = Review::create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'rating' => 5,
            'comment' => 'Great event!'
        ]);
    }

    public function test_user_can_update_own_review(): void
    {
        $response = $this->policy->update($this->user, $this->review);
        $this->assertTrue($response->allowed());
    }

    public function test_user_cannot_update_others_review(): void
    {
        $otherUser = User::factory()->create();
        $response = $this->policy->update($otherUser, $this->review);
        $this->assertFalse($response->allowed());
    }

    public function test_user_can_delete_own_review(): void
    {
        $response = $this->policy->delete($this->user, $this->review);
        $this->assertTrue($response->allowed());
    }

    public function test_user_cannot_delete_others_review(): void
    {
        $otherUser = User::factory()->create();
        $response = $this->policy->delete($otherUser, $this->review);
        $this->assertFalse($response->allowed());
    }
} 