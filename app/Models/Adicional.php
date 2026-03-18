<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Adicional extends Model
{
    use HasFactory;

    protected $table = 'adicionais';

    protected $fillable = [
        'grupo_id', 'nome', 'descricao', 'preco', 'estoque', 'ativo', 'ordem',
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'ativo' => 'boolean',
    ];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoAdicional::class, 'grupo_id');
    }
}
