<?php

namespace Shagalni\Shared\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Shagalni\Shared\Models\ApplicationStatusHistory;

class ApplicationWithdrawn
{
    use Dispatchable, SerializesModels;

    public function __construct(public ApplicationStatusHistory $history) {}
}
