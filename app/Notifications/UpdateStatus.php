<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class UpdateStatus extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $ppdb;
    protected $pendaftar;

    public function __construct($ppdb, $pendaftar)
    {
        $this->ppdb = $ppdb;
        $this->pendaftar = $pendaftar;
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
        Log::info('Sending email to: ' . $this->pendaftar->email);
        $fullName = $this->pendaftar->nama_depan . ' ' . $this->pendaftar->nama_belakang;
        return (new MailMessage)
            ->mailer('smtp')

            ->subject('Penerimaan Siswa')
            ->greeting('Hello ' . $fullName . ',')
            ->line('New Status: ' . $this->getStatusLabel($this->ppdb->status))
            ->line('Thank you for using our application!');
    }

    protected function getStatusLabel($status)
    {
        $statusLabels = [
            1 => 'Mendaftar',
            2 => 'Telah Membayar',
            3 => 'Lulus',
            4 => 'Ditolak',
        ];

        return $statusLabels[$status] ?? 'Unknown';
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'ppdb_id' => $this->ppdb->id,
            'status' => $this->ppdb->status,
        ];
    }
}
