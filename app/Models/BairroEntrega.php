<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BairroEntrega extends Model
{
    use HasFactory;

    protected $table = 'bairros_entrega';

    protected $fillable = [
        'loja_id', 'nome', 'cidade', 'estado',
        'taxa', 'tempo_estimado_min', 'tempo_estimado_max', 'ativo',
    ];

    protected $casts = [
        'taxa'  => 'decimal:2',
        'ativo' => 'boolean',
    ];

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }
}
