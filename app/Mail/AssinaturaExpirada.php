<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AssinaturaExpirada extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $assinatura
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🔒 Sua assinatura expirou",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.assinatura_expirada',
            with: [
                'assinatura' => $this->assinatura,
                'urlUpgrade' => route('admin.planos.upgrade'),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
