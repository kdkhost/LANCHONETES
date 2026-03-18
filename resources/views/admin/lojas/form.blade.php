@extends('layouts.admin')
@section('titulo', isset($loja) ? 'Editar Loja — ' . $loja->nome : 'Nova Loja')

@section('conteudo')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.lojas.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
    @if(isset($loja))
    <a href="{{ route('admin.lojas.bairros', $loja) }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-geo-alt"></i> Bairros de Entrega
    </a>
    <a href="{{ url('/' . $loja->slug) }}" target="_blank" class="btn btn-secondary btn-sm">
        <i class="bi bi-box-arrow-up-right"></i> Ver Loja
    </a>
    @endif
</div>

<form action="{{ isset($loja) ? route('admin.lojas.update', $loja) : route('admin.lojas.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($loja)) @method('PUT') @endif
    @include('admin.lojas._form', ['loja' => $loja ?? null])
</form>

@if(isset($loja))
<div class="card-admin mt-4">
    <div class="card-admin-header">
        <h3><i class="bi bi-credit-card"></i> Credenciais MercadoPago</h3>
    </div>
    <div class="card-admin-body">
        <form action="{{ route('admin.lojas.mercadopago', $loja) }}" method="POST">
            @csrf
            <div class="campo-row">
                <div class="campo-grupo">
                    <label class="campo-label">Public Key</label>
                    <input type="text" name="mercadopago_public_key" class="campo-input" value="{{ $loja->mercadopago_public_key }}" placeholder="APP_USR-xxxxx">
                </div>
                <div class="campo-grupo">
                    <label class="campo-label">Access Token</label>
                    <input type="text" name="mercadopago_access_token" class="campo-input" value="{{ $loja->mercadopago_access_token }}" placeholder="APP_USR-xxxxx">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Salvar Credenciais MP
            </button>
        </form>
    </div>
</div>
@endif
@endsection
