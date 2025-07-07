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
    public function __construct(public int $otp) {}

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
        return (new MailMessage)
            ->subject('Password Reset OTP Request')
            ->greeting('Hello!')
            ->line('You recently requested to reset your password.')
            ->line('Please use the one-time password (OTP) below to proceed:')
            ->line('')
            ->line("🔐 **{$this->otp}**")
            ->line('')
            ->line('This code will expire in **10 minutes** for your security.')
            ->line('If you did not request this, please ignore this email.')
            ->salutation('Thank you,')
            ->salutation(config('app.name') . ' Team');
    }
}
