<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemPedido extends Model
{
    use HasFactory;

    protected $table = 'itens_pedido';

    protected $fillable = [
        'pedido_id', 'produto_id', 'produto_nome', 'produto_descricao',
        'produto_preco', 'quantidade', 'subtotal', 'observacoes',
    ];

    protected $casts = [
        'produto_preco' => 'decimal:2',
        'subtotal'      => 'decimal:2',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    public function adicionais(): HasMany
    {
        return $this->hasMany(ItemAdicionalPedido::class);
    }
}
