<?php

namespace Tests\Unit\Policies;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Policies\EventPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventPolicyTest extends TestCase
{
    use RefreshDatabase;

    private EventPolicy $policy;
    private User $user;
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new EventPolicy();
        $this->user = User::factory()->create();
        $this->event = Event::factory()->create([
            'user_id' => $this->user->id,
            'reservation_deadline' => now()->addDays(1),
            'date_time' => now()->addDays(2)
        ]);
    }

    public function test_owner_can_update_event(): void
    {
        $response = $this->policy->update($this->user, $this->event);
        $this->assertTrue($response->allowed());
    }

    public function test_non_owner_cannot_update_event(): void
    {
        $otherUser = User::factory()->create();
        $response = $this->policy->update($otherUser, $this->event);
        $this->assertFalse($response->allowed());
    }

    public function test_owner_can_delete_event(): void
    {
        $response = $this->policy->delete($this->user, $this->event);
        $this->assertTrue($response->allowed());
    }

    public function test_non_owner_cannot_delete_event(): void
    {
        $otherUser = User::factory()->create();
        $response = $this->policy->delete($otherUser, $this->event);
        $this->assertFalse($response->allowed());
    }

    public function test_cannot_update_past_event(): void
    {
        $pastEvent = Event::factory()->create([
            'user_id' => $this->user->id,
            'reservation_deadline' => now()->subDays(2),
            'date_time' => now()->subDay()
        ]);

        $response = $this->policy->update($this->user, $pastEvent);
        $this->assertTrue($response->allowed());
    }

    public function test_cannot_delete_past_event(): void
    {
        $pastEvent = Event::factory()->create([
            'user_id' => $this->user->id,
            'reservation_deadline' => now()->subDays(2),
            'date_time' => now()->subDay()
        ]);

        $response = $this->policy->delete($this->user, $pastEvent);
        $this->assertTrue($response->allowed());
    }
} 