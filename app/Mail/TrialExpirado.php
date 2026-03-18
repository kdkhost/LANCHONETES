<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialExpirado extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $loja
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🔒 Seu período de teste expirou",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trial_expirado',
            with: [
                'loja' => $this->loja,
                'urlUpgrade' => route('admin.planos.upgrade'),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
