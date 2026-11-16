<?php

namespace Shagalni\Shared\Listeners;

use Shagalni\Shared\Events\ApplicationStatusUpdated;
use Shagalni\Shared\Notifications\ApplicationStatusChanged;

class NotifyJobSeekerOfStatusChange
{
    public function handle(ApplicationStatusUpdated $event): void
    {
        $application = $event->history->application;
        $user = $application?->user;

        if ($user !== null && $user->canReceiveApplicationNotifications()) {
            $user->notify(new ApplicationStatusChanged($application, $event->history->message));
        }
    }
}
