<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\EventRequest;
use App\Services\Filters\EventFilter;
use App\Http\Resources\EventListResource;
use App\Http\Resources\EventResource;
use App\Traits\ApiResponses;

class EventController extends Controller
{
    use AuthorizesRequests, ApiResponses;

    public function index(EventRequest $request)
    {
        $events = EventFilter::apply(Event::query(), $request)
                    ->with(['creator'])
                    ->paginate(request()->query('per_page', 15));
                    
        return EventListResource::collection($events);
    }

    public function show($id)
    {
        $event = Event::with(['creator', 'attendees', 'reviews.user'])
            ->findOrFail($id);
        
        return new EventResource($event);
    }

    public function store(EventRequest $request)
    {
        $event = Event::create([
            'user_id' => $request->user()->id,
            ...$request->validated()
        ]);
        
        return $this->success('Event created successfully', $event, 201);
    }

    public function update(EventRequest $request, $id)
    {
        $event = Event::findOrFail($id);
        $this->authorize('update', $event);
        
        $event->update($request->validated());
        return $this->ok('Event updated successfully', $event);
    }

    public function replace(EventRequest $request, $id)
    {
        $event = Event::findOrFail($id);
        $this->authorize('update', $event);

        $event->update($request->validated());
        return $this->ok('Event replaced successfully', $event);
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $this->authorize('delete', $event);

        $event->delete();
        return $this->ok("Event {$event->id} deleted successfully");
    }
}
