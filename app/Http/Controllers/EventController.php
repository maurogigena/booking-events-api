<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\EventRequest;
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
                    
        return EventResource::collection($events);
    }

    public function show(Event $event)
    {
        if ($event->reservation_deadline <= now() || ($event->attendees_count ?? 0) >= $event->attendee_limit) {
            return $this->error(403);
        }

        return new EventResource(
            $event->loadCount('attendees')
                ->load(['reviews' => fn($query) => $query->latest()])
        );
    }

    public function store(EventRequest $request)
    {
        $event = Event::create([
            'user_id' => $request->user()->id,
            ...$request->validated()
        ]);
        
        return $this->success(new EventResource($event));
    }

    public function update(EventRequest $request, Event $event)
    {
        $this->authorize('update', $event);
        
        $event->update($request->validated());
        return $this->success(new EventResource($event));
    }

    public function replace(EventRequest $request, Event $event)
    {
        $this->authorize('update', $event);

        $event->update($request->validated());
        return $this->success(new EventResource($event));
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        $event->delete();
        return $this->success(200);
    }
}
