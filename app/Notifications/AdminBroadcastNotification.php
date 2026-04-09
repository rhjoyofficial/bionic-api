<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminBroadcastNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $subject,
        public readonly string $message,
        public readonly array  $channels = ['database'],
    ) {}

    /**
     * Determine the notification channels for the given notifiable.
     */
    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    /**
     * Build the mail representation.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line($this->message)
            ->line('This message was sent by the admin team.');
    }

    /**
     * Build the database representation (stored in notifications table).
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'subject' => $this->subject,
            'message' => $this->message,
            'channel' => 'database',
        ];
    }

    /**
     * Get the array representation (fallback / broadcasting).
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
