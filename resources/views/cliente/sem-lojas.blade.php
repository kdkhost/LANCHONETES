@extends('layouts.pwa')
@section('titulo', 'Nenhuma Loja Disponível')

@section('conteudo')
<div class="estado-vazio" style="min-height:60vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 20px;text-align:center">
    <div style="font-size:5rem;margin-bottom:16px">🍔</div>
    <h1 style="font-size:1.4rem;font-weight:800;color:var(--cor-titulo);margin-bottom:8px">Nenhuma loja disponível</h1>
    <p style="color:var(--cor-texto-muted);max-width:280px;line-height:1.6;margin-bottom:24px">
        No momento não há lojas cadastradas ou ativas neste sistema.
        Tente novamente mais tarde.
    </p>
    <a href="{{ url('/') }}" class="btn btn-primario">
        <i class="bi bi-arrow-clockwise"></i> Tentar novamente
    </a>
</div>
@endsection
