<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Pedido extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pedidos';

    protected $fillable = [
        'loja_id', 'usuario_id', 'numero', 'status', 'tipo_entrega',
        'endereco_id', 'endereco_cep', 'endereco_logradouro', 'endereco_numero',
        'endereco_complemento', 'endereco_bairro', 'endereco_cidade', 'endereco_estado',
        'endereco_latitude', 'endereco_longitude', 'subtotal', 'taxa_entrega',
        'desconto', 'total', 'observacoes', 'cupom_codigo', 'tempo_estimado_min',
        'confirmado_em', 'entregue_em', 'cancelado_em', 'motivo_cancelamento',
        'entregador_id', 'link_rastreamento', 'historico_status',
    ];

    protected $casts = [
        'subtotal'            => 'decimal:2',
        'taxa_entrega'        => 'decimal:2',
        'desconto'            => 'decimal:2',
        'total'               => 'decimal:2',
        'endereco_latitude'   => 'decimal:8',
        'endereco_longitude'  => 'decimal:8',
        'confirmado_em'       => 'datetime',
        'entregue_em'         => 'datetime',
        'cancelado_em'        => 'datetime',
        'historico_status'    => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($pedido) {
            if (empty($pedido->numero)) {
                $pedido->numero = self::gerarNumero();
            }
        });
    }

    public static function gerarNumero(): string
    {
        do {
            $numero = '#' . strtoupper(Str::random(2)) . now()->format('ymd') . rand(100, 999);
        } while (self::where('numero', $numero)->exists());
        return $numero;
    }

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function endereco(): BelongsTo
    {
        return $this->belongsTo(Endereco::class);
    }

    public function entregadorFuncionario(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class, 'entregador_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ItemPedido::class);
    }

    public function pagamento(): HasOne
    {
        return $this->hasOne(Pagamento::class)->latest();
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class);
    }

    public function entrega(): HasOne
    {
        return $this->hasOne(Entrega::class);
    }

    public function avaliacao(): HasOne
    {
        return $this->hasOne(Avaliacao::class);
    }

    public function notaFiscal(): HasOne
    {
        return $this->hasOne(NotaFiscal::class);
    }

    public function atualizarStatus(string $novoStatus, ?string $observacao = null): void
    {
        $historico = $this->historico_status ?? [];
        $historico[] = [
            'status'    => $novoStatus,
            'anterior'  => $this->status,
            'data'      => now()->toISOString(),
            'observacao'=> $observacao,
        ];

        $dados = ['status' => $novoStatus, 'historico_status' => $historico];

        match ($novoStatus) {
            'confirmado'       => $dados['confirmado_em'] = now(),
            'entregue'         => $dados['entregue_em'] = now(),
            'cancelado'        => $dados['cancelado_em'] = now(),
            default            => null,
        };

        if ($observacao && $novoStatus === 'cancelado') {
            $dados['motivo_cancelamento'] = $observacao;
        }

        $this->update($dados);
    }

    public function getStatusLabelAttribute(): string
    {
        return config('lanchonete.pedido.status')[$this->status] ?? $this->status;
    }

    public function getStatusCorAttribute(): string
    {
        return config('lanchonete.pedido.cores_status')[$this->status] ?? '#6C757D';
    }

    public function getTotalFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->total, 2, ',', '.');
    }

    public function podeCancelar(): bool
    {
        return in_array($this->status, ['aguardando_pagamento', 'pagamento_aprovado', 'confirmado']);
    }

    public function estaAtivo(): bool
    {
        return !in_array($this->status, ['entregue', 'cancelado', 'recusado']);
    }

    public function scopeAtivos($query)
    {
        return $query->whereNotIn('status', ['entregue', 'cancelado', 'recusado']);
    }

    public function scopePorLoja($query, int $lojaId)
    {
        return $query->where('loja_id', $lojaId);
    }
}
