<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedByAdmin extends Notification
{
    use Queueable;

    public function __construct(
        public readonly User $adminUser,
    ) {}

    /**
     * @param  User  $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * @param  User  $notifiable
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your password has been changed')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('An administrator has changed your password.')
            ->line('Admin: '.$this->adminUser->name.' ('.$this->adminUser->email.')')
            ->line('Time: '.now()->toDateTimeString())
            ->line('If you did not expect this change, please contact support immediately.');
    }
}
