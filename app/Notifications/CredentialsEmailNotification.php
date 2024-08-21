<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Ichtrojan\Otp\Otp;

class CredentialsEmailNotification extends Notification
{
    use Queueable;

    public $message;
    public $subject;
    public $fromEmail;
    public $mailer;
    private $password;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        $this->message = 'Use the below code for verification process';
        $this->subject = 'Email Verification';
        $this->fromEmail = 'kiagusnaufal310@gmail.com';
        $this->mailer = 'smtp';
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
        $this->password = Str::random(16); // generate a random password

        return (new MailMessage)
            ->mailer('smtp')
            ->subject($this->subject)
            ->greeting('Hello ' . $notifiable->nama_depan) // Add space after greeting
            ->line('Your email is: ' . $notifiable->email)
            ->line('Your password is: ' . $this->password)
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
            'password' => $this->password,
        ];
    }
}