<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\EventRequest;
use App\Http\Resources\EventResource;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    use AuthorizesRequests;

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
        $isDeadlinePassed = $event->reservation_deadline <= now();
        $isEventFull = $event->attendees()->count() >= $event->attendee_limit;
        
        if ($isDeadlinePassed || $isEventFull) {
            abort(403);
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
        
        return response()->json(['data' => new EventResource($event)], 201);
    }

    public function update(EventRequest $request, Event $event)
    {
        $this->authorize('update', $event);
        
        $event->update($request->validated());
        return response()->json(['data' => new EventResource($event)]);
    }

    public function replace(EventRequest $request, Event $event)
    {
        $this->authorize('update', $event);

        $event->update($request->validated());
        return response()->json(['data' => new EventResource($event)]);
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        $event->delete();
        return response()->json(200);
    }
}
