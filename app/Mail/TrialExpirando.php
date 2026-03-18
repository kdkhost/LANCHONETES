<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialExpirando extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $loja,
        public int $diasRestantes,
        public string $dataExpiracao
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "⚠️ Seu período de teste expira em {$this->diasRestantes} dias",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trial_expirando',
            with: [
                'loja' => $this->loja,
                'diasRestantes' => $this->diasRestantes,
                'dataExpiracao' => $this->dataExpiracao,
                'urlUpgrade' => route('admin.planos.upgrade'),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
