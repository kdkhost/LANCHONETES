<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RedefinirSenhaNotification extends Notification
{
    public function __construct(private string $link) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Redefinição de Senha')
            ->greeting('Olá, ' . $notifiable->nome . '!')
            ->line('Recebemos uma solicitação para redefinir a senha da sua conta.')
            ->action('Redefinir Senha', $this->link)
            ->line('Este link expira em 2 horas.')
            ->line('Se você não solicitou a redefinição, ignore este e-mail.');
    }
}
