<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaFiscal extends Model
{
    use HasFactory;

    protected $table = 'notas_fiscais';

    protected $fillable = [
        'pedido_id', 'loja_id', 'numero', 'serie', 'tipo', 'ambiente',
        'status', 'chave_acesso', 'protocolo', 'url_danfe', 'xml_path',
        'motivo_rejeicao', 'dados_emissao', 'resposta_sefaz', 'valor_total',
        'emitida_em', 'cancelada_em',
    ];

    protected $casts = [
        'dados_emissao'  => 'array',
        'resposta_sefaz' => 'array',
        'valor_total'    => 'decimal:2',
        'emitida_em'     => 'datetime',
        'cancelada_em'   => 'datetime',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'pendente'     => 'Pendente',
            'processando'  => 'Processando',
            'autorizada'   => 'Autorizada',
            'cancelada'    => 'Cancelada',
            'rejeitada'    => 'Rejeitada',
        ][$this->status] ?? $this->status;
    }

    public function getStatusCorAttribute(): string
    {
        return [
            'pendente'    => '#6c757d',
            'processando' => '#ffc107',
            'autorizada'  => '#28a745',
            'cancelada'   => '#dc3545',
            'rejeitada'   => '#dc3545',
        ][$this->status] ?? '#6c757d';
    }

    public function estaAutorizada(): bool
    {
        return $this->status === 'autorizada';
    }
}
