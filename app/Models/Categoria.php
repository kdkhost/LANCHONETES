<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Categoria extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categorias';

    protected $fillable = [
        'loja_id', 'categoria_pai_id', 'nome', 'slug', 'descricao',
        'imagem', 'icone', 'ordem', 'ativo', 'destaque',
    ];

    protected $casts = [
        'ativo'    => 'boolean',
        'destaque' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($categoria) {
            if (empty($categoria->slug)) {
                $categoria->slug = Str::slug($categoria->nome);
            }
        });
    }

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    public function categoriaPai(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_pai_id');
    }

    public function subcategorias(): HasMany
    {
        return $this->hasMany(Categoria::class, 'categoria_pai_id');
    }

    public function produtos(): HasMany
    {
        return $this->hasMany(Produto::class)->where('ativo', true)->orderBy('ordem');
    }

    public function getImagemUrlAttribute(): string
    {
        return $this->imagem
            ? asset('storage/' . $this->imagem)
            : asset('img/categoria-default.png');
    }
}
