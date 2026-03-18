@extends('layouts.pwa')
@section('titulo', $produto->nome . ' — ' . $loja->nome)

@section('conteudo')
<div class="page-header">
    <button onclick="history.back()" class="btn-voltar"><i class="bi bi-arrow-left"></i></button>
    <h1 class="page-title">{{ $loja->nome }}</h1>
</div>

{{-- Imagem do produto --}}
<div class="produto-detalhe-img-wrap">
    @if($produto->imagem_principal)
    <img src="{{ $produto->imagem_url }}" alt="{{ $produto->nome }}" class="produto-detalhe-img">
    @else
    <div class="produto-detalhe-img-placeholder"><i class="bi bi-image"></i></div>
    @endif
    @if(!$produto->ativo)
    <div class="produto-indisponivel-overlay">Indisponível no momento</div>
    @endif
</div>

<div class="produto-detalhe-corpo">
    {{-- Info básica --}}
    <div class="produto-detalhe-header">
        <h1 class="produto-detalhe-nome">{{ $produto->nome }}</h1>
        @if($produto->descricao)
        <p class="produto-detalhe-desc">{{ $produto->descricao }}</p>
        @endif
        <div class="produto-detalhe-preco-wrap">
            @if($produto->preco_promocional)
            <span class="produto-preco-de">R$ {{ number_format($produto->preco, 2, ',', '.') }}</span>
            <span class="produto-detalhe-preco">R$ {{ number_format($produto->preco_promocional, 2, ',', '.') }}</span>
            @else
            <span class="produto-detalhe-preco">R$ {{ number_format($produto->preco, 2, ',', '.') }}</span>
            @endif
        </div>
    </div>

    @if($produto->ativo)
    <form id="formAdicionarCarrinho">
        @csrf

        {{-- Grupos de adicionais --}}
        @foreach($produto->gruposAdicionais as $grupo)
        <div class="adicional-grupo" id="grupo-{{ $grupo->id }}">
            <div class="adicional-grupo-header">
                <div>
                    <span class="adicional-grupo-nome">{{ $grupo->nome }}</span>
                    @if($grupo->obrigatorio)
                    <span class="adicional-badge-req">Obrigatório</span>
                    @endif
                </div>
                @if($grupo->max_selecao > 1)
                <span class="adicional-grupo-max">máx. {{ $grupo->max_selecao }}</span>
                @endif
            </div>

            <div class="adicional-opcoes">
                @foreach($grupo->adicionais as $adicional)
                <label class="adicional-item {{ !$adicional->ativo ? 'adicional-item-off' : '' }}">
                    @if($grupo->max_selecao <= 1)
                    <input type="radio" name="grupo_{{ $grupo->id }}"
                        value="{{ $adicional->id }}"
                        data-preco="{{ $adicional->preco }}"
                        data-nome="{{ $adicional->nome }}"
                        {{ $grupo->obrigatorio && $loop->first ? 'checked' : '' }}
                        {{ !$adicional->ativo ? 'disabled' : '' }}
                        onchange="atualizarTotal()">
                    @else
                    <input type="checkbox" name="adicional_{{ $adicional->id }}"
                        value="{{ $adicional->id }}"
                        data-grupo="{{ $grupo->id }}"
                        data-max="{{ $grupo->max_selecao }}"
                        data-preco="{{ $adicional->preco }}"
                        data-nome="{{ $adicional->nome }}"
                        {{ !$adicional->ativo ? 'disabled' : '' }}
                        onchange="atualizarTotal()">
                    @endif
                    <div class="adicional-item-info">
                        <span class="adicional-item-nome">{{ $adicional->nome }}</span>
                        @if($adicional->descricao)
                        <span class="adicional-item-desc">{{ $adicional->descricao }}</span>
                        @endif
                    </div>
                    @if($adicional->preco > 0)
                    <span class="adicional-item-preco">+ R$ {{ number_format($adicional->preco, 2, ',', '.') }}</span>
                    @else
                    <span class="adicional-item-preco">grátis</span>
                    @endif
                </label>
                @endforeach
            </div>
        </div>
        @endforeach

        {{-- Observação --}}
        <div class="produto-obs-grupo">
            <label class="produto-obs-label">Alguma observação? <span class="text-muted">(opcional)</span></label>
            <textarea name="observacao" class="produto-obs-input" rows="2"
                placeholder="Ex: Sem cebola, ponto da carne bem passado..."></textarea>
        </div>

        {{-- Quantidade e adicionar --}}
        <div class="produto-qty-bar">
            <div class="qty-controle">
                <button type="button" class="qty-btn" onclick="alterarQty(-1)">−</button>
                <span class="qty-valor" id="qtyValor">1</span>
                <button type="button" class="qty-btn" onclick="alterarQty(1)">+</button>
            </div>
            <button type="submit" class="btn-adicionar-carrinho" id="btnAdicionar">
                Adicionar · <span id="totalDisplay">R$ {{ number_format($produto->preco_promocional ?? $produto->preco, 2, ',', '.') }}</span>
            </button>
        </div>
    </form>
    @endif
</div>

<style>
.produto-detalhe-img-wrap { position: relative; }
.produto-detalhe-img { width: 100%; height: 260px; object-fit: cover; display: block; }
.produto-detalhe-img-placeholder { width: 100%; height: 200px; background: var(--cor-borda); display: flex; align-items: center; justify-content: center; font-size: 3rem; color: var(--cor-texto-muted); }
.produto-indisponivel-overlay { position: absolute; inset: 0; background: rgba(0,0,0,.5); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 1.1rem; }
.produto-detalhe-corpo { padding: 0 0 100px; }
.produto-detalhe-header { padding: 16px; background: var(--cor-card); margin-bottom: 8px; }
.produto-detalhe-nome { font-size: 1.25rem; font-weight: 800; margin-bottom: 6px; }
.produto-detalhe-desc { color: var(--cor-texto-muted); font-size: 0.88rem; line-height: 1.5; margin-bottom: 10px; }
.produto-detalhe-preco-wrap { display: flex; align-items: baseline; gap: 8px; }
.produto-detalhe-preco { font-size: 1.5rem; font-weight: 800; color: var(--cor-primaria); }
.produto-preco-de { font-size: 0.9rem; color: var(--cor-texto-muted); text-decoration: line-through; }
.adicional-grupo { background: var(--cor-card); margin-bottom: 8px; }
.adicional-grupo-header { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px 8px; border-bottom: 1px solid var(--cor-borda); }
.adicional-grupo-nome { font-weight: 800; font-size: 0.95rem; }
.adicional-badge-req { font-size: 0.7rem; background: var(--cor-primaria); color: #fff; border-radius: 20px; padding: 2px 8px; margin-left: 6px; font-weight: 700; }
.adicional-grupo-max { font-size: 0.75rem; color: var(--cor-texto-muted); }
.adicional-opcoes { padding: 4px 0; }
.adicional-item { display: flex; align-items: center; gap: 12px; padding: 10px 16px; cursor: pointer; transition: background .1s; }
.adicional-item:hover { background: var(--cor-fundo); }
.adicional-item-off { opacity: .5; pointer-events: none; }
.adicional-item input { width: 18px; height: 18px; accent-color: var(--cor-primaria); flex-shrink: 0; }
.adicional-item-info { flex: 1; }
.adicional-item-nome { font-weight: 600; font-size: 0.9rem; display: block; }
.adicional-item-desc { font-size: 0.75rem; color: var(--cor-texto-muted); }
.adicional-item-preco { font-size: 0.85rem; font-weight: 700; color: var(--cor-primaria); white-space: nowrap; }
.produto-obs-grupo { background: var(--cor-card); margin-bottom: 8px; padding: 12px 16px; }
.produto-obs-label { font-weight: 700; font-size: 0.88rem; display: block; margin-bottom: 6px; }
.produto-obs-input { width: 100%; border: 1.5px solid var(--cor-borda); border-radius: 10px; padding: 8px 12px; font-family: inherit; font-size: 0.88rem; resize: none; background: var(--cor-fundo); color: var(--cor-texto); }
.produto-qty-bar { position: fixed; bottom: 0; left: 0; right: 0; background: var(--cor-card); border-top: 1px solid var(--cor-borda); padding: 12px 16px; display: flex; align-items: center; gap: 12px; z-index: 100; box-shadow: 0 -4px 20px rgba(0,0,0,.1); }
.qty-controle { display: flex; align-items: center; gap: 0; border: 2px solid var(--cor-borda); border-radius: 12px; overflow: hidden; flex-shrink: 0; }
.qty-btn { width: 36px; height: 36px; background: var(--cor-fundo); border: none; font-size: 1.2rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background .15s; }
.qty-btn:hover { background: var(--cor-borda); }
.qty-valor { min-width: 36px; text-align: center; font-weight: 800; font-size: 1rem; }
.btn-adicionar-carrinho { flex: 1; padding: 12px; background: var(--cor-primaria); color: #fff; border: none; border-radius: 14px; font-family: inherit; font-weight: 800; font-size: 0.95rem; cursor: pointer; transition: opacity .2s; }
.btn-adicionar-carrinho:hover { opacity: .9; }
</style>
@endsection

@push('scripts')
<script>
const precoProduto  = {{ $produto->preco_promocional ?? $produto->preco }};
let quantidade      = 1;

function alterarQty(delta) {
    quantidade = Math.max(1, quantidade + delta);
    document.getElementById('qtyValor').textContent = quantidade;
    atualizarTotal();
}

function atualizarTotal() {
    let extras = 0;
    document.querySelectorAll('input[type=radio]:checked[data-preco], input[type=checkbox]:checked[data-preco]').forEach(el => {
        extras += parseFloat(el.dataset.preco) || 0;
    });
    const total = (precoProduto + extras) * quantidade;
    document.getElementById('totalDisplay').textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
}

document.querySelectorAll('input[type=checkbox][data-max]').forEach(cb => {
    cb.addEventListener('change', function() {
        const max     = parseInt(this.dataset.max);
        const grupoId = this.dataset.grupo;
        const checks  = document.querySelectorAll(`input[type=checkbox][data-grupo="${grupoId}"]:checked`);
        if (checks.length > max) { this.checked = false; }
    });
});

document.getElementById('formAdicionarCarrinho')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const adicionaisSelecionados = [];
    document.querySelectorAll('input[type=radio]:checked[data-preco], input[type=checkbox]:checked[data-preco]').forEach(el => {
        adicionaisSelecionados.push({ id: el.value, nome: el.dataset.nome, preco: parseFloat(el.dataset.preco) || 0 });
    });

    let extras = adicionaisSelecionados.reduce((sum, a) => sum + a.preco, 0);
    const item = {
        id:          {{ $produto->id }},
        nome:        '{{ addslashes($produto->nome) }}',
        preco:       precoProduto,
        preco_unit:  precoProduto + extras,
        imagem:      '{{ $produto->imagem_principal ? asset("storage/" . $produto->imagem_principal) : asset("img/placeholder.svg") }}',
        quantidade,
        adicionais:  adicionaisSelecionados,
        observacao:  document.querySelector('textarea[name=observacao]')?.value || '',
    };

    if (typeof adicionarAoCarrinho === 'function') {
        adicionarAoCarrinho(item);
        mostrarToast(item.nome + ' adicionado!', 'sucesso');
        history.back();
    }
});
</script>
@endpush
