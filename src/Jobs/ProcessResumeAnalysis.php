<?php

namespace Shagalni\Shared\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Shagalni\Shared\Ai\DocumentParser;
use Shagalni\Shared\Ai\ResumeAnalyzer;
use Shagalni\Shared\Ai\UnsupportedResumeException;
use Shagalni\Shared\Enums\AiAnalysisStatus;
use Shagalni\Shared\Models\AiAnalysis;
use Shagalni\Shared\Models\Application;
use Throwable;

class ProcessResumeAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const QUEUE = 'ai';

    public int $tries = 3;

    public int $timeout = 120;

    public bool $failOnTimeout = true;

    public function __construct(public string $applicationId)
    {
        $this->onQueue(self::QUEUE);
    }

    public function backoff(): array
    {
        return [30, 120];
    }

    public function handle(DocumentParser $parser, ResumeAnalyzer $analyzer): void
    {
        $application = Application::with(['resumeSnapshot', 'jobVacancy'])->find($this->applicationId);

        if ($application === null) {
            return;
        }

        $analysis = AiAnalysis::firstOrCreate(
            ['application_id' => $application->id],
            ['status' => AiAnalysisStatus::Pending, 'attempts' => 0]
        );

        if ($analysis->status === AiAnalysisStatus::Completed) {
            return;
        }

        $analysis->update([
            'status' => AiAnalysisStatus::Processing,
            'failure_reason' => null,
        ]);
        $analysis->increment('attempts');

        try {
            $job = $application->jobVacancy;
            $snapshot = $application->resumeSnapshot;

            if ($job === null) {
                throw new UnsupportedResumeException('The job is no longer available for analysis.');
            }

            if ($snapshot === null) {
                throw new UnsupportedResumeException('No resume was found for this application.');
            }

            $text = $parser->extract($snapshot);
            $result = $analyzer->analyze($text, [
                'title' => $job->title,
                'description' => $job->description,
                'requirements' => $job->requirements,
                'required_skills' => $job->required_skills,
                'location' => $job->location,
                'employment_type' => $job->employment_type?->value,
                'work_location_type' => $job->work_location_type?->value,
                'experience_level' => $job->experience_level?->value,
            ]);
        } catch (UnsupportedResumeException $exception) {
            $this->markFailed($exception->getMessage());

            return;
        }

        $analysis->update([
            'status' => AiAnalysisStatus::Completed,
            'match_score' => $result->score,
            'feedback' => $result->feedback,
            'failure_reason' => null,
            'analyzed_at' => now(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        report($exception);
        $this->markFailed('The AI service is temporarily unavailable. You can retry the analysis later.');
    }

    private function markFailed(string $reason): void
    {
        AiAnalysis::where('application_id', $this->applicationId)->update([
            'status' => AiAnalysisStatus::Failed->value,
            'failure_reason' => $reason,
        ]);
    }
}