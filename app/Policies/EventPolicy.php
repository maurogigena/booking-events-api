<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EventPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Event $event): Response
    {
        return $user->id === $event->user_id
            ? Response::allow()
            : Response::deny('You are not authorized to update this event.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Event $event): Response
    {
        return $user->id === $event->user_id
            ? Response::allow()
            : Response::deny('You are not authorized to delete this event.');
    }
}