@extends('layouts.admin')
@section('titulo', 'Nova Loja')

@section('conteudo')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.lojas.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<form action="{{ route('admin.lojas.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('admin.lojas._form', ['loja' => null])
</form>
@endsection
