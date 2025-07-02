<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $frontendUrl = env('FRONTEND_RESET_PASSWORD_URL');

        $resetUrl = "{$frontendUrl}?token={$this->token}&email={$notifiable->email}";

        return (new MailMessage)
            ->subject('Reset Your Password')
            ->line('Click the button below to reset your password:')
            ->action('Reset Password', $resetUrl)
            ->line('If you did not request a password reset, no further action is required.');
    }
}
