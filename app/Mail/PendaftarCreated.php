<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PendaftarCreated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $pendaftar;

    public function __construct($pendaftar)
    {
        $this->pendaftar = $pendaftar;
    }

    public function build()
    {
        return $this->subject('Pendaftaran Berhasil')
                    ->view('emails.pendaftar_created', ['pendaftar' => $this->pendaftar]);
    }
}