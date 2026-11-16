<?php

namespace Shagalni\Shared\Observers;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Event;
use Shagalni\Shared\Enums\ApplicationStatus;
use Shagalni\Shared\Events\ApplicationStatusUpdated;
use Shagalni\Shared\Events\ApplicationWithdrawn;
use Shagalni\Shared\Models\ApplicationStatusHistory;

class ApplicationStatusHistoryObserver implements ShouldHandleEventsAfterCommit
{
    public function created(ApplicationStatusHistory $history): void
    {
        if ($history->from_status === null) {
            return;
        }

        if ($history->to_status === ApplicationStatus::Withdrawn) {
            Event::dispatch(new ApplicationWithdrawn($history));

            return;
        }

        Event::dispatch(new ApplicationStatusUpdated($history));
    }
}
