<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Loja extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lojas';

    protected $fillable = [
        'nome', 'slug', 'cnpj', 'telefone', 'whatsapp', 'email',
        'logo', 'banner', 'descricao', 'cep', 'logradouro', 'numero',
        'complemento', 'bairro', 'cidade', 'estado', 'latitude', 'longitude',
        'raio_entrega_km', 'cor_primaria', 'cor_secundaria', 'pedido_minimo',
        'tempo_entrega_min', 'tempo_entrega_max', 'ativo', 'aceita_retirada',
        'aceita_entrega', 'aceita_pagamento_entrega', 'tipo_taxa_entrega',
        'taxa_entrega_fixa', 'taxa_por_km', 'km_gratis', 'chave_pix',
        'mercadopago_public_key', 'mercadopago_access_token', 'evolution_instance',
        'notificacoes_whatsapp', 'horarios_funcionamento', 'configuracoes',
        'avaliacao_media', 'avaliacoes_total',
        'popup_saida_ativo', 'popup_saida_titulo', 'popup_saida_texto',
        'popup_saida_desconto_tipo', 'popup_saida_desconto_valor',
        'popup_saida_cupom', 'popup_saida_imagem', 'popup_saida_validade_min',
        'popup_promo_ativo', 'popup_promo_titulo', 'popup_promo_texto',
        'popup_promo_imagem', 'popup_promo_delay_seg', 'popup_promo_expira_em', 'popup_promo_url',
        'nfe_ativo', 'nfe_ambiente', 'nfe_token', 'nfe_cnpj_emitente',
        'nfe_razao_social', 'nfe_serie', 'nfe_numero_atual', 'nfe_provedor',
        'lgpd_texto_cookies', 'lgpd_url_politica', 'lgpd_url_termos',
        'cozinha_ativo', 'cozinha_pin', 'wpp_templates',
        'horario_automatico', 'horario_abertura', 'horario_fechamento', 'dias_funcionamento',
        'plano_id', 'trial_expira_em', 'trial_utilizado', 'limitacoes_plano',
    ];

    protected $casts = [
        'ativo'                      => 'boolean',
        'aceita_retirada'            => 'boolean',
        'aceita_entrega'             => 'boolean',
        'aceita_pagamento_entrega'   => 'boolean',
        'notificacoes_whatsapp'      => 'boolean',
        'latitude'                   => 'decimal:8',
        'longitude'                  => 'decimal:8',
        'raio_entrega_km'            => 'decimal:2',
        'pedido_minimo'              => 'decimal:2',
        'taxa_entrega_fixa'          => 'decimal:2',
        'taxa_por_km'                => 'decimal:2',
        'km_gratis'                  => 'decimal:2',
        'horarios_funcionamento'        => 'array',
        'configuracoes'                 => 'array',
        'mercadopago_access_token'      => 'encrypted',
        'avaliacao_media'               => 'decimal:2',
        'avaliacoes_total'              => 'integer',
        'popup_saida_ativo'             => 'boolean',
        'popup_saida_desconto_valor'    => 'decimal:2',
        'popup_saida_validade_min'      => 'integer',
        'popup_promo_ativo'             => 'boolean',
        'popup_promo_delay_seg'         => 'integer',
        'popup_promo_expira_em'         => 'datetime',
        'nfe_ativo'                     => 'boolean',
        'nfe_numero_atual'              => 'integer',
        'cozinha_ativo'                 => 'boolean',
        'wpp_templates'                 => 'array',
        'horario_automatico'            => 'boolean',
        'dias_funcionamento'            => 'array',
        'trial_expira_em'               => 'date',
        'trial_utilizado'               => 'boolean',
        'limitacoes_plano'              => 'array',
    ];

    protected $hidden = ['mercadopago_access_token'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($loja) {
            if (empty($loja->slug)) {
                $loja->slug = Str::slug($loja->nome);
            }
        });
    }

    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class)->orderBy('ordem');
    }

    public function produtos(): HasMany
    {
        return $this->hasMany(Produto::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class);
    }

    public function funcionarios(): HasMany
    {
        return $this->hasMany(Funcionario::class);
    }

    public function entregadores(): HasMany
    {
        return $this->hasMany(Funcionario::class)->where('e_entregador', true)->where('ativo', true);
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function bairrosEntrega(): HasMany
    {
        return $this->hasMany(BairroEntrega::class)->where('ativo', true);
    }

    public function cupons(): HasMany
    {
        return $this->hasMany(Cupom::class);
    }

    public function banners(): HasMany
    {
        return $this->hasMany(Banner::class)->where('ativo', true)->orderBy('ordem');
    }

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class);
    }

    public function assinatura(): HasOne
    {
        return $this->hasOne(Assinatura::class)->latest();
    }

    public function estaEmTrial(): bool
    {
        return $this->trial_expira_em && 
               $this->trial_expira_em->isFuture() && 
               !$this->trial_utilizado;
    }

    public function trialExpirado(): bool
    {
        return $this->trial_expira_em && 
               $this->trial_expira_em->isPast();
    }

    public function estaBloqueadaPorPlano(): bool
    {
        if ($this->estaEmTrial()) {
            return false; // Trial tem acesso total
        }

        if ($this->assinatura?->estaAtiva()) {
            return false; // Assinatura ativa
        }

        return true; // Bloqueada
    }

    public function podeCriarProdutos(): bool
    {
        if ($this->estaEmTrial()) {
            return true;
        }

        return $this->assinatura?->podeCriarProdutos() ?? false;
    }

    public function podeConfigurarPagamento(): bool
    {
        if ($this->estaEmTrial()) {
            return true;
        }

        return $this->assinatura?->podeConfigurarPagamento() ?? false;
    }

    public function podeVender(): bool
    {
        if ($this->estaEmTrial()) {
            return true;
        }

        return $this->assinatura?->podeVender() ?? false;
    }

    public function getDiasRestantesTrialAttribute(): int
    {
        if (!$this->trial_expira_em) {
            return 0;
        }
        
        return max(0, now()->diffInDays($this->trial_expira_em, false));
    }

    public function getStatusPlanoAttribute(): string
    {
        if ($this->estaEmTrial()) {
            return "Trial ({$this->dias_restantes_trial} dias restantes)";
        }

        if ($this->assinatura?->estaAtiva()) {
            return "Assinatura {$this->assinatura->plano->nome}";
        }

        return 'Bloqueada';
    }

    public function avaliacoes(): HasMany
    {
        return $this->hasMany(Avaliacao::class);
    }

    public function notasFiscais(): HasMany
    {
        return $this->hasMany(NotaFiscal::class);
    }

    public function wppTemplate(string $chave): string
    {
        $templates = $this->wpp_templates ?? [];
        return $templates[$chave] ?? config("lanchonete.wpp_templates.{$chave}", '');
    }

    public function estaAberta(): bool
    {
        // Se horário automático está ativo, usa essa lógica
        if ($this->horario_automatico) {
            return $this->verificarHorarioAutomatico();
        }

        // Caso contrário, usa horários_funcionamento (legado)
        $horarios = $this->horarios_funcionamento;
        if (empty($horarios)) return true;

        $diaSemana = strtolower(now()->locale('pt_BR')->isoFormat('dddd'));
        $mapa = [
            'segunda-feira' => 'segunda',
            'terça-feira'   => 'terca',
            'quarta-feira'  => 'quarta',
            'quinta-feira'  => 'quinta',
            'sexta-feira'   => 'sexta',
            'sábado'        => 'sabado',
            'domingo'       => 'domingo',
        ];
        $chave = $mapa[$diaSemana] ?? null;
        if (!$chave || !isset($horarios[$chave])) return true;

        $h = $horarios[$chave];
        if (empty($h['ativo'])) return false;

        $agora = now()->format('H:i');
        return $agora >= $h['abertura'] && $agora <= $h['fechamento'];
    }

    public function verificarHorarioAutomatico(): bool
    {
        if (!$this->horario_automatico || !$this->horario_abertura || !$this->horario_fechamento) {
            return $this->ativo;
        }

        // Verificar se hoje é dia de funcionamento
        $diaAtual = (int) now()->dayOfWeek; // 0=domingo, 1=segunda, ..., 6=sábado
        $diasFuncionamento = $this->dias_funcionamento ?? [];
        
        if (!empty($diasFuncionamento) && !in_array($diaAtual, $diasFuncionamento)) {
            return false;
        }

        // Verificar horário
        $agora = now()->format('H:i:s');
        return $agora >= $this->horario_abertura && $agora <= $this->horario_fechamento;
    }

    public static function validarCnpjAlfanumerico(?string $cnpj): bool
    {
        if (!$cnpj) return true; // CNPJ é opcional
        
        // Remove formatação
        $cnpj = preg_replace('/[^A-Z0-9]/i', '', strtoupper($cnpj));
        
        // CNPJ alfanumérico: 14 caracteres (letras A-Z e números 0-9)
        // Formato: XXXX.XXXX.XXXX/XX (sem dígitos verificadores no novo formato)
        if (strlen($cnpj) !== 14) {
            return false;
        }

        // Validar se contém apenas letras e números
        return preg_match('/^[A-Z0-9]{14}$/', $cnpj) === 1;
    }

    public function getCnpjFormatadoAttribute(): string
    {
        if (!$this->cnpj) return '';
        
        $cnpj = preg_replace('/[^A-Z0-9]/i', '', strtoupper($this->cnpj));
        
        // Formato: XXXX.XXXX.XXXX/XX
        if (strlen($cnpj) === 14) {
            return substr($cnpj, 0, 4) . '.' . 
                   substr($cnpj, 4, 4) . '.' . 
                   substr($cnpj, 8, 4) . '/' . 
                   substr($cnpj, 12, 2);
        }
        
        return $this->cnpj;
    }

    public function temEntregadorDisponivel(): bool
    {
        return $this->entregadores()
            ->where('disponivel_entregas', true)
            ->exists();
    }

    public function getEnderecoCompletoAttribute(): string
    {
        return "{$this->logradouro}, {$this->numero}" .
               ($this->complemento ? " - {$this->complemento}" : '') .
               ", {$this->bairro}, {$this->cidade}/{$this->estado}";
    }

    public function getLogoUrlAttribute(): string
    {
        if ($this->logo && Str::startsWith($this->logo, ['http://', 'https://'])) {
            return $this->logo;
        }

        return $this->logo
            ? asset('storage/' . $this->logo)
            : asset('img/loja-default.png');
    }

    public function getBannerUrlAttribute(): string
    {
        if ($this->banner && Str::startsWith($this->banner, ['http://', 'https://'])) {
            return $this->banner;
        }

        return $this->banner
            ? asset('storage/' . $this->banner)
            : asset('img/banner-default.jpg');
    }
}
