@extends('layouts.admin')
@section('titulo', 'Novo Produto')

@section('conteudo')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.produtos.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<form action="{{ route('admin.produtos.store') }}" method="POST" id="formProduto" enctype="multipart/form-data">
    @csrf
    @include('admin.produtos._form', ['produto' => null])
</form>
@endsection
