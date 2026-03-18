@extends('layouts.admin')
@section('titulo', 'Editar Produto — ' . $produto->nome)

@section('conteudo')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.produtos.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
    <span class="badge badge-secondary">Editando: <strong>{{ $produto->nome }}</strong></span>
</div>

<form action="{{ route('admin.produtos.update', $produto) }}" method="POST" id="formProduto" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('admin.produtos._form', ['produto' => $produto])
</form>
@endsection
