@extends('layouts.pwa')
@section('titulo', 'Finalizar Pedido')

@push('styles')
<link rel="stylesheet" href="https://sdk.mercadopago.com/js/v2">
@endpush

@section('conteudo')
<div class="checkout-container">
    <div class="page-header">
        <button onclick="history.back()" class="btn-voltar"><i class="bi bi-arrow-left"></i></button>
        <h1 class="page-title">Finalizar Pedido</h1>
    </div>

    <form id="formCheckout">
        @csrf

        {{-- Itens do carrinho --}}
        <div class="checkout-section" id="secaoItens">
            <h3 class="checkout-section-title"><i class="bi bi-bag"></i> Itens do Pedido</h3>
            <div id="listaItensCheckout"></div>
            <div class="checkout-subtotal" id="checkoutSubtotal"></div>
        </div>

        {{-- Tipo de Entrega --}}
        <div class="checkout-section">
            <h3 class="checkout-section-title"><i class="bi bi-bicycle"></i> Tipo de Entrega</h3>
            <div class="entrega-opcoes">
                @if($loja->aceita_entrega)
                <label class="entrega-opcao {{ $loja->aceita_retirada ? '' : 'selected' }}" data-tipo="entrega">
                    <input type="radio" name="tipo_entrega" value="entrega" {{ $loja->aceita_retirada ? '' : 'checked' }}>
                    <i class="bi bi-bicycle"></i>
                    <div>
                        <strong>Entrega</strong>
                        <small>Receba em casa</small>
                    </div>
                </label>
                @endif
                @if($loja->aceita_retirada)
                <label class="entrega-opcao selected" data-tipo="retirada">
                    <input type="radio" name="tipo_entrega" value="retirada" checked>
                    <i class="bi bi-shop"></i>
                    <div>
                        <strong>Retirada</strong>
                        <small>No balcão</small>
                    </div>
                </label>
                @endif
            </div>
        </div>

        {{-- Endereço de Entrega --}}
        <div class="checkout-section" id="secaoEndereco" style="display:none">
            <h3 class="checkout-section-title"><i class="bi bi-geo-alt"></i> Endereço de Entrega</h3>

            @if($enderecos->count())
            <div class="enderecos-salvos mb-3">
                <p class="text-muted small">Endereços salvos:</p>
                @foreach($enderecos as $end)
                <label class="endereco-opcao">
                    <input type="radio" name="endereco_salvo" value="{{ $end->id }}" data-endereco='@json($end)'>
                    <div>
                        <strong>{{ $end->apelido }}</strong>
                        <small>{{ $end->endereco_resumido }}</small>
                    </div>
                </label>
                @endforeach
                <label class="endereco-opcao">
                    <input type="radio" name="endereco_salvo" value="novo">
                    <div><strong>+ Novo endereço</strong></div>
                </label>
            </div>
            @endif

            <div id="formEndereco">
                <div class="campo-grupo">
                    <label>CEP *</label>
                    <div class="input-cep-grupo">
                        <input type="text" name="endereco[cep]" id="inputCep" class="campo-input mascara-cep" placeholder="00000-000" maxlength="9">
                        <button type="button" class="btn-buscar-cep" id="btnBuscarCep"><i class="bi bi-search"></i></button>
                    </div>
                    <div class="cep-loading" id="cepLoading" style="display:none"><div class="spinner-sm"></div> Buscando...</div>
                </div>
                <div class="campos-endereco" id="camposEndereco" style="display:none">
                    <div class="campo-grupo">
                        <label>Logradouro *</label>
                        <input type="text" name="endereco[logradouro]" id="inputLogradouro" class="campo-input" placeholder="Rua, Av., etc.">
                    </div>
                    <div class="campo-row">
                        <div class="campo-grupo">
                            <label>Número *</label>
                            <input type="text" name="endereco[numero]" id="inputNumero" class="campo-input" placeholder="123">
                        </div>
                        <div class="campo-grupo">
                            <label>Complemento</label>
                            <input type="text" name="endereco[complemento]" id="inputComplemento" class="campo-input" placeholder="Apto, casa...">
                        </div>
                    </div>
                    <div class="campo-grupo">
                        <label>Bairro *</label>
                        <input type="text" name="endereco[bairro]" id="inputBairro" class="campo-input" placeholder="Seu bairro">
                    </div>
                    <div class="campo-row">
                        <div class="campo-grupo">
                            <label>Cidade *</label>
                            <input type="text" name="endereco[cidade]" id="inputCidade" class="campo-input" placeholder="Cidade">
                        </div>
                        <div class="campo-grupo campo-grupo-sm">
                            <label>UF *</label>
                            <input type="text" name="endereco[estado]" id="inputEstado" class="campo-input" placeholder="SP" maxlength="2">
                        </div>
                    </div>
                    <input type="hidden" name="endereco[latitude]" id="inputLat">
                    <input type="hidden" name="endereco[longitude]" id="inputLng">
                </div>
            </div>

            <div class="taxa-entrega-info" id="taxaEntregaInfo" style="display:none">
                <div class="taxa-row">
                    <span>Taxa de Entrega</span>
                    <strong id="taxaEntregaValor">Calculando...</strong>
                </div>
                <div class="taxa-row" id="tempoEstimadoRow" style="display:none">
                    <span>Tempo estimado</span>
                    <strong id="tempoEstimado"></strong>
                </div>
            </div>
        </div>

        {{-- Cupom --}}
        <div class="checkout-section">
            <h3 class="checkout-section-title"><i class="bi bi-ticket-perforated"></i> Cupom de Desconto</h3>
            <div class="cupom-grupo">
                <input type="text" id="inputCupom" class="campo-input" placeholder="Digite seu cupom" style="text-transform:uppercase">
                <button type="button" class="btn-aplicar-cupom" id="btnAplicarCupom">Aplicar</button>
            </div>
            <div id="cupomStatus"></div>
        </div>

        {{-- Observações --}}
        <div class="checkout-section">
            <h3 class="checkout-section-title"><i class="bi bi-chat-text"></i> Observações</h3>
            <textarea name="observacoes" class="campo-input" rows="3" placeholder="Alguma observação? Ex: sem cebola, capricha no molho..."></textarea>
        </div>

        {{-- Gorjeta para o entregador --}}
        <div class="checkout-section">
            <h3 class="checkout-section-title"><i class="bi bi-emoji-smile"></i> Agrade o entregador</h3>
            <p class="text-muted" style="margin: 0 0 10px; font-size: .9rem;">100% do valor vai direto para quem leva seu pedido. Escolha um valor sugerido ou personalize.</p>
            <div class="gorjeta-opcoes">
                @foreach([2,5,8,10] as $valor)
                    <button type="button" class="gorjeta-opcao" data-valor="{{ $valor }}">R$ {{ number_format($valor, 2, ',', '.') }}</button>
                @endforeach
                <button type="button" class="gorjeta-opcao" data-valor="0">Sem gorjeta</button>
            </div>
            <div class="gorjeta-input">
                <span>R$</span>
                <input type="number" step="0.50" min="0" max="500" id="inputGorjeta" placeholder="0,00">
            </div>
        </div>

        {{-- Pagamento --}}
        <div class="checkout-section">
            <h3 class="checkout-section-title"><i class="bi bi-credit-card"></i> Forma de Pagamento</h3>
            <div class="pagamento-opcoes">
                <label class="pagamento-opcao selected" data-metodo="pix">
                    <input type="radio" name="metodo_pagamento" value="pix" checked>
                    <img src="{{ asset('img/pix.svg') }}" alt="PIX" class="pagamento-icone">
                    <span>PIX</span>
                </label>
                <label class="pagamento-opcao" data-metodo="cartao_credito">
                    <input type="radio" name="metodo_pagamento" value="cartao_credito">
                    <i class="bi bi-credit-card-2-front"></i>
                    <span>Crédito</span>
                </label>
                <label class="pagamento-opcao" data-metodo="cartao_debito">
                    <input type="radio" name="metodo_pagamento" value="cartao_debito">
                    <i class="bi bi-credit-card"></i>
                    <span>Débito</span>
                </label>
                @if($loja->aceita_pagamento_entrega && $temEntregador)
                <label class="pagamento-opcao" data-metodo="pagamento_entrega">
                    <input type="radio" name="metodo_pagamento" value="pagamento_entrega">
                    <i class="bi bi-cash-coin"></i>
                    <span>Na entrega</span>
                </label>
                @endif
            </div>

            {{-- Formulário Cartão MercadoPago --}}
            <div id="formCartao" style="display:none">
                <div id="mp-card-form">
                    <div class="campo-grupo">
                        <label>Número do Cartão</label>
                        <div id="mp-cardNumber" class="campo-mp"></div>
                    </div>
                    <div class="campo-row">
                        <div class="campo-grupo">
                            <label>Validade</label>
                            <div id="mp-expirationDate" class="campo-mp"></div>
                        </div>
                        <div class="campo-grupo">
                            <label>CVV</label>
                            <div id="mp-securityCode" class="campo-mp"></div>
                        </div>
                    </div>
                    <div class="campo-grupo">
                        <label>Nome no Cartão</label>
                        <div id="mp-cardholderName" class="campo-mp"></div>
                    </div>
                    <div class="campo-grupo">
                        <label>CPF do Titular</label>
                        <input type="text" id="mp-cpf" class="campo-input mascara-cpf" placeholder="000.000.000-00">
                    </div>
                    <div class="campo-grupo" id="parcelasGrupo" style="display:none">
                        <label>Parcelas</label>
                        <select id="mp-parcelas" class="campo-input"></select>
                    </div>
                    <div id="mp-bandeira" class="mp-bandeira-info"></div>
                </div>
            </div>
        </div>

        {{-- Resumo do Pedido --}}
        <div class="checkout-resumo">
            <div class="resumo-linha"><span>Subtotal</span><span id="resumoSubtotal">R$ 0,00</span></div>
            <div class="resumo-linha" id="resumoFreteRow" style="display:none"><span>Entrega</span><span id="resumoFrete">R$ 0,00</span></div>
            <div class="resumo-linha" id="resumoGorjetaRow" style="display:none"><span>Gorjeta</span><span id="resumoGorjeta">R$ 0,00</span></div>
            <div class="resumo-linha text-success" id="resumoDescontoRow" style="display:none"><span>Desconto</span><span id="resumoDesconto">-R$ 0,00</span></div>
            <div class="resumo-linha resumo-total"><span>Total</span><span id="resumoTotal">R$ 0,00</span></div>
        </div>

        <button type="submit" class="btn-finalizar" id="btnFinalizar" disabled>
            <span id="btnFinalizarTexto">Finalize o pedido</span>
            <div class="spinner-btn" id="spinnerBtn" style="display:none"></div>
        </button>
    </form>
</div>

{{-- Modal PIX --}}
<div class="modal-overlay" id="modalPixOverlay"></div>
<div class="modal-pix" id="modalPix">
    <h4><i class="bi bi-qr-code"></i> Pague com PIX</h4>
    <div class="pix-qr-container">
        <img id="pixQrImg" src="" alt="QR Code PIX">
    </div>
    <p class="pix-instrucao">Escaneie o QR Code ou copie o código abaixo:</p>
    <div class="pix-codigo-container">
        <textarea id="pixCodigo" class="pix-codigo" readonly></textarea>
        <button onclick="copiarPix()" class="btn-copiar-pix"><i class="bi bi-clipboard"></i> Copiar</button>
    </div>
    <div class="pix-timer" id="pixTimer"></div>
    <div class="pix-aguardando">
        <div class="spinner"></div>
        <p>Aguardando confirmação do pagamento...</p>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
const MP_PUBLIC_KEY = '{{ $loja->mercadopago_public_key ?? env("MERCADOPAGO_PUBLIC_KEY") }}';
const LOJA_ID_CHECKOUT = {{ $loja->id }};
let mpInstance = null;
let cardTokenForm = null;
let taxaEntregaAtual = 0;
let descontoAtual = 0;
let gorjetaAtual = 0;

document.addEventListener('DOMContentLoaded', () => {
    renderizarItensCheckout();
    inicializarEventos();
    verificarCarrinho();
});

function inicializarEventos() {
    // Tipo de entrega
    document.querySelectorAll('input[name="tipo_entrega"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.entrega-opcao').forEach(o => o.classList.remove('selected'));
            this.closest('.entrega-opcao').classList.add('selected');
            const isEntrega = this.value === 'entrega';
            document.getElementById('secaoEndereco').style.display = isEntrega ? '' : 'none';
            taxaEntregaAtual = isEntrega ? taxaEntregaAtual : 0;
            atualizarResumo();
        });
    });

    // Métodos de pagamento
    document.querySelectorAll('input[name="metodo_pagamento"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.pagamento-opcao').forEach(o => o.classList.remove('selected'));
            this.closest('.pagamento-opcao').classList.add('selected');
            const isCartao = ['cartao_credito', 'cartao_debito'].includes(this.value);
            document.getElementById('formCartao').style.display = isCartao ? '' : 'none';
            if (isCartao && !mpInstance) inicializarMercadoPago();
        });
    });

    // Endereços salvos
    document.querySelectorAll('input[name="endereco_salvo"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'novo') {
                document.getElementById('formEndereco').style.display = '';
                resetarCamposEndereco();
            } else {
                document.getElementById('formEndereco').style.display = 'none';
                const end = JSON.parse(this.dataset.endereco || '{}');
                preencherEndereco(end);
                calcularFrete(end.bairro, end.cidade, end.latitude, end.longitude);
            }
        });
    });

    // CEP
    document.getElementById('inputCep')?.addEventListener('blur', function() {
        const cep = this.value.replace(/\D/g, '');
        if (cep.length === 8) buscarCep(cep);
    });
    document.getElementById('btnBuscarCep')?.addEventListener('click', () => {
        const cep = document.getElementById('inputCep').value.replace(/\D/g, '');
        if (cep.length === 8) buscarCep(cep);
    });

    // Cupom
    document.getElementById('btnAplicarCupom').addEventListener('click', aplicarCupom);
    document.getElementById('inputCupom').addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); aplicarCupom(); }
    });

    document.querySelectorAll('.gorjeta-opcao').forEach(btn => btn.addEventListener('click', () => {
        const valor = parseFloat(btn.dataset.valor || '0');
        setarGorjeta(valor);
        document.getElementById('inputGorjeta').value = valor > 0 ? valor.toFixed(2) : '';
    }));
    document.getElementById('inputGorjeta').addEventListener('input', e => {
        const valor = parseFloat((e.target.value || '0').replace(',', '.')) || 0;
        setarGorjeta(valor);
    });

    // Submit
    document.getElementById('formCheckout').addEventListener('submit', finalizarPedido);

    // Verificar carrinho inicial
    const tipoEntrega = document.querySelector('input[name="tipo_entrega"]:checked')?.value;
    if (tipoEntrega === 'entrega') {
        document.getElementById('secaoEndereco').style.display = '';
    }
}

function renderizarItensCheckout() {
    const carrinho = Carrinho.obter();
    const lista    = document.getElementById('listaItensCheckout');
    if (!carrinho.length) {
        lista.innerHTML = '<p class="text-muted">Carrinho vazio.</p>';
        return;
    }
    lista.innerHTML = carrinho.map(item => `
        <div class="checkout-item">
            <div class="checkout-item-info">
                <span class="checkout-item-qtd">${item.quantidade}x</span>
                <span class="checkout-item-nome">${item.nome}</span>
            </div>
            <span class="checkout-item-preco">R$ ${(item.preco_total * item.quantidade).toFixed(2).replace('.', ',')}</span>
        </div>
    `).join('');
    atualizarResumo();
}

function atualizarResumo() {
    const carrinho  = Carrinho.obter();
    const subtotal  = carrinho.reduce((s, i) => s + (i.preco_total * i.quantidade), 0);
    const frete     = taxaEntregaAtual;
    const desconto  = descontoAtual;
    const total     = subtotal + frete + gorjetaAtual - desconto;

    document.getElementById('resumoSubtotal').textContent = 'R$ ' + subtotal.toFixed(2).replace('.', ',');
    document.getElementById('resumoFrete').textContent    = frete > 0 ? 'R$ ' + frete.toFixed(2).replace('.', ',') : 'Grátis';
    document.getElementById('resumoGorjeta').textContent  = gorjetaAtual > 0 ? 'R$ ' + gorjetaAtual.toFixed(2).replace('.', ',') : 'R$ 0,00';
    document.getElementById('resumoDesconto').textContent = '-R$ ' + desconto.toFixed(2).replace('.', ',');
    document.getElementById('resumoTotal').textContent    = 'R$ ' + total.toFixed(2).replace('.', ',');
    document.getElementById('resumoFreteRow').style.display   = frete > 0 ? '' : 'none';
    document.getElementById('resumoGorjetaRow').style.display = gorjetaAtual > 0 ? '' : 'none';
    document.getElementById('resumoDescontoRow').style.display = desconto > 0 ? '' : 'none';
    document.getElementById('checkoutSubtotal').textContent   = carrinho.length + ' item(ns)';

    const btnFinalizar = document.getElementById('btnFinalizar');
    btnFinalizar.disabled = carrinho.length === 0;
    btnFinalizar.querySelector('#btnFinalizarTexto').textContent =
        'Finalizar — R$ ' + total.toFixed(2).replace('.', ',');
}

function buscarCep(cep) {
    document.getElementById('cepLoading').style.display = '';
    fetch(`/api/cep/${cep}`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('cepLoading').style.display = 'none';
            if (data.sucesso) {
                preencherEndereco(data);
                document.getElementById('camposEndereco').style.display = '';
                calcularFrete(data.bairro, data.cidade);
            } else {
                mostrarToast('CEP não encontrado', 'erro');
            }
        })
        .catch(() => {
            document.getElementById('cepLoading').style.display = 'none';
            mostrarToast('Erro ao buscar CEP', 'erro');
        });
}

function preencherEndereco(data) {
    document.getElementById('inputLogradouro').value = data.logradouro || '';
    document.getElementById('inputBairro').value     = data.bairro || '';
    document.getElementById('inputCidade').value     = data.cidade || '';
    document.getElementById('inputEstado').value     = data.estado || '';
    if (data.latitude)  document.getElementById('inputLat').value = data.latitude;
    if (data.longitude) document.getElementById('inputLng').value = data.longitude;
    document.getElementById('camposEndereco').style.display = '';
    document.getElementById('inputNumero').focus();
}

function calcularFrete(bairro, cidade, lat, lng) {
    const cep = document.getElementById('inputCep').value.replace(/\D/g, '');
    fetch('/checkout/calcular-frete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        body: JSON.stringify({ cep, bairro, cidade, latitude: lat, longitude: lng })
    })
    .then(r => r.json())
    .then(data => {
        const info = document.getElementById('taxaEntregaInfo');
        info.style.display = '';
        if (!data.disponivel) {
            document.getElementById('taxaEntregaValor').textContent = '⚠ ' + (data.erro || 'Fora da área');
            taxaEntregaAtual = 0;
        } else {
            taxaEntregaAtual = parseFloat(data.taxa) || 0;
            document.getElementById('taxaEntregaValor').textContent =
                taxaEntregaAtual === 0 ? '🎉 Grátis' : 'R$ ' + taxaEntregaAtual.toFixed(2).replace('.', ',');
            if (data.tempo_min) {
                document.getElementById('tempoEstimadoRow').style.display = '';
                document.getElementById('tempoEstimado').textContent = `${data.tempo_min}–${data.tempo_max} min`;
            }
        }
        atualizarResumo();
    });
}

function aplicarCupom() {
    const codigo = document.getElementById('inputCupom').value.trim().toUpperCase();
    if (!codigo) return;
    const status = document.getElementById('cupomStatus');
    status.innerHTML = '<div class="spinner-sm"></div>';

    fetch(`/api/cupom/${LOJA_SLUG}/${codigo}`)
        .then(r => r.json())
        .then(data => {
            if (data.valido) {
                const carrinho = Carrinho.obter();
                const subtotal = carrinho.reduce((s, i) => s + (i.preco_total * i.quantidade), 0);
                descontoAtual = data.tipo === 'percentual'
                    ? subtotal * (data.valor / 100)
                    : (data.tipo === 'fixo' ? data.valor : 0);
                if (data.frete_gratis) taxaEntregaAtual = 0;
                status.innerHTML = `<div class="cupom-valido"><i class="bi bi-check-circle"></i> ${data.descricao || 'Cupom aplicado!'}</div>`;
                atualizarResumo();
            } else {
                descontoAtual = 0;
                status.innerHTML = `<div class="cupom-invalido"><i class="bi bi-x-circle"></i> ${data.erro}</div>`;
                atualizarResumo();
            }
        });
}

function inicializarMercadoPago() {
    if (!MP_PUBLIC_KEY) { mostrarToast('Chave MercadoPago não configurada', 'erro'); return; }
    mpInstance = new MercadoPago(MP_PUBLIC_KEY, { locale: 'pt-BR' });
    const fields = mpInstance.fields.create('cardNumber', { placeholder: '0000 0000 0000 0000' }).mount('mp-cardNumber');
    mpInstance.fields.create('expirationDate', { placeholder: 'MM/AA' }).mount('mp-expirationDate');
    mpInstance.fields.create('securityCode', { placeholder: 'CVV' }).mount('mp-securityCode');
    mpInstance.fields.create('cardholderName', { placeholder: 'Nome igual no cartão' }).mount('mp-cardholderName');

    fields.on('binChange', async ({ bin }) => {
        if (bin && bin.length >= 6) {
            const info = await mpInstance.getPaymentMethods({ bin });
            const bandeira = info.results?.[0]?.thumbnail;
            if (bandeira) document.getElementById('mp-bandeira').innerHTML = `<img src="${bandeira}" class="mp-bandeira-img">`;
            const metodo = document.querySelector('input[name="metodo_pagamento"]:checked')?.value;
            if (metodo === 'cartao_credito') {
                const parcelas = await mpInstance.getInstallments({ bin, amount: obterTotalAtual() });
                const sel = document.getElementById('mp-parcelas');
                sel.innerHTML = parcelas[0]?.payer_costs?.map(p =>
                    `<option value="${p.installments}" data-rate="${p.installment_rate}">${p.recommended_message}</option>`
                ).join('') || '';
                document.getElementById('parcelasGrupo').style.display = '';
            }
        }
    });
}

function obterTotalAtual() {
    const carrinho = Carrinho.obter();
    const subtotal = carrinho.reduce((s, i) => s + (i.preco_total * i.quantidade), 0);
    return subtotal + taxaEntregaAtual + gorjetaAtual - descontoAtual;
}

async function finalizarPedido(e) {
    e.preventDefault();
    const btn = document.getElementById('btnFinalizar');
    const spinner = document.getElementById('spinnerBtn');
    const txtBtn  = document.getElementById('btnFinalizarTexto');
    btn.disabled = true; spinner.style.display = ''; txtBtn.style.display = 'none';

    const metodo   = document.querySelector('input[name="metodo_pagamento"]:checked')?.value;
    const tipo     = document.querySelector('input[name="tipo_entrega"]:checked')?.value;
    const carrinho = Carrinho.obter();
    const dados    = {
        carrinho: carrinho.map(i => ({
            produto_id: i.produto_id, quantidade: i.quantidade,
            adicionais: i.adicionais || [], observacoes: i.observacoes || ''
        })),
        tipo_entrega: tipo,
        metodo_pagamento: metodo,
        observacoes: document.querySelector('textarea[name=observacoes]')?.value || '',
        gorjeta: gorjetaAtual,
        cupom_codigo: document.getElementById('inputCupom').value.trim().toUpperCase() || null,
    };

    if (tipo === 'entrega') {
        dados.endereco = {
            cep:         document.getElementById('inputCep')?.value || '',
            logradouro:  document.getElementById('inputLogradouro')?.value || '',
            numero:      document.getElementById('inputNumero')?.value || '',
            complemento: document.getElementById('inputComplemento')?.value || '',
            bairro:      document.getElementById('inputBairro')?.value || '',
            cidade:      document.getElementById('inputCidade')?.value || '',
            estado:      document.getElementById('inputEstado')?.value || '',
            latitude:    document.getElementById('inputLat')?.value || null,
            longitude:   document.getElementById('inputLng')?.value || null,
        };
    }

    if (['cartao_credito', 'cartao_debito'].includes(metodo)) {
        try {
            const token = await mpInstance.fields.createCardToken({
                cardholderName: document.querySelector('#mp-cardholderName input')?.value || '',
                identificationType: 'CPF',
                identificationNumber: document.getElementById('mp-cpf').value.replace(/\D/g,''),
            });
            dados.token             = token.id;
            dados.parcelas          = parseInt(document.getElementById('mp-parcelas')?.value || '1');
            dados.bandeira          = token.payment_method_id;
            dados.ultimos_digitos   = token.last_four_digits;
            dados.titular           = token.cardholder?.name;
            dados.cpf               = document.getElementById('mp-cpf').value;
        } catch (err) {
            mostrarToast('Erro ao processar cartão: ' + err.message, 'erro');
            btn.disabled = false; spinner.style.display = 'none'; txtBtn.style.display = '';
            return;
        }
    }

    fetch('/checkout/criar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        body: JSON.stringify(dados)
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false; spinner.style.display = 'none'; txtBtn.style.display = '';
        if (!data.sucesso) { mostrarToast(data.erro || 'Erro ao criar pedido', 'erro'); return; }
        Carrinho.limpar();
        if (data.tipo === 'pix') {
            mostrarModalPix(data);
            iniciarPollingPagamento(data.pedido_id);
        } else {
            window.location.href = `/checkout/sucesso/${data.pedido_id}`;
        }
    })
    .catch(() => {
        btn.disabled = false; spinner.style.display = 'none'; txtBtn.style.display = '';
        mostrarToast('Erro de conexão. Tente novamente.', 'erro');
    });
}

function mostrarModalPix(data) {
    document.getElementById('pixQrImg').src = `data:image/png;base64,${data.qr_code_base64}`;
    document.getElementById('pixCodigo').value = data.qr_code;
    document.getElementById('modalPixOverlay').classList.add('ativo');
    document.getElementById('modalPix').classList.add('ativo');
    iniciarCountdownPix(data.expiracao);
}

function copiarPix() {
    const codigo = document.getElementById('pixCodigo');
    codigo.select();
    navigator.clipboard.writeText(codigo.value).then(() => mostrarToast('Código PIX copiado!', 'sucesso'));
}

function iniciarCountdownPix(expiracao) {
    const exp = new Date(expiracao);
    const timer = document.getElementById('pixTimer');
    const interval = setInterval(() => {
        const diff = Math.floor((exp - new Date()) / 1000);
        if (diff <= 0) { clearInterval(interval); timer.textContent = 'Expirado'; return; }
        const min = Math.floor(diff / 60);
        const sec = diff % 60;
        timer.textContent = `Expira em ${min}:${String(sec).padStart(2, '0')}`;
    }, 1000);
}

function iniciarPollingPagamento(pedidoId) {
    let tentativas = 0;
    const interval = setInterval(() => {
        fetch(`/checkout/verificar/${pedidoId}`)
            .then(r => r.json())
            .then(data => {
                if (data.aprovado || data.status === 'approved') {
                    clearInterval(interval);
                    window.location.href = `/checkout/sucesso/${pedidoId}`;
                }
            });
        if (++tentativas >= 120) clearInterval(interval);
    }, 5000);
}

function verificarCarrinho() {
    const carrinho = Carrinho.obter();
    if (!carrinho.length) {
        document.getElementById('btnFinalizar').disabled = true;
        mostrarToast('Seu carrinho está vazio!', 'aviso');
    }
}

function resetarCamposEndereco() {
    ['inputCep','inputLogradouro','inputNumero','inputComplemento','inputBairro','inputCidade','inputEstado'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    document.getElementById('camposEndereco').style.display = 'none';
    document.getElementById('taxaEntregaInfo').style.display = 'none';
    taxaEntregaAtual = 0;
    atualizarResumo();
}
</script>
@endpush
