<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoAdicional extends Model
{
    use HasFactory;

    protected $table = 'grupos_adicionais';

    protected $fillable = [
        'produto_id', 'nome', 'descricao', 'min_selecao',
        'max_selecao', 'obrigatorio', 'ordem', 'ativo',
    ];

    protected $casts = [
        'obrigatorio' => 'boolean',
        'ativo'       => 'boolean',
    ];

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    public function adicionais(): HasMany
    {
        return $this->hasMany(Adicional::class, 'grupo_id')->where('ativo', true)->orderBy('ordem');
    }
}
