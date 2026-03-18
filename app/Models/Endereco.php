<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Endereco extends Model
{
    use HasFactory;

    protected $table = 'enderecos';

    protected $fillable = [
        'usuario_id', 'apelido', 'destinatario', 'cep', 'logradouro',
        'numero', 'complemento', 'bairro', 'cidade', 'estado',
        'latitude', 'longitude', 'principal', 'ativo',
    ];

    protected $casts = [
        'principal' => 'boolean',
        'ativo'     => 'boolean',
        'latitude'  => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function getEnderecoCompletoAttribute(): string
    {
        return "{$this->logradouro}, {$this->numero}" .
               ($this->complemento ? " - {$this->complemento}" : '') .
               ", {$this->bairro}, {$this->cidade}/{$this->estado} - CEP: {$this->cep}";
    }

    public function getEnderecoResumidoAttribute(): string
    {
        return "{$this->logradouro}, {$this->numero}, {$this->bairro}";
    }
}
