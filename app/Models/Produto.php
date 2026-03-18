<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Produto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'produtos';

    protected $fillable = [
        'loja_id', 'categoria_id', 'nome', 'slug', 'descricao', 'ingredientes',
        'preco', 'preco_promocional', 'peso_gramas', 'imagem_principal', 'imagens',
        'estoque', 'controla_estoque', 'ativo', 'disponivel', 'destaque', 'novo',
        'tempo_preparo_min', 'ordem', 'vendas_total', 'avaliacao_media',
        'avaliacoes_total', 'informacoes_nutricionais', 'alergicos',
    ];

    protected $casts = [
        'preco'                   => 'decimal:2',
        'preco_promocional'       => 'decimal:2',
        'peso_gramas'             => 'decimal:2',
        'avaliacao_media'         => 'decimal:2',
        'ativo'                   => 'boolean',
        'disponivel'              => 'boolean',
        'destaque'                => 'boolean',
        'novo'                    => 'boolean',
        'controla_estoque'        => 'boolean',
        'imagens'                 => 'array',
        'informacoes_nutricionais'=> 'array',
        'alergicos'               => 'array',
    ];

    protected $appends = ['imagem_url', 'preco_atual', 'tem_promocao'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($produto) {
            if (empty($produto->slug)) {
                $produto->slug = Str::slug($produto->nome);
            }
        });
    }

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function gruposAdicionais(): HasMany
    {
        return $this->hasMany(GrupoAdicional::class, 'produto_id')->where('ativo', true)->orderBy('ordem');
    }

    public function getImagemUrlAttribute(): string
    {
        if ($this->imagem_principal && Str::startsWith($this->imagem_principal, ['http://', 'https://'])) {
            return $this->imagem_principal;
        }

        return $this->imagem_principal
            ? asset('storage/' . $this->imagem_principal)
            : asset('img/produto-default.png');
    }

    public function getPrecoAtualAttribute(): float
    {
        return $this->preco_promocional ?? $this->preco;
    }

    public function getTemPromocaoAttribute(): bool
    {
        return !is_null($this->preco_promocional) && $this->preco_promocional < $this->preco;
    }

    public function getPrecoFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->preco_atual, 2, ',', '.');
    }

    public function estaDisponivel(): bool
    {
        if (!$this->ativo || !$this->disponivel) return false;
        if ($this->controla_estoque && $this->estoque <= 0) return false;
        return true;
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true)->where('disponivel', true);
    }

    public function scopeDestaques($query)
    {
        return $query->ativos()->where('destaque', true);
    }
}
