<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\ReviewRequest;
use App\Services\Filters\ReviewFilter;
use App\Http\Resources\ReviewResource;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    use AuthorizesRequests;

    public function index(Event $event)
    {
        $reviews = $event->reviews()
            ->with(['user', 'event'])
            ->filter(request()->input('filter', []))
            ->sort(request()->query('sort'))
            ->paginate(request()->query('per_page', 15));

        return ReviewResource::collection($reviews);
    }

    public function store(ReviewRequest $request, Event $event)
    {
        $user = $request->user();

        if (!$event->attendees()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        $review = $request->createReview($event->id);

        return response()->json([
            'data' => new ReviewResource($review)
        ], 201);
    }

    public function update(ReviewRequest $request, Review $review)
    {
        $this->authorize('update', $review);

        $validated = $request->validate($request->rules());
        $review->update($validated);

        return response()->json([
            'data' => new ReviewResource($review)
        ]);
    }

    public function replace(ReviewRequest $request, Review $review)
    {
        $this->authorize('update', $review);

        $validated = $request->validate($request->rules());
        $review->update($validated);

        return response()->json([
            'data' => new ReviewResource($review)
        ]);
    }

    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $review->delete();
        return response()->json(200);
    }
}