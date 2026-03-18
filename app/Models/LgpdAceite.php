<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LgpdAceite extends Model
{
    protected $table = 'lgpd_aceites';

    protected $fillable = [
        'usuario_id', 'loja_id', 'ip', 'user_agent', 'versao', 'tipo',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }
}
