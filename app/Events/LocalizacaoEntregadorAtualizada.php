<?php

namespace App\Events;

use App\Models\Entrega;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocalizacaoEntregadorAtualizada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Entrega $entrega,
        public float $latitude,
        public float $longitude
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("rastreamento.{$this->entrega->token_rastreamento}"),
            new Channel("pedido.{$this->entrega->pedido_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'localizacao.atualizada';
    }

    public function broadcastWith(): array
    {
        return [
            'latitude'     => $this->latitude,
            'longitude'    => $this->longitude,
            'atualizado_em'=> now()->toISOString(),
            'token'        => $this->entrega->token_rastreamento,
        ];
    }
}
