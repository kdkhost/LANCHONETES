<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'nome', 'email', 'senha', 'telefone', 'whatsapp', 'cpf',
        'data_nascimento', 'genero', 'foto_perfil', 'role', 'loja_id',
        'ativo', 'dispositivo_push_token', 'preferencias',
        'email_verificado_em', 'ultimo_acesso_em',
        'token_redefinicao', 'token_redefinicao_expira_em',
    ];

    protected $hidden = ['senha', 'remember_token'];

    protected $casts = [
        'email_verificado_em' => 'datetime',
        'ultimo_acesso_em'    => 'datetime',
        'data_nascimento'     => 'date',
        'ativo'               => 'boolean',
        'preferencias'        => 'array',
        'senha'                       => 'hashed',
        'token_redefinicao_expira_em' => 'datetime',
    ];

    protected $appends = ['foto_perfil_url'];

    public function getAuthPassword(): string
    {
        return $this->senha;
    }

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    public function funcionario(): HasOne
    {
        return $this->hasOne(Funcionario::class);
    }

    public function enderecos(): HasMany
    {
        return $this->hasMany(Endereco::class)->orderByDesc('principal');
    }

    public function enderecoPrincipal(): HasOne
    {
        return $this->hasOne(Endereco::class)->where('principal', true)->latestOfMany();
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class)->latest();
    }

    public function notificacoes(): HasMany
    {
        return $this->hasMany(Notificacao::class)->latest();
    }

    public function notificacoesNaoLidas(): HasMany
    {
        return $this->hasMany(Notificacao::class)->where('lida', false);
    }

    public function avaliacoes(): HasMany
    {
        return $this->hasMany(Avaliacao::class);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isGerente(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'gerente']);
    }

    public function isEntregador(): bool
    {
        return $this->role === 'entregador';
    }

    public function isCliente(): bool
    {
        return $this->role === 'cliente';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->role === $roles;
        }
        return in_array($this->role, $roles);
    }

    public function getFotoPerfilUrlAttribute(): string
    {
        return $this->foto_perfil
            ? asset('storage/' . $this->foto_perfil)
            : asset('img/avatar-default.png');
    }

    public function getNomeAbreviadoAttribute(): string
    {
        $partes = explode(' ', $this->nome);
        if (count($partes) >= 2) {
            return $partes[0] . ' ' . $partes[count($partes) - 1];
        }
        return $this->nome;
    }
}
