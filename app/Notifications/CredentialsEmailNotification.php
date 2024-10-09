<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CredentialsEmailNotification extends Notification
{
    use Queueable;

    public $plainPassword;

    /**
     * Create a new notification instance.
     */
    public function __construct($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->mailer('smtp')
            ->subject('Your Account')
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your email is: ' . $notifiable->email)
            ->line('Your password is: ' . $this->plainPassword)
            ->line('Please use the above credentials to login to your account.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray(object $notifiable): array
    {
        return [
            'plainPassword' => $this->plainPassword,
        ];
    }
}
