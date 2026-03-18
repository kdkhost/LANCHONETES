<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacao extends Model
{
    use HasFactory;

    protected $table = 'notificacoes';

    protected $fillable = [
        'usuario_id', 'titulo', 'mensagem', 'tipo',
        'icone', 'url', 'dados', 'lida', 'lida_em',
    ];

    protected $casts = [
        'dados'  => 'array',
        'lida'   => 'boolean',
        'lida_em'=> 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function marcarComoLida(): void
    {
        $this->update(['lida' => true, 'lida_em' => now()]);
    }
}
