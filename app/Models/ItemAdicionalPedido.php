<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemAdicionalPedido extends Model
{
    use HasFactory;

    protected $table = 'itens_adicionais_pedido';

    protected $fillable = [
        'item_pedido_id', 'adicional_id', 'adicional_nome',
        'adicional_preco', 'quantidade', 'subtotal',
    ];

    protected $casts = [
        'adicional_preco' => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    public function itemPedido(): BelongsTo
    {
        return $this->belongsTo(ItemPedido::class);
    }

    public function adicional(): BelongsTo
    {
        return $this->belongsTo(Adicional::class);
    }
}
