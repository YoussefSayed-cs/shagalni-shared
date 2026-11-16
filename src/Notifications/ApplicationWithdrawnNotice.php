<?php

namespace Shagalni\Shared\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Shagalni\Shared\Models\ApplicationStatusHistory;

class ApplicationWithdrawnNotice extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public string $applicationId;

    public string $jobTitle;

    public string $applicantName;

    public string $status;

    public string $message;

    public function __construct(ApplicationStatusHistory $history)
    {
        $application = $history->application;
        $this->applicationId = $application->id;
        $this->jobTitle = $application->job_title;
        $this->applicantName = $application->user?->full_name ?? 'Applicant';
        $this->status = $history->to_status->value;
        $this->message = $history->message ?? 'The applicant withdrew their application.';
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Application withdrawn for '.$this->jobTitle)
            ->greeting('Hello '.$notifiable->full_name.',')
            ->line($this->applicantName.' withdrew their application for '.$this->jobTitle.'.')
            ->line($this->message);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->applicationId,
            'job_title' => $this->jobTitle,
            'applicant_name' => $this->applicantName,
            'status' => $this->status,
            'message' => $this->message,
        ];
    }
}
