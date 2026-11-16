<?php

namespace Shagalni\Shared\Listeners;

use Shagalni\Shared\Events\ApplicationWithdrawn;
use Shagalni\Shared\Notifications\ApplicationWithdrawnNotice;

class NotifyCompanyOwnerOfWithdrawal
{
    public function handle(ApplicationWithdrawn $event): void
    {
        $owner = $event->history->application?->company?->owner;

        if ($owner !== null) {
            $owner->notify(new ApplicationWithdrawnNotice($event->history));
        }
    }
}
