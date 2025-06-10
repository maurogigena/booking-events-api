<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_by_title(): void
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

        $events = Event::query()
            ->filter(['title' => 'Concert'])
            ->get();

        $this->assertCount(1, $events);
        $this->assertEquals('Concert A', $events->first()->title);
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

        $events = Event::query()
            ->filter([
                'price_min' => 100,
                'price_max' => 200
            ])
            ->get();

        $this->assertCount(1, $events);
        $this->assertEquals(150, $events->first()->price);
    }

    public function test_filter_by_date_range(): void
    {
        $pastEvent = Event::factory()->create([
            'date_time' => now()->subDays(5),
            'reservation_deadline' => now()->addDays(1)
        ]);
        
        $futureEvent = Event::factory()->create([
            'date_time' => now()->addDays(5),
            'reservation_deadline' => now()->addDays(1)
        ]);

        $events = Event::query()
            ->filter([
                'date_min' => now()->format('Y-m-d'),
                'date_max' => now()->addDays(10)->format('Y-m-d')
            ])
            ->get();

        $this->assertCount(1, $events);
        $this->assertTrue($events->first()->date_time > now());
    }

    public function test_sort_events_by_price(): void
    {
        Event::factory()->create([
            'price' => 300,
            'reservation_deadline' => now()->addDays(1)
        ]);
        Event::factory()->create([
            'price' => 100,
            'reservation_deadline' => now()->addDays(1)
        ]);

        // Test ascending price sort
        $events = Event::query()->sort('price')->get();
        $this->assertEquals(100, $events->first()->price);

        // Test descending price sort
        $events = Event::query()->sort('-price')->get();
        $this->assertEquals(300, $events->first()->price);
    }

    public function test_sort_events_by_date(): void
    {
        $laterEvent = Event::factory()->create([
            'date_time' => now()->addDays(5),
            'reservation_deadline' => now()->addDays(1)
        ]);
        $soonerEvent = Event::factory()->create([
            'date_time' => now()->addDays(2),
            'reservation_deadline' => now()->addDays(1)
        ]);

        // Test ascending date sort
        $events = Event::query()->sort('date_time')->get();
        $this->assertTrue($events->first()->date_time < $events->last()->date_time);

        // Test descending date sort
        $events = Event::query()->sort('-date_time')->get();
        $this->assertTrue($events->first()->date_time > $events->last()->date_time);
    }
} 