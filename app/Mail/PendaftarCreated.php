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
        $html = '<h1>Pendaftaran Berhasil</h1>';
        $html .= '<p>Data pendaftaran:</p>';
        $html .= '<pre>' . json_encode($this->pendaftar, JSON_PRETTY_PRINT) . '</pre>';
    
        return $this->subject('Pendaftaran Berhasil')
                    ->html($html);
    }
}