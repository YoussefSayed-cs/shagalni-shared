<?php

namespace Shagalni\Shared;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Shagalni\Shared\Events\ApplicationSubmitted;
use Shagalni\Shared\Events\ApplicationStatusUpdated;
use Shagalni\Shared\Events\ApplicationWithdrawn;
use Shagalni\Shared\Listeners\NotifyCompanyOwnerOfNewApplication;
use Shagalni\Shared\Listeners\NotifyCompanyOwnerOfWithdrawal;
use Shagalni\Shared\Listeners\NotifyJobSeekerOfStatusChange;
use Shagalni\Shared\Models\ApplicationStatusHistory;
use Shagalni\Shared\Observers\ApplicationStatusHistoryObserver;

class SharedServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        ApplicationStatusHistory::observe(ApplicationStatusHistoryObserver::class);

        Event::listen(ApplicationStatusUpdated::class, NotifyJobSeekerOfStatusChange::class);
        Event::listen(ApplicationWithdrawn::class, NotifyCompanyOwnerOfWithdrawal::class);
        Event::listen(ApplicationSubmitted::class, NotifyCompanyOwnerOfNewApplication::class);
    }
}
