<?php

namespace Shagalni\Shared\Listeners;

use Shagalni\Shared\Enums\AiAnalysisStatus;
use Shagalni\Shared\Events\ApplicationSubmitted;
use Shagalni\Shared\Jobs\ProcessResumeAnalysis;
use Shagalni\Shared\Models\AiAnalysis;
use Throwable;

class QueueResumeAnalysis
{
    public function handle(ApplicationSubmitted $event): void
    {
        try {
            AiAnalysis::firstOrCreate(
                ['application_id' => $event->application->id],
                ['status' => AiAnalysisStatus::Pending, 'attempts' => 0]
            );

            ProcessResumeAnalysis::dispatch($event->application->id);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}