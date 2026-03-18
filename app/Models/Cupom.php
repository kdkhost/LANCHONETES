<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cupom extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cupons';

    protected $fillable = [
        'loja_id', 'codigo', 'descricao', 'tipo', 'valor',
        'valor_minimo_pedido', 'desconto_maximo', 'usos_maximos',
        'usos_por_usuario', 'usos_realizados', 'valido_de', 'valido_ate',
        'primeiro_pedido', 'ativo',
    ];

    protected $casts = [
        'valor'               => 'decimal:2',
        'valor_minimo_pedido' => 'decimal:2',
        'desconto_maximo'     => 'decimal:2',
        'valido_de'           => 'datetime',
        'valido_ate'          => 'datetime',
        'primeiro_pedido'     => 'boolean',
        'ativo'               => 'boolean',
    ];

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    public function calcularDesconto(float $subtotal): float
    {
        if ($this->tipo === 'percentual') {
            $desconto = $subtotal * ($this->valor / 100);
            if ($this->desconto_maximo) {
                $desconto = min($desconto, $this->desconto_maximo);
            }
            return $desconto;
        }
        if ($this->tipo === 'fixo') {
            return min($this->valor, $subtotal);
        }
        return 0;
    }

    public function estaValido(): bool
    {
        if (!$this->ativo) return false;
        $agora = now();
        if ($this->valido_de && $agora->lt($this->valido_de)) return false;
        if ($this->valido_ate && $agora->gt($this->valido_ate)) return false;
        if ($this->usos_maximos && $this->usos_realizados >= $this->usos_maximos) return false;
        return true;
    }
}
