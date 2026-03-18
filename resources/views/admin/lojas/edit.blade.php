@extends('layouts.admin')
@section('titulo', 'Editar Loja — ' . $loja->nome)

@section('conteudo')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.lojas.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
    <a href="{{ route('admin.lojas.bairros', $loja) }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-geo-alt"></i> Bairros de Entrega
    </a>
    <a href="{{ url('/' . $loja->slug) }}" target="_blank" class="btn btn-secondary btn-sm">
        <i class="bi bi-box-arrow-up-right"></i> Ver Loja
    </a>
</div>

<form action="{{ route('admin.lojas.update', $loja) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('admin.lojas._form', ['loja' => $loja])
</form>

{{-- Configurações MercadoPago --}}
<div class="card-admin mt-4">
    <div class="card-admin-header">
        <h3><i class="bi bi-credit-card"></i> Credenciais MercadoPago</h3>
    </div>
    <div class="card-admin-body">
        <form action="{{ route('admin.lojas.mercadopago', $loja) }}" method="POST" id="formMP">
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
                <i class="bi bi-save"></i> Salvar Credenciais
            </button>
        </form>
    </div>
</div>
@endsection
