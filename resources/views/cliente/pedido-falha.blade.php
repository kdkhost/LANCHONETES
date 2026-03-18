@extends('layouts.pwa')
@section('titulo', 'Pagamento não aprovado')

@section('conteudo')
<div class="resultado-container">
    <div class="resultado-icone falha">
        <i class="bi bi-x-circle-fill"></i>
    </div>
    <h1 class="resultado-titulo">Pagamento não aprovado</h1>
    <p class="resultado-subtitulo">Seu pagamento foi recusado. Verifique os dados do cartão e tente novamente.</p>

    <div class="resultado-card">
        <div class="resultado-linha">
            <span>Pedido</span>
            <strong>#{{ $pedido->numero }}</strong>
        </div>
        <div class="resultado-linha">
            <span>Valor</span>
            <strong>R$ {{ number_format($pedido->total, 2, ',', '.') }}</strong>
        </div>
    </div>

    <div class="resultado-dicas">
        <p class="dica-titulo">Possíveis motivos:</p>
        <ul class="dica-lista">
            <li>Saldo insuficiente</li>
            <li>Dados do cartão incorretos</li>
            <li>Cartão bloqueado para compras online</li>
            <li>Limite de crédito atingido</li>
        </ul>
    </div>

    <div class="resultado-acoes">
        <a href="{{ route('cliente.checkout') }}" class="btn btn-primario w-100 mb-2">
            <i class="bi bi-arrow-repeat"></i> Tentar Novamente
        </a>
        <a href="{{ route('cliente.loja', $pedido->loja->slug) }}" class="btn btn-outline w-100">
            <i class="bi bi-house"></i> Voltar ao Cardápio
        </a>
    </div>
</div>

<style>
.resultado-container { display: flex; flex-direction: column; align-items: center; padding: 40px 20px 30px; text-align: center; }
.resultado-icone { font-size: 5rem; margin-bottom: 16px; }
.resultado-icone.falha { color: var(--cor-erro); }
.resultado-icone.pendente { color: var(--cor-aviso); }
.resultado-titulo { font-size: 1.4rem; font-weight: 800; margin: 0 0 8px; }
.resultado-subtitulo { color: var(--cor-texto-muted); margin: 0 0 24px; font-size: 0.9rem; line-height: 1.5; }
.resultado-card { width: 100%; background: var(--cor-card); border-radius: 16px; padding: 16px; box-shadow: var(--sombra-sm); margin-bottom: 16px; text-align: left; }
.resultado-linha { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--cor-borda); font-size: 0.9rem; }
.resultado-linha:last-child { border: none; }
.resultado-linha span:first-child { color: var(--cor-texto-muted); }
.resultado-dicas { width: 100%; background: #fff3cd; border-radius: 12px; padding: 14px; margin-bottom: 24px; text-align: left; }
.dica-titulo { font-weight: 700; font-size: 0.88rem; color: #856404; margin: 0 0 8px; }
.dica-lista { padding-left: 18px; margin: 0; font-size: 0.85rem; color: #856404; }
.dica-lista li { margin-bottom: 4px; }
.resultado-acoes { width: 100%; }
.mb-2 { margin-bottom: 8px; }
</style>
@endsection
