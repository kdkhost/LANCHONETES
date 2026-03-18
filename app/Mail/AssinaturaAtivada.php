<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AssinaturaAtivada extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $assinatura
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "✅ Sua assinatura foi ativada!",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.assinatura_ativada',
            with: [
                'assinatura' => $this->assinatura,
                'urlPlanos' => route('admin.planos.index'),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
