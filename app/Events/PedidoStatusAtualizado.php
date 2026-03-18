<?php

namespace App\Events;

use App\Models\Pedido;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PedidoStatusAtualizado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Pedido $pedido) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("pedido.{$this->pedido->id}"),
            new PrivateChannel("loja.{$this->pedido->loja_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'pedido.status';
    }

    public function broadcastWith(): array
    {
        return [
            'pedido_id'     => $this->pedido->id,
            'numero'        => $this->pedido->numero,
            'status'        => $this->pedido->status,
            'status_label'  => $this->pedido->status_label,
            'status_cor'    => $this->pedido->status_cor,
            'atualizado_em' => now()->toISOString(),
        ];
    }
}
