<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\EventRequest;
use App\Http\Resources\EventListResource;
use App\Http\Resources\EventResource;
use App\Traits\ApiResponses;

class EventController extends Controller
{
    use AuthorizesRequests, ApiResponses;

    public function index()
    {
        $events = Event::query()
            ->filter(request()->input('filter', []))
            ->sort(request()->query('sort'))
            ->paginate(request()->query('per_page', 15));
                    
        return EventListResource::collection($events);
    }

    public function show(Event $event)
    {
        return new EventResource(
            $event->loadCount('attendees')
                  ->load(['reviews' => function($query) {
                      $query->latest();
                  }])
        );
    }

    public function store(EventRequest $request)
    {
        $event = Event::create([
            'user_id' => $request->user()->id,
            ...$request->validated()
        ]);
        
        return $this->success('Event created successfully', $event, 201);
    }

    public function update(EventRequest $request, Event $event)
    {
        $this->authorize('update', $event);
        
        $event->update($request->validated());
        return $this->success('Event updated successfully', $event);
    }

    public function replace(EventRequest $request, Event $event)
    {
        $this->authorize('update', $event);

        $event->update($request->validated());
        return $this->success('Event replaced successfully', $event);
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        $event->delete();
        return $this->success("Event {$event->id} deleted successfully");
    }
}
