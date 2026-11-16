<?php

namespace Shagalni\Shared\Listeners;

use Shagalni\Shared\Events\ApplicationSubmitted;
use Shagalni\Shared\Notifications\NewApplicationReceived;

class NotifyCompanyOwnerOfNewApplication
{
    public function handle(ApplicationSubmitted $event): void
    {
        $owner = $event->application->company?->owner;

        if ($owner !== null) {
            $owner->notify(NewApplicationReceived::forApplication($event->application));
        }
    }
}
