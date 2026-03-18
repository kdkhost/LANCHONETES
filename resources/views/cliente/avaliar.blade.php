@extends('layouts.pwa')
@section('titulo', 'Avaliar Pedido')

@section('conteudo')
<div class="page-header">
    <button onclick="history.back()" class="btn-voltar"><i class="bi bi-arrow-left"></i></button>
    <h1 class="page-title">Avaliar Pedido</h1>
</div>

<div class="avaliar-container">
    <div class="avaliar-loja">
        <img src="{{ $pedido->loja->logo_url }}" alt="{{ $pedido->loja->nome }}" class="avaliar-loja-logo">
        <div>
            <div class="avaliar-loja-nome">{{ $pedido->loja->nome }}</div>
            <div class="avaliar-pedido-info">Pedido #{{ $pedido->numero }} · {{ $pedido->created_at->format('d/m/Y') }}</div>
        </div>
    </div>

    <form id="formAvaliar">
        @csrf
        {{-- Nota geral --}}
        <div class="avaliar-card">
            <p class="avaliar-label">Como você avalia a experiência geral?</p>
            <div class="estrelas-input" id="estrelasGeral">
                @for($i = 1; $i <= 5; $i++)
                <span class="estrela" data-nota="{{ $i }}" onclick="selecionarEstrela('geral', {{ $i }})">★</span>
                @endfor
            </div>
            <input type="hidden" name="nota" id="notaGeral" value="0" required>
        </div>

        {{-- Avaliações específicas --}}
        @foreach([
            ['comida', '🍔', 'Qualidade da comida'],
            ['entrega', '🛵', 'Rapidez da entrega'],
            ['embalagem', '📦', 'Embalagem'],
        ] as [$campo, $icone, $label])
        <div class="avaliar-card">
            <p class="avaliar-label">{{ $icone }} {{ $label }}</p>
            <div class="estrelas-input" id="estrelas{{ ucfirst($campo) }}">
                @for($i = 1; $i <= 5; $i++)
                <span class="estrela" data-nota="{{ $i }}" onclick="selecionarEstrela('{{ $campo }}', {{ $i }})">★</span>
                @endfor
            </div>
            <input type="hidden" name="{{ $campo }}_nota" id="nota{{ ucfirst($campo) }}" value="0">
        </div>
        @endforeach

        {{-- Comentário --}}
        <div class="avaliar-card">
            <p class="avaliar-label">Deixe um comentário (opcional)</p>
            <textarea name="comentario" class="campo-input" rows="3"
                placeholder="Conte o que você achou do pedido..."></textarea>
        </div>

        {{-- Tags rápidas --}}
        <div class="avaliar-card">
            <p class="avaliar-label">O que você gostou?</p>
            <div class="avaliar-tags" id="tagsSelecionadas">
                @foreach(['Sabor ótimo','Entrega rápida','Boa embalagem','Porção generosa','Preço justo','Tudo perfeito!'] as $tag)
                <button type="button" class="tag-btn" onclick="toggleTag(this, '{{ $tag }}')">{{ $tag }}</button>
                @endforeach
            </div>
            <input type="hidden" name="tags" id="tagsInput">
        </div>

        <div id="erroAvaliar" class="alerta alerta-erro mx-3 mb-2" style="display:none"></div>

        <div class="avaliar-acoes">
            <button type="submit" class="btn btn-primario w-100" id="btnEnviarAvaliacao">
                <i class="bi bi-star-fill"></i> Enviar Avaliação
            </button>
        </div>
    </form>
</div>

<style>
.avaliar-container { padding: 0 0 20px; }
.avaliar-loja { display: flex; align-items: center; gap: 12px; padding: 16px; background: var(--cor-card); margin-bottom: 8px; }
.avaliar-loja-logo { width: 52px; height: 52px; border-radius: 12px; object-fit: cover; border: 2px solid var(--cor-borda); }
.avaliar-loja-nome { font-weight: 800; font-size: 1rem; }
.avaliar-pedido-info { font-size: 0.8rem; color: var(--cor-texto-muted); margin-top: 2px; }
.avaliar-card { background: var(--cor-card); margin: 8px 12px; border-radius: 14px; padding: 14px; box-shadow: var(--sombra-sm); }
.avaliar-label { font-weight: 700; font-size: 0.9rem; margin: 0 0 10px; }
.estrelas-input { display: flex; gap: 6px; }
.estrela { font-size: 2.2rem; cursor: pointer; color: #ddd; transition: color .1s, transform .1s; line-height: 1; }
.estrela:hover, .estrela.ativa { color: #ffc107; transform: scale(1.1); }
.avaliar-tags { display: flex; flex-wrap: wrap; gap: 8px; }
.tag-btn { padding: 7px 14px; border-radius: 20px; border: 1.5px solid var(--cor-borda); background: var(--cor-fundo); font-family: inherit; font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: all .15s; }
.tag-btn.selecionada { background: var(--cor-primaria); color: #fff; border-color: var(--cor-primaria); }
.avaliar-acoes { padding: 8px 12px 20px; }
</style>
@endsection

@push('scripts')
<script>
const tagsSelecionadas = new Set();

function selecionarEstrela(campo, nota) {
    const mapa = { geral: 'notaGeral', comida: 'notaComida', entrega: 'notaEntrega', embalagem: 'notaEmbalagem' };
    const idInput = mapa[campo];
    const idDiv   = 'estrelas' + campo.charAt(0).toUpperCase() + campo.slice(1);

    document.getElementById(idInput).value = nota;
    document.querySelectorAll(`#${idDiv} .estrela`).forEach((el, i) => {
        el.classList.toggle('ativa', i < nota);
    });
}

function toggleTag(btn, tag) {
    if (tagsSelecionadas.has(tag)) {
        tagsSelecionadas.delete(tag);
        btn.classList.remove('selecionada');
    } else {
        tagsSelecionadas.add(tag);
        btn.classList.add('selecionada');
    }
    document.getElementById('tagsInput').value = JSON.stringify([...tagsSelecionadas]);
}

document.getElementById('formAvaliar').addEventListener('submit', async function(e) {
    e.preventDefault();
    const nota = parseInt(document.getElementById('notaGeral').value);
    const erro = document.getElementById('erroAvaliar');
    if (!nota) { erro.textContent = 'Por favor, selecione pelo menos a nota geral.'; erro.style.display = ''; return; }
    erro.style.display = 'none';

    const btn = document.getElementById('btnEnviarAvaliacao');
    btn.disabled = true; btn.innerHTML = '<div class="spinner-btn"></div>';

    const fd = new FormData(this);
    try {
        const res  = await fetch('/avaliar/{{ $pedido->id }}', {
            method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data.sucesso) {
            mostrarToast('Obrigado pela avaliação! ⭐', 'sucesso');
            setTimeout(() => window.location.href = data.redirect || '/', 1000);
        } else {
            erro.textContent = data.erro || 'Erro ao enviar avaliação.';
            erro.style.display = '';
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-star-fill"></i> Enviar Avaliação';
        }
    } catch {
        erro.textContent = 'Erro de conexão.';
        erro.style.display = '';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-star-fill"></i> Enviar Avaliação';
    }
});
</script>
@endpush
