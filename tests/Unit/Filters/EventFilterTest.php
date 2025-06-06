<?php

namespace Tests\Unit\Filters;

use Tests\TestCase;
use App\Models\Event;
use App\Services\Filters\EventFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Models\User;

class EventFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_by_name(): void
    {
        Event::factory()->create([
            'title' => 'Concert A',
            'reservation_deadline' => now()->addDays(1),
            'date_time' => now()->addDays(2)
        ]);
        Event::factory()->create([
            'title' => 'Party B',
            'reservation_deadline' => now()->addDays(1),
            'date_time' => now()->addDays(2)
        ]);
        
        $request = new Request(['filter' => ['title' => 'Concert']]);
        $query = Event::query();
        
        $filteredEvents = EventFilter::apply($query, $request)->get();
        
        $this->assertCount(1, $filteredEvents);
        $this->assertEquals('Concert A', $filteredEvents->first()->title);
    }

    public function test_filter_by_price_range(): void
    {
        Event::factory()->create([
            'price' => 50,
            'reservation_deadline' => now()->addDays(1),
            'date_time' => now()->addDays(2)
        ]);
        Event::factory()->create([
            'price' => 150,
            'reservation_deadline' => now()->addDays(1),
            'date_time' => now()->addDays(2)
        ]);
        Event::factory()->create([
            'price' => 250,
            'reservation_deadline' => now()->addDays(1),
            'date_time' => now()->addDays(2)
        ]);
        
        $request = new Request([
            'filter' => [
                'price_min' => 100,
                'price_max' => 200
            ]
        ]);
        
        $filteredEvents = EventFilter::apply(Event::query(), $request)->get();
        
        $this->assertCount(1, $filteredEvents);
        $this->assertEquals(150, $filteredEvents->first()->price);
    }

    public function test_filter_by_date_range(): void
    {
        $pastEvent = Event::factory()->create([
            'date_time' => now()->subDays(5),
            'reservation_deadline' => now()->addDays(1)
        ]);
        
        $futureEvent = Event::factory()->create([
            'date_time' => now()->addDays(5),
            'reservation_deadline' => now()->addDays(6)
        ]);
        
        $request = new Request([
            'filter' => [
                'date_min' => now()->format('Y-m-d'),
                'date_max' => now()->addDays(10)->format('Y-m-d')
            ]
        ]);
        
        $filteredEvents = EventFilter::apply(Event::query(), $request)->get();
        
        $this->assertCount(1, $filteredEvents);
        $this->assertTrue($filteredEvents->first()->date_time > now());
    }

    public function test_sort_events(): void
    {
        Event::factory()->create([
            'price' => 300,
            'title' => 'B Event',
            'reservation_deadline' => now()->addDays(1),
            'date_time' => now()->addDays(2)
        ]);
        Event::factory()->create([
            'price' => 100,
            'title' => 'A Event',
            'reservation_deadline' => now()->addDays(1),
            'date_time' => now()->addDays(2)
        ]);
        
        // Test ascending price sort
        $request = new Request(['sort' => 'price']);
        $events = EventFilter::apply(Event::query(), $request)->get();
        $this->assertEquals(100, $events->first()->price);
        
        // Test descending price sort
        $request = new Request(['sort' => '-price']);
        $events = EventFilter::apply(Event::query(), $request)->get();
        $this->assertEquals(300, $events->first()->price);
        
        // Test title sort
        $request = new Request(['sort' => 'title']);
        $events = EventFilter::apply(Event::query(), $request)->get();
        $this->assertEquals('A Event', $events->first()->title);
    }

    public function test_only_available_events_are_returned(): void
    {
        // Create past deadline event
        Event::factory()->create([
            'reservation_deadline' => now()->subDays(1),
            'date_time' => now()->addDays(2),
            'attendee_limit' => 10
        ]);

        // Create future but full event
        $fullEvent = Event::factory()->create([
            'reservation_deadline' => now()->addDays(1),
            'date_time' => now()->addDays(2),
            'attendee_limit' => 1
        ]);
        
        // Fill the event to its limit
        for ($i = 0; $i < $fullEvent->attendee_limit; $i++) {
            $fullEvent->attendees()->attach(User::factory()->create()->id);
        }

        // Create available event
        Event::factory()->create([
            'reservation_deadline' => now()->addDays(1),
            'date_time' => now()->addDays(2),
            'attendee_limit' => 10
        ]);

        $request = new Request();
        $filteredEvents = EventFilter::apply(Event::query(), $request)->get();

        $this->assertCount(1, $filteredEvents);
    }
} 