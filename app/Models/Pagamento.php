<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagamento extends Model
{
    use HasFactory;

    protected $table = 'pagamentos';

    protected $fillable = [
        'pedido_id', 'metodo', 'status', 'valor',
        'mp_payment_id', 'mp_preference_id', 'mp_status', 'mp_status_detail',
        'mp_transaction_id', 'pix_qr_code', 'pix_qr_code_base64', 'pix_expiracao',
        'parcelas', 'bandeira_cartao', 'ultimos_digitos_cartao', 'titular_cartao',
        'comprovante', 'resposta_gateway', 'pago_em',
    ];

    protected $casts = [
        'valor'             => 'decimal:2',
        'pix_expiracao'     => 'datetime',
        'pago_em'           => 'datetime',
        'resposta_gateway'  => 'array',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function getMetodoLabelAttribute(): string
    {
        return config('lanchonete.pagamento.metodos')[$this->metodo] ?? $this->metodo;
    }

    public function getStatusLabelAttribute(): string
    {
        return config('lanchonete.pagamento.status')[$this->status] ?? $this->status;
    }

    public function estaAprovado(): bool
    {
        return $this->status === 'aprovado';
    }
}
