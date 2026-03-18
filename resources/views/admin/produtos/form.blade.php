@extends('layouts.admin')
@section('titulo', isset($produto) ? 'Editar Produto — ' . $produto->nome : 'Novo Produto')

@section('conteudo')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.produtos.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<form action="{{ isset($produto) ? route('admin.produtos.update', $produto) : route('admin.produtos.store') }}"
    method="POST" id="formProduto" enctype="multipart/form-data">
    @csrf
    @if(isset($produto)) @method('PUT') @endif
    @include('admin.produtos._form', ['produto' => $produto ?? null])
</form>
@endsection
