@extends('layouts.admin')
@section('titulo', 'Novo Funcionário')

@section('conteudo')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.funcionarios.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<form action="{{ route('admin.funcionarios.store') }}" method="POST">
    @csrf
    @include('admin.funcionarios._form', ['funcionario' => null])
</form>
@endsection
