<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\ReviewRequest;
use App\Services\Filters\ReviewFilter;
use App\Http\Resources\ReviewResource;
use App\Traits\ApiResponses;

class ReviewController extends Controller
{
    use AuthorizesRequests, ApiResponses;

    public function index($eventId)
    {
        $reviews = ReviewFilter::apply(Review::where('event_id', $eventId)
                    ->with(['user', 'event']), request())
                    ->paginate(request()->query('per_page', 15));

        return ReviewResource::collection($reviews);
    }

    public function store(ReviewRequest $request, $eventId)
    {
        $user = $request->user();
        $event = Event::findOrFail($eventId);

        if (!$event->attendees()->where('user_id', $user->id)->exists()) {
            return $this->error('Only users who have reserved can leave a review', 403);
        }

        $review = $request->createReview($eventId);

        return $this->success('Review created successfully', new ReviewResource($review), 201);
    }

    public function update(ReviewRequest $request, $id)
    {
        $review = Review::findOrFail($id);
        $this->authorize('update', $review);

        $validated = $request->validate($request->rules());
        $review->update($validated);

        return $this->ok('Review updated successfully', $review);
    }

    public function replace(ReviewRequest $request, $id)
    {
        $review = Review::findOrFail($id);
        $this->authorize('update', $review);

        $validated = $request->validate($request->rules());
        $review->update($validated);

        return $this->ok('Review replaced successfully', $review);
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $this->authorize('delete', $review);

        $review->delete();
        return $this->ok('Review deleted successfully');
    }
}