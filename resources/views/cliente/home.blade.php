@extends('layouts.pwa')
@section('titulo', $loja->nome)

@section('conteudo')
{{-- Banners --}}
@if($banners->count())
<div class="banner-slider" id="bannerSlider">
    @foreach($banners as $banner)
    <div class="banner-slide">
        <a href="{{ $banner->url ?? '#' }}">
            <img src="{{ $banner->imagem_url }}" alt="{{ $banner->titulo }}" class="banner-img" loading="lazy">
        </a>
    </div>
    @endforeach
</div>
@endif

{{-- Info da Loja --}}
<div class="loja-info-bar">
    <div class="loja-info-status {{ $estaAberta ? 'aberta' : 'fechada' }}">
        <i class="bi bi-circle-fill"></i>
        {{ $estaAberta ? 'Aberta' : 'Fechada' }}
    </div>
    <div class="loja-info-tempo">
        <i class="bi bi-clock"></i>
        {{ $loja->tempo_entrega_min }}–{{ $loja->tempo_entrega_max }} min
    </div>
    @if($loja->taxa_entrega_fixa > 0)
    <div class="loja-info-frete">
        <i class="bi bi-bicycle"></i>
        R$ {{ number_format($loja->taxa_entrega_fixa, 2, ',', '.') }}
    </div>
    @elseif($loja->tipo_taxa_entrega === 'gratis')
    <div class="loja-info-frete text-success">
        <i class="bi bi-bicycle"></i> Grátis
    </div>
    @endif
    @if($loja->pedido_minimo > 0)
    <div class="loja-info-minimo">
        <i class="bi bi-bag-check"></i>
        Mín. R$ {{ number_format($loja->pedido_minimo, 2, ',', '.') }}
    </div>
    @endif
</div>

{{-- Busca rápida --}}
<div class="busca-rapida px-3 pt-3">
    <div class="input-busca">
        <i class="bi bi-search"></i>
        <input type="text" id="inputBuscaRapida" placeholder="Buscar no cardápio..." autocomplete="off">
    </div>
</div>

{{-- Destaques --}}
@if($destaques->count())
<section class="secao-destaques">
    <h2 class="secao-titulo px-3">⭐ Destaques</h2>
    <div class="produtos-scroll">
        @foreach($destaques as $produto)
        <div class="produto-card-mini" onclick="abrirProduto('{{ $produto->slug }}')">
            <img src="{{ $produto->imagem_url }}" alt="{{ $produto->nome }}" loading="lazy">
            <div class="produto-mini-info">
                <span class="produto-mini-nome">{{ $produto->nome }}</span>
                <span class="produto-mini-preco">R$ {{ number_format($produto->preco_atual, 2, ',', '.') }}</span>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- Navegação de Categorias --}}
@if($categorias->count())
<div class="categorias-nav" id="categoriasNav">
    @foreach($categorias as $cat)
    <a href="#cat-{{ $cat->id }}" class="categoria-nav-item" data-cat="{{ $cat->id }}">
        @if($cat->icone)<i class="bi bi-{{ $cat->icone }}"></i>@endif
        {{ $cat->nome }}
    </a>
    @endforeach
</div>

{{-- Cardápio por Categoria --}}
<div class="cardapio" id="cardapio">
    @foreach($categorias as $categoria)
    @if($categoria->produtos->count())
    <section class="categoria-secao" id="cat-{{ $categoria->id }}" data-categoria="{{ $categoria->id }}">
        <h2 class="secao-titulo px-3">{{ $categoria->nome }}</h2>
        <div class="produtos-lista">
            @foreach($categoria->produtos as $produto)
            <div class="produto-card" onclick="abrirProduto('{{ $produto->slug }}')" data-produto-id="{{ $produto->id }}">
                <div class="produto-card-info">
                    <h3 class="produto-nome">{{ $produto->nome }}</h3>
                    @if($produto->descricao)
                    <p class="produto-descricao">{{ Str::limit($produto->descricao, 70) }}</p>
                    @endif
                    <div class="produto-preco-row">
                        @if($produto->tem_promocao)
                        <span class="produto-preco-original">R$ {{ number_format($produto->preco, 2, ',', '.') }}</span>
                        @endif
                        <span class="produto-preco {{ $produto->tem_promocao ? 'preco-promocao' : '' }}">
                            R$ {{ number_format($produto->preco_atual, 2, ',', '.') }}
                        </span>
                        @if($produto->novo)<span class="badge-novo">Novo</span>@endif
                    </div>
                </div>
                <div class="produto-card-img">
                    <img src="{{ $produto->imagem_url }}" alt="{{ $produto->nome }}" loading="lazy">
                    <button class="produto-add-btn" onclick="event.stopPropagation(); adicionarAoCarrinhoRapido({{ $produto->id }})">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif
    @endforeach
</div>
@endif

{{-- Modal de Produto --}}
<div class="modal-overlay" id="modalProdutoOverlay" onclick="fecharModalProduto()"></div>
<div class="modal-bottom" id="modalProduto">
    <div class="modal-bottom-handle"></div>
    <div id="modalProdutoConteudo">
        <div class="text-center py-5"><div class="spinner"></div></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const LOJA_SLUG_ATUAL = '{{ $loja->slug }}';

function abrirProduto(slug) {
    document.getElementById('modalProdutoOverlay').classList.add('ativo');
    document.getElementById('modalProduto').classList.add('ativo');
    document.body.style.overflow = 'hidden';

    fetch(`/${LOJA_SLUG_ATUAL}/produto/${slug}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.text())
        .then(html => {
            document.getElementById('modalProdutoConteudo').innerHTML = html;
            inicializarAdicionais();
        });
}

function fecharModalProduto() {
    document.getElementById('modalProdutoOverlay').classList.remove('ativo');
    document.getElementById('modalProduto').classList.remove('ativo');
    document.body.style.overflow = '';
}

function inicializarAdicionais() {
    document.querySelectorAll('.grupo-adicional').forEach(grupo => {
        const max = parseInt(grupo.dataset.max);
        const checkboxes = grupo.querySelectorAll('input[type=checkbox]');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                const marcados = grupo.querySelectorAll('input:checked').length;
                if (marcados >= max) {
                    checkboxes.forEach(c => { if (!c.checked) c.disabled = true; });
                } else {
                    checkboxes.forEach(c => c.disabled = false);
                }
                atualizarTotalProduto();
            });
        });
    });
    atualizarTotalProduto();
}

function atualizarTotalProduto() {
    const precoBase = parseFloat(document.getElementById('produtoPrecoBase')?.dataset.preco || 0);
    const qtd       = parseInt(document.getElementById('qtdProduto')?.value || 1);
    let extras = 0;
    document.querySelectorAll('.adicional-check:checked, .adicional-radio:checked').forEach(el => {
        extras += parseFloat(el.dataset.preco || 0) * parseInt(el.dataset.qtd || 1);
    });
    const total = (precoBase + extras) * qtd;
    const el = document.getElementById('totalProduto');
    if (el) el.textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
}

// Busca rápida inline
let timerBusca;
document.getElementById('inputBuscaRapida')?.addEventListener('input', function() {
    clearTimeout(timerBusca);
    const q = this.value.trim();
    if (q.length < 2) {
        document.querySelectorAll('.produto-card').forEach(c => c.style.display = '');
        document.querySelectorAll('.categoria-secao').forEach(s => s.style.display = '');
        return;
    }
    timerBusca = setTimeout(() => {
        document.querySelectorAll('.produto-card').forEach(card => {
            const nome = card.querySelector('.produto-nome')?.textContent.toLowerCase() || '';
            card.style.display = nome.includes(q.toLowerCase()) ? '' : 'none';
        });
        document.querySelectorAll('.categoria-secao').forEach(s => {
            const visíveis = s.querySelectorAll('.produto-card:not([style*="none"])').length;
            s.style.display = visíveis ? '' : 'none';
        });
    }, 300);
});

// Spy de scroll nas categorias
const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const id = entry.target.dataset.categoria;
            document.querySelectorAll('.categoria-nav-item').forEach(el => {
                el.classList.toggle('active', el.dataset.cat === id);
            });
            const activeEl = document.querySelector(`.categoria-nav-item[data-cat="${id}"]`);
            activeEl?.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }
    }); }, { threshold: 0.3 });

document.querySelectorAll('.categoria-secao').forEach(s => observer.observe(s));

// Banner autoplay
const slides = document.querySelectorAll('.banner-slide');
let bannerIdx = 0;
if (slides.length > 1) {
    setInterval(() => {
        slides[bannerIdx].classList.remove('ativo');
        bannerIdx = (bannerIdx + 1) % slides.length;
        slides[bannerIdx].classList.add('ativo');
    }, 4000);
}
if (slides.length) slides[0].classList.add('ativo');
</script>
@endpush
