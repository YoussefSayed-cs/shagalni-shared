<?php

namespace Shagalni\Shared\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Shagalni\Shared\Models\Application;

class NewApplicationReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public string $applicationId,
        public string $jobTitle,
        public string $applicantName,
        public string $status,
        public string $message,
    ) {}

    public static function forApplication(Application $application): self
    {
        return new self(
            $application->id,
            $application->job_title,
            $application->user?->full_name ?? 'Applicant',
            $application->status->value,
            'A new application has been submitted.'
        );
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New application for '.$this->jobTitle)
            ->greeting('Hello '.$notifiable->full_name.',')
            ->line($this->applicantName.' submitted an application for '.$this->jobTitle.'.')
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
