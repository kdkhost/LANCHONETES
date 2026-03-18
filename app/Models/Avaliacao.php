<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Avaliacao extends Model
{
    use HasFactory;

    protected $table = 'avaliacoes';

    protected $fillable = [
        'pedido_id', 'usuario_id', 'loja_id', 'nota_loja',
        'nota_entrega', 'nota_comida', 'comentario', 'tags', 'fotos', 'aprovado', 'anonimo',
    ];

    protected $casts = [
        'fotos'    => 'array',
        'tags'     => 'array',
        'aprovado' => 'boolean',
        'anonimo'  => 'boolean',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }
}
