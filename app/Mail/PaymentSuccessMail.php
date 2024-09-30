<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected string $nama_sekolah,
        protected string $logo_sekolah,
        protected string $user_name,
        protected string $payment_name,
        protected string $ds_code,
        protected string $merchant_order_id,
        protected int $nominal,
        protected string $customer_name,
        protected string $payment_method,
        protected string $payment_time,
        protected string $payment_status
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Success Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.PaymentSuccess',
            with: [
                'nama_sekolah' => $this->nama_sekolah,
                'logo_sekolah' => $this->logo_sekolah,
                'username' => $this->user_name,
                'payment_name' => $this->payment_name,
                'ds_code' => $this->ds_code,
                'merchant_order_id' => $this->merchant_order_id,
                'nominal' => $this->nominal,
                'customer_name' => $this->customer_name,
                'payment_method' => $this->payment_method,
                'payment_time' => $this->payment_time,
                'payment_status' => $this->payment_status,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
