<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Banner extends Model
{
    use HasFactory;

    protected $table = 'banners';

    protected $fillable = [
        'loja_id', 'titulo', 'imagem', 'url',
        'ordem', 'ativo', 'valido_de', 'valido_ate',
    ];

    protected $casts = [
        'ativo'     => 'boolean',
        'valido_de' => 'datetime',
        'valido_ate'=> 'datetime',
    ];

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    public function getImagemUrlAttribute(): string
    {
        return asset('storage/' . $this->imagem);
    }

    public function estaAtivo(): bool
    {
        if (!$this->ativo) return false;
        $agora = now();
        if ($this->valido_de && $agora->lt($this->valido_de)) return false;
        if ($this->valido_ate && $agora->gt($this->valido_ate)) return false;
        return true;
    }
}
