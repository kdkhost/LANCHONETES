<div style="display:grid;grid-template-columns:1fr 320px;gap:16px;align-items:start">

    {{-- Coluna principal --}}
    <div>
        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-info-circle"></i> Informações Básicas</h3></div>
            <div class="card-admin-body">
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">Nome da Loja *</label>
                        <input type="text" name="nome" class="campo-input" value="{{ old('nome', $loja?->nome) }}" required placeholder="Ex: Lanchonete do Zé">
                        @error('nome')<span class="campo-erro">{{ $message }}</span>@enderror
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Slug (URL) *</label>
                        <input type="text" name="slug" id="lojaSlug" class="campo-input" value="{{ old('slug', $loja?->slug) }}" required placeholder="lanchonete-do-ze">
                        <span class="campo-hint">URL: seusite.com/<span id="previewSlug">...</span></span>
                        @error('slug')<span class="campo-erro">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">CNPJ Alfanumérico</label>
                        <input type="text" name="cnpj" class="campo-input" value="{{ old('cnpj', $loja?->cnpj) }}" placeholder="ABCD.1234.EFGH/56" maxlength="18" style="text-transform:uppercase">
                        <span class="campo-hint">Novo formato: 14 caracteres alfanuméricos (A-Z, 0-9)</span>
                        @error('cnpj')<span class="campo-erro">{{ $message }}</span>@enderror
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">E-mail</label>
                        <input type="email" name="email" class="campo-input" value="{{ old('email', $loja?->email) }}" placeholder="loja@email.com">
                    </div>
                </div>
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">Telefone</label>
                        <input type="text" name="telefone" class="campo-input mascara-telefone" value="{{ old('telefone', $loja?->telefone) }}" placeholder="(11) 98765-4321">
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">WhatsApp</label>
                        <input type="text" name="whatsapp" class="campo-input mascara-telefone" value="{{ old('whatsapp', $loja?->whatsapp) }}" placeholder="(11) 98765-4321">
                    </div>
                </div>
                <div class="campo-grupo">
                    <label class="campo-label">Descrição</label>
                    <textarea name="descricao" class="campo-input" rows="3" placeholder="Breve descrição da loja...">{{ old('descricao', $loja?->descricao) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Endereço --}}
        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-geo-alt"></i> Endereço</h3></div>
            <div class="card-admin-body">
                <div class="campo-row">
                    <div class="campo-grupo" style="max-width:160px">
                        <label class="campo-label">CEP *</label>
                        <input type="text" name="cep" id="lojaCep" class="campo-input mascara-cep" value="{{ old('cep', $loja?->cep) }}" placeholder="00000-000" required>
                    </div>
                </div>
                <div class="campo-row">
                    <div class="campo-grupo" style="flex:2">
                        <label class="campo-label">Logradouro *</label>
                        <input type="text" name="logradouro" id="lojaLogradouro" class="campo-input" value="{{ old('logradouro', $loja?->logradouro) }}" required>
                    </div>
                    <div class="campo-grupo" style="max-width:100px">
                        <label class="campo-label">Número *</label>
                        <input type="text" name="numero" class="campo-input" value="{{ old('numero', $loja?->numero) }}" required>
                    </div>
                </div>
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">Complemento</label>
                        <input type="text" name="complemento" class="campo-input" value="{{ old('complemento', $loja?->complemento) }}" placeholder="Sala, Apto...">
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Bairro *</label>
                        <input type="text" name="bairro" id="lojaBairro" class="campo-input" value="{{ old('bairro', $loja?->bairro) }}" required>
                    </div>
                </div>
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">Cidade *</label>
                        <input type="text" name="cidade" id="lojaCidade" class="campo-input" value="{{ old('cidade', $loja?->cidade) }}" required>
                    </div>
                    <div class="campo-grupo" style="max-width:90px">
                        <label class="campo-label">UF *</label>
                        <input type="text" name="estado" id="lojaEstado" class="campo-input" value="{{ old('estado', $loja?->estado) }}" maxlength="2" required>
                    </div>
                </div>
            </div>
        </div>

        {{-- Entrega --}}
        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-bicycle"></i> Configurações de Entrega</h3></div>
            <div class="card-admin-body">
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">Tipo de Taxa *</label>
                        <select name="tipo_taxa_entrega" id="tipoTaxa" class="campo-input" onchange="atualizarTipoTaxa()">
                            <option value="fixo"   {{ old('tipo_taxa_entrega', $loja?->tipo_taxa_entrega) === 'fixo'   ? 'selected' : '' }}>Taxa Fixa</option>
                            <option value="bairro" {{ old('tipo_taxa_entrega', $loja?->tipo_taxa_entrega) === 'bairro' ? 'selected' : '' }}>Por Bairro</option>
                            <option value="por_km" {{ old('tipo_taxa_entrega', $loja?->tipo_taxa_entrega) === 'por_km' ? 'selected' : '' }}>Por KM</option>
                            <option value="gratis" {{ old('tipo_taxa_entrega', $loja?->tipo_taxa_entrega) === 'gratis' ? 'selected' : '' }}>Grátis</option>
                        </select>
                    </div>
                    <div class="campo-grupo" id="campoTaxaFixa">
                        <label class="campo-label">Valor da Taxa (R$)</label>
                        <input type="text" name="taxa_entrega_fixa" class="campo-input mascara-moeda" value="{{ old('taxa_entrega_fixa', $loja?->taxa_entrega_fixa ? number_format($loja->taxa_entrega_fixa, 2, ',', '.') : '') }}" placeholder="0,00">
                    </div>
                    <div class="campo-grupo" id="campoRaioKm" style="display:none">
                        <label class="campo-label">Raio máx. (km)</label>
                        <input type="number" name="raio_entrega_km" class="campo-input" value="{{ old('raio_entrega_km', $loja?->raio_entrega_km) }}" step="0.1">
                    </div>
                </div>
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">Pedido mínimo (R$)</label>
                        <input type="text" name="pedido_minimo" class="campo-input mascara-moeda" value="{{ old('pedido_minimo', $loja?->pedido_minimo ? number_format($loja->pedido_minimo, 2, ',', '.') : '') }}" placeholder="0,00">
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Tempo entrega min./máx. (min)</label>
                        <div class="d-flex gap-2">
                            <input type="number" name="tempo_entrega_min" class="campo-input" value="{{ old('tempo_entrega_min', $loja?->tempo_entrega_min ?? 30) }}" placeholder="30" min="0">
                            <input type="number" name="tempo_entrega_max" class="campo-input" value="{{ old('tempo_entrega_max', $loja?->tempo_entrega_max ?? 60) }}" placeholder="60" min="0">
                        </div>
                    </div>
                </div>
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="switch-label">
                            <input type="checkbox" class="switch-input" name="aceita_entrega" {{ old('aceita_entrega', $loja?->aceita_entrega ?? true) ? 'checked' : '' }}>
                            <span class="switch-slider"></span> Aceita delivery
                        </label>
                    </div>
                    <div class="campo-grupo">
                        <label class="switch-label">
                            <input type="checkbox" class="switch-input" name="aceita_retirada" {{ old('aceita_retirada', $loja?->aceita_retirada ?? true) ? 'checked' : '' }}>
                            <span class="switch-slider"></span> Aceita retirada
                        </label>
                    </div>
                    <div class="campo-grupo">
                        <label class="switch-label">
                            <input type="checkbox" class="switch-input" name="aceita_pagamento_entrega" {{ old('aceita_pagamento_entrega', $loja?->aceita_pagamento_entrega) ? 'checked' : '' }}>
                            <span class="switch-slider"></span> Pag. na entrega
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Coluna lateral --}}
    <div>
        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-image"></i> Imagens</h3></div>
            <div class="card-admin-body">
                <p class="campo-label mb-2">Logo</p>
                @if($loja?->logo)
                <div class="upload-preview mb-2"><div class="upload-preview-item"><img src="{{ $loja->logo_url }}"><input type="hidden" name="logo_path" value="{{ $loja->logo }}"></div></div>
                @else
                <div class="upload-preview mb-2" id="previewLogo"></div>
                @endif
                <div class="upload-area" data-url="{{ route('admin.lojas.upload') }}" data-tipo="logo" id="uploadLogo">
                    <input type="file" accept="image/*">
                    <div class="upload-area-icon"><i class="bi bi-shop"></i></div>
                    <p>Logo da loja</p>
                </div>
                <div class="upload-progresso mt-1"><div class="upload-progresso-barra"></div></div>

                <p class="campo-label mb-2 mt-3">Banner</p>
                @if($loja?->banner)
                <div class="upload-preview mb-2" id="previewBanner"><div class="upload-preview-item"><img src="{{ $loja->banner_url }}" style="width:100%;height:80px;object-fit:cover;border-radius:8px"><input type="hidden" name="banner_path" value="{{ $loja->banner }}"></div></div>
                @else
                <div class="upload-preview mb-2" id="previewBanner"></div>
                @endif
                <div class="upload-area" data-url="{{ route('admin.lojas.upload') }}" data-tipo="banner" id="uploadBanner">
                    <input type="file" accept="image/*">
                    <div class="upload-area-icon"><i class="bi bi-image"></i></div>
                    <p>Banner da loja (1200×400)</p>
                </div>
                <div class="upload-progresso mt-1"><div class="upload-progresso-barra"></div></div>
            </div>
        </div>

        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-palette"></i> Visual</h3></div>
            <div class="card-admin-body">
                <div class="campo-grupo">
                    <label class="campo-label">Cor Primária</label>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="color" name="cor_primaria" class="campo-input" style="height:40px;padding:4px;width:60px" value="{{ old('cor_primaria', $loja?->cor_primaria ?? '#FF6B35') }}">
                        <input type="text" id="corPrimText" class="campo-input" style="width:100px" value="{{ old('cor_primaria', $loja?->cor_primaria ?? '#FF6B35') }}" placeholder="#FF6B35">
                    </div>
                </div>
                <div class="campo-grupo">
                    <label class="campo-label">Cor Secundária</label>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="color" name="cor_secundaria" class="campo-input" style="height:40px;padding:4px;width:60px" value="{{ old('cor_secundaria', $loja?->cor_secundaria ?? '#2C3E50') }}">
                        <input type="text" class="campo-input" style="width:100px" value="{{ old('cor_secundaria', $loja?->cor_secundaria ?? '#2C3E50') }}" placeholder="#2C3E50">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Pop-up de Saída ──────────────────────────────────────────── --}}
        <div class="card-admin mb-3" id="popup-saida">
            <div class="card-admin-header">
                <h3><i class="bi bi-door-open"></i> Pop-up de Saída (Exit Intent)</h3>
            </div>
            <div class="card-admin-body">
                <div class="campo-grupo mb-3">
                    <label class="switch-label">
                        <input type="checkbox" class="switch-input" name="popup_saida_ativo" {{ old('popup_saida_ativo', $loja?->popup_saida_ativo) ? 'checked' : '' }}>
                        <span class="switch-slider"></span> Ativar pop-up de saída
                    </label>
                    <span class="campo-hint">Aparece quando o cliente tenta sair da página. Cada lojista ativa/desativa independentemente.</span>
                </div>
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">Título do Pop-up</label>
                        <input type="text" name="popup_saida_titulo" class="campo-input" value="{{ old('popup_saida_titulo', $loja?->popup_saida_titulo) }}" placeholder="Espere! Temos um presente para você!">
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Código do Cupom</label>
                        <input type="text" name="popup_saida_cupom" class="campo-input" value="{{ old('popup_saida_cupom', $loja?->popup_saida_cupom) }}" placeholder="FIQUE10" style="text-transform:uppercase">
                    </div>
                </div>
                <div class="campo-grupo">
                    <label class="campo-label">Texto do Pop-up</label>
                    <textarea name="popup_saida_texto" class="campo-input" rows="2" placeholder="Não vá embora sem aproveitar este desconto especial!">{{ old('popup_saida_texto', $loja?->popup_saida_texto) }}</textarea>
                </div>
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">Tipo de Desconto</label>
                        <select name="popup_saida_desconto_tipo" class="campo-input">
                            <option value="percentual" {{ old('popup_saida_desconto_tipo', $loja?->popup_saida_desconto_tipo) === 'percentual' ? 'selected' : '' }}>Percentual (%)</option>
                            <option value="fixo" {{ old('popup_saida_desconto_tipo', $loja?->popup_saida_desconto_tipo) === 'fixo' ? 'selected' : '' }}>Valor Fixo (R$)</option>
                            <option value="frete_gratis" {{ old('popup_saida_desconto_tipo', $loja?->popup_saida_desconto_tipo) === 'frete_gratis' ? 'selected' : '' }}>Frete Grátis</option>
                        </select>
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Valor do Desconto</label>
                        <input type="number" name="popup_saida_desconto_valor" class="campo-input" value="{{ old('popup_saida_desconto_valor', $loja?->popup_saida_desconto_valor ?? 10) }}" min="0" step="0.01">
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Validade (minutos)</label>
                        <input type="number" name="popup_saida_validade_min" class="campo-input" value="{{ old('popup_saida_validade_min', $loja?->popup_saida_validade_min ?? 30) }}" min="1">
                    </div>
                </div>
                <div class="campo-grupo">
                    <label class="campo-label">Imagem do Pop-up</label>
                    <div id="uploadPopupSaida" class="upload-area upload-area-sm" data-tipo="popup_saida">
                        <i class="bi bi-cloud-upload"></i> Clique ou arraste uma imagem
                    </div>
                    <div id="previewPopupSaida">
                        @if($loja?->popup_saida_imagem)
                        <div class="upload-preview-item"><img src="{{ asset('storage/'.$loja->popup_saida_imagem) }}">
                        <input type="hidden" name="popup_saida_imagem" value="{{ $loja->popup_saida_imagem }}"></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Pop-up de Promoção / Relâmpago ──────────────────────────── --}}
        <div class="card-admin mb-3" id="popup-promo">
            <div class="card-admin-header">
                <h3><i class="bi bi-lightning-charge"></i> Pop-up de Promoção / Relâmpago</h3>
            </div>
            <div class="card-admin-body">
                <div class="campo-grupo mb-3">
                    <label class="switch-label">
                        <input type="checkbox" class="switch-input" name="popup_promo_ativo" {{ old('popup_promo_ativo', $loja?->popup_promo_ativo) ? 'checked' : '' }}>
                        <span class="switch-slider"></span> Ativar pop-up de promoção
                    </label>
                </div>
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">Título</label>
                        <input type="text" name="popup_promo_titulo" class="campo-input" value="{{ old('popup_promo_titulo', $loja?->popup_promo_titulo) }}" placeholder="⚡ Promoção Relâmpago!">
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Delay para exibir (segundos)</label>
                        <input type="number" name="popup_promo_delay_seg" class="campo-input" value="{{ old('popup_promo_delay_seg', $loja?->popup_promo_delay_seg ?? 5) }}" min="0">
                    </div>
                </div>
                <div class="campo-grupo">
                    <label class="campo-label">Texto</label>
                    <textarea name="popup_promo_texto" class="campo-input" rows="2" placeholder="Aproveite agora! Oferta por tempo limitado.">{{ old('popup_promo_texto', $loja?->popup_promo_texto) }}</textarea>
                </div>
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">Expira em (data/hora)</label>
                        <input type="datetime-local" name="popup_promo_expira_em" class="campo-input" value="{{ old('popup_promo_expira_em', $loja?->popup_promo_expira_em?->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">URL do botão (opcional)</label>
                        <input type="url" name="popup_promo_url" class="campo-input" value="{{ old('popup_promo_url', $loja?->popup_promo_url) }}" placeholder="https://...">
                    </div>
                </div>
                <div class="campo-grupo">
                    <label class="campo-label">Imagem da Promoção</label>
                    <div id="uploadPopupPromo" class="upload-area upload-area-sm" data-tipo="popup_promo">
                        <i class="bi bi-cloud-upload"></i> Clique ou arraste uma imagem
                    </div>
                    <div id="previewPopupPromo">
                        @if($loja?->popup_promo_imagem)
                        <div class="upload-preview-item"><img src="{{ asset('storage/'.$loja->popup_promo_imagem) }}">
                        <input type="hidden" name="popup_promo_imagem" value="{{ $loja->popup_promo_imagem }}"></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── LGPD ─────────────────────────────────────────────────────── --}}
        <div class="card-admin mb-3" id="lgpd">
            <div class="card-admin-header">
                <h3><i class="bi bi-shield-lock"></i> LGPD / Cookies</h3>
            </div>
            <div class="card-admin-body">
                <div class="campo-grupo">
                    <label class="campo-label">Texto do banner de cookies</label>
                    <textarea name="lgpd_texto_cookies" class="campo-input" rows="2" placeholder="Utilizamos cookies para melhorar sua experiência...">{{ old('lgpd_texto_cookies', $loja?->lgpd_texto_cookies) }}</textarea>
                </div>
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">URL da Política de Privacidade</label>
                        <input type="url" name="lgpd_url_politica" class="campo-input" value="{{ old('lgpd_url_politica', $loja?->lgpd_url_politica) }}" placeholder="https://... (deixe vazio para usar a padrão do sistema)">
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">URL dos Termos de Uso</label>
                        <input type="url" name="lgpd_url_termos" class="campo-input" value="{{ old('lgpd_url_termos', $loja?->lgpd_url_termos) }}" placeholder="https://... (deixe vazio para usar a padrão do sistema)">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Tela da Cozinha ──────────────────────────────────────────── --}}
        <div class="card-admin mb-3" id="cozinha">
            <div class="card-admin-header">
                <h3><i class="bi bi-egg-fried"></i> Tela da Cozinha</h3>
            </div>
            <div class="card-admin-body">
                <div class="campo-grupo mb-3">
                    <label class="switch-label">
                        <input type="checkbox" class="switch-input" name="cozinha_ativo" {{ old('cozinha_ativo', $loja?->cozinha_ativo) ? 'checked' : '' }}>
                        <span class="switch-slider"></span> Habilitar tela da cozinha
                    </label>
                    <span class="campo-hint">Exibe os pedidos confirmados/em preparo em tempo real com alertas sonoros.</span>
                </div>
                <div class="campo-grupo">
                    <label class="campo-label">PIN de acesso (opcional)</label>
                    <input type="text" name="cozinha_pin" class="campo-input" value="{{ old('cozinha_pin', $loja?->cozinha_pin) }}" placeholder="Ex: 1234" maxlength="10" style="max-width:160px">
                    <span class="campo-hint">Se definido, a equipe de cozinha precisará inserir o PIN para acessar.</span>
                </div>
                @if($loja?->id && $loja->cozinha_ativo)
                <a href="{{ route('admin.cozinha') }}" target="_blank" class="btn btn-outline-primary btn-sm mt-2">
                    <i class="bi bi-box-arrow-up-right"></i> Abrir Tela da Cozinha
                </a>
                @endif
            </div>
        </div>

        {{-- ── Nota Fiscal (NFe) ─────────────────────────────────────────── --}}
        <div class="card-admin mb-3" id="nfe">
            <div class="card-admin-header">
                <h3><i class="bi bi-receipt"></i> Nota Fiscal Eletrônica (NFe/NFC-e)</h3>
            </div>
            <div class="card-admin-body">
                <div class="campo-grupo mb-3">
                    <label class="switch-label">
                        <input type="checkbox" class="switch-input" name="nfe_ativo" {{ old('nfe_ativo', $loja?->nfe_ativo) ? 'checked' : '' }}>
                        <span class="switch-slider"></span> Habilitar emissão de nota fiscal
                    </label>
                    <span class="campo-hint">Integração com Focus NFe / SEFAZ. Requer contratação do serviço de terceiros.</span>
                </div>
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">Provedor</label>
                        <select name="nfe_provedor" class="campo-input">
                            <option value="focusnfe" {{ old('nfe_provedor', $loja?->nfe_provedor) === 'focusnfe' ? 'selected' : '' }}>Focus NFe</option>
                        </select>
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Ambiente</label>
                        <select name="nfe_ambiente" class="campo-input">
                            <option value="homologacao" {{ old('nfe_ambiente', $loja?->nfe_ambiente) === 'homologacao' ? 'selected' : '' }}>Homologação (Testes)</option>
                            <option value="producao"    {{ old('nfe_ambiente', $loja?->nfe_ambiente) === 'producao' ? 'selected' : '' }}>Produção</option>
                        </select>
                    </div>
                </div>
                <div class="campo-grupo">
                    <label class="campo-label">Token da API</label>
                    <input type="password" name="nfe_token" class="campo-input" value="{{ old('nfe_token', $loja?->nfe_token) }}" placeholder="Token gerado no painel do Focus NFe">
                </div>
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">CNPJ Emitente</label>
                        <input type="text" name="nfe_cnpj_emitente" class="campo-input mascara-cnpj" value="{{ old('nfe_cnpj_emitente', $loja?->nfe_cnpj_emitente) }}" placeholder="00.000.000/0001-00">
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Razão Social</label>
                        <input type="text" name="nfe_razao_social" class="campo-input" value="{{ old('nfe_razao_social', $loja?->nfe_razao_social) }}" placeholder="Nome da empresa como no CNPJ">
                    </div>
                </div>
                <div class="campo-row">
                    <div class="campo-grupo" style="max-width:120px">
                        <label class="campo-label">Série</label>
                        <input type="text" name="nfe_serie" class="campo-input" value="{{ old('nfe_serie', $loja?->nfe_serie ?? '1') }}" placeholder="1">
                    </div>
                    <div class="campo-grupo" style="max-width:160px">
                        <label class="campo-label">Nº Atual</label>
                        <input type="number" name="nfe_numero_atual" class="campo-input" value="{{ old('nfe_numero_atual', $loja?->nfe_numero_atual ?? 1) }}" min="1">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Templates WhatsApp ───────────────────────────────────────── --}}
        <div class="card-admin mb-3" id="wpp-templates">
            <div class="card-admin-header">
                <h3><i class="bi bi-whatsapp"></i> Templates de Mensagem (WhatsApp)</h3>
            </div>
            <div class="card-admin-body">
                <p class="text-muted small mb-3">Personalize as mensagens enviadas via WhatsApp. Deixe em branco para usar o texto padrão do sistema.</p>
                @foreach([
                    'pedido_novo'             => ['Novo Pedido (para a loja)', '🔔 *NOVO PEDIDO!* ...'],
                    'pedido_confirmado_cliente'=> ['Pedido Recebido (para o cliente)', '✅ *Pedido Recebido com Sucesso!* ...'],
                    'status_confirmado'       => ['Pedido Confirmado', '✅ *Pedido Confirmado!* ...'],
                    'status_em_preparo'       => ['Em Preparo', '👨‍🍳 *Na Cozinha Agora!* ...'],
                    'status_pronto'           => ['Pronto / Para Retirada', '🍽 *Pronto!* ...'],
                    'status_saiu_para_entrega'=> ['Saiu para Entrega', '🛵 *Saiu para Entrega!* ...'],
                    'status_entregue'         => ['Entregue', '🎉 *Pedido Entregue!* ...'],
                    'status_cancelado'        => ['Cancelado', '❌ *Pedido Cancelado* ...'],
                    'pagamento_pix'           => ['Pagamento PIX', '💰 *Pagamento via PIX* ...'],
                    'link_rastreamento'       => ['Link de Rastreamento', '🗺 *Acompanhe Sua Entrega!* ...'],
                    'avaliacao'               => ['Solicitação de Avaliação', '⭐ *Como foi sua experiência?* ...'],
                ] as $chave => $info)
                <div class="campo-grupo mb-2">
                    <label class="campo-label">{{ $info[0] }}</label>
                    <textarea name="wpp_templates[{{ $chave }}]" class="campo-input" rows="2" placeholder="{{ $info[1] }}">{{ old("wpp_templates.{$chave}", $loja?->wpp_templates[$chave] ?? '') }}</textarea>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Coluna lateral --}}
    <div>
        {{-- Horários Automáticos --}}
        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-clock-history"></i> Horários Automáticos</h3></div>
            <div class="card-admin-body">
                <div class="campo-grupo mb-3">
                    <label class="switch-label">
                        <input type="checkbox" class="switch-input" name="horario_automatico" id="horarioAutomaticoToggle" {{ old('horario_automatico', $loja?->horario_automatico) ? 'checked' : '' }}>
                        <span class="switch-slider"></span> Ativar abertura/fechamento automático
                    </label>
                    <span class="campo-hint">A loja abrirá e fechará automaticamente</span>
                </div>

                <div id="horariosConfig" style="display:{{ old('horario_automatico', $loja?->horario_automatico) ? 'block' : 'none' }}">
                    <div class="campo-grupo mb-2">
                        <label class="campo-label">Horário de Abertura</label>
                        <input type="time" name="horario_abertura" class="campo-input" value="{{ old('horario_abertura', $loja?->horario_abertura?->format('H:i')) }}">
                    </div>
                    <div class="campo-grupo mb-2">
                        <label class="campo-label">Horário de Fechamento</label>
                        <input type="time" name="horario_fechamento" class="campo-input" value="{{ old('horario_fechamento', $loja?->horario_fechamento?->format('H:i')) }}">
                    </div>

                    <div class="campo-grupo">
                        <label class="campo-label">Dias de Funcionamento</label>
                        <div style="display:flex;flex-direction:column;gap:6px">
                            @php
                                $diasSemana = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                                $diasSelecionados = old('dias_funcionamento', $loja?->dias_funcionamento ?? []);
                            @endphp
                            @foreach($diasSemana as $num => $nome)
                            <label style="display:flex;align-items:center;gap:8px;font-size:.9rem">
                                <input type="checkbox" name="dias_funcionamento[]" value="{{ $num }}" {{ in_array($num, $diasSelecionados) ? 'checked' : '' }}>
                                {{ $nome }}
                            </label>
                            @endforeach
                        </div>
                        <span class="campo-hint">Deixe vazio para todos os dias</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status --}}
        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-toggles"></i> Status</h3></div>
            <div class="card-admin-body">
                <div class="campo-grupo">
                    <label class="switch-label">
                        <input type="checkbox" class="switch-input" name="ativo" {{ old('ativo', $loja?->ativo ?? true) ? 'checked' : '' }}>
                        <span class="switch-slider"></span> Loja ativa
                    </label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-2">
            <i class="bi bi-check-lg"></i> Salvar Loja
        </button>
        <a href="{{ route('admin.lojas.index') }}" class="btn btn-secondary w-100">Cancelar</a>
    </div>
</div>

@push('scripts')
<script>
// Slug automático a partir do nome
document.querySelector('input[name=nome]')?.addEventListener('input', function() {
    if (!{{ $loja ? 'true' : 'false' }}) {
        const slug = this.value.toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        document.getElementById('lojaSlug').value = slug;
        document.getElementById('previewSlug').textContent = slug || '...';
    }
});
document.getElementById('lojaSlug')?.addEventListener('input', function() {
    document.getElementById('previewSlug').textContent = this.value || '...';
});

// CEP autofill
document.getElementById('lojaCep')?.addEventListener('blur', async function() {
    const cep = this.value.replace(/\D/g, '');
    if (cep.length !== 8) return;
    try {
        const r = await fetch(`/api/cep/${cep}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const d = await r.json();
        if (d.logradouro) document.querySelector('[name=logradouro]').value = d.logradouro;
        if (d.bairro)     document.querySelector('[name=bairro]').value     = d.bairro;
        if (d.localidade) document.querySelector('[name=cidade]').value     = d.localidade;
        if (d.uf)         document.querySelector('[name=estado]').value     = d.uf.toUpperCase();
    } catch {}
});

function atualizarTipoTaxa() {
    const tipo = document.getElementById('tipoTaxa').value;
    document.getElementById('campoTaxaFixa').style.display = tipo === 'fixo' ? '' : 'none';
    document.getElementById('campoRaioKm').style.display   = tipo === 'por_km' ? '' : 'none';
}
atualizarTipoTaxa();

inicializarUploadAdmin('#uploadLogo', '{{ route('admin.lojas.upload') }}', 'logo_path', resp => {
    document.getElementById('previewLogo').innerHTML =
        `<div class="upload-preview-item"><img src="${resp.url}"><input type="hidden" name="logo_path" value="${resp.caminho}"></div>`;
});

inicializarUploadAdmin('#uploadBanner', '{{ route('admin.lojas.upload') }}', 'banner_path', resp => {
    document.getElementById('previewBanner').innerHTML =
        `<div class="upload-preview-item"><img src="${resp.url}" style="width:100%;height:80px;object-fit:cover;border-radius:8px"><input type="hidden" name="banner_path" value="${resp.caminho}"></div>`;
});

inicializarUploadAdmin('#uploadPopupSaida', '{{ route('admin.lojas.upload') }}', 'popup_saida_imagem', resp => {
    document.getElementById('previewPopupSaida').innerHTML =
        `<div class="upload-preview-item"><img src="${resp.url}" style="width:100%;height:80px;object-fit:cover;border-radius:8px"><input type="hidden" name="popup_saida_imagem" value="${resp.caminho}"></div>`;
});

inicializarUploadAdmin('#uploadPopupPromo', '{{ route('admin.lojas.upload') }}', 'popup_promo_imagem', resp => {
    document.getElementById('previewPopupPromo').innerHTML =
        `<div class="upload-preview-item"><img src="${resp.url}" style="width:100%;height:80px;object-fit:cover;border-radius:8px"><input type="hidden" name="popup_promo_imagem" value="${resp.caminho}"></div>`;
});

// Toggle horários automáticos
document.getElementById('horarioAutomaticoToggle')?.addEventListener('change', function() {
    document.getElementById('horariosConfig').style.display = this.checked ? 'block' : 'none';
});
</script>
@endpush
