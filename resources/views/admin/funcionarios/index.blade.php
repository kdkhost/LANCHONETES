@extends('layouts.admin')
@section('titulo', 'Funcionários')

@section('conteudo')
<div class="d-flex align-items-center justify-content-between mb-4">
    <p class="text-muted mb-0">Funcionários e entregadores da loja.</p>
    <a href="{{ route('admin.funcionarios.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus"></i> Novo Funcionário
    </a>
</div>

<div class="card-admin">
    <div class="card-admin-body p-0">
        <table class="tabela-admin">
            <thead>
                <tr>
                    <th style="width:50px">Foto</th>
                    <th>Nome</th>
                    <th>Cargo</th>
                    <th>Tipo</th>
                    <th>Entregador</th>
                    <th>Status</th>
                    <th style="width:120px">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($funcionarios as $func)
                <tr>
                    <td>
                        <img src="{{ $func->usuario->foto_perfil_url }}" alt="{{ $func->usuario->nome }}"
                            style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid var(--adm-borda)">
                    </td>
                    <td>
                        <strong>{{ $func->usuario->nome }}</strong><br>
                        <small class="text-muted">{{ $func->usuario->email }}</small>
                    </td>
                    <td>{{ $func->cargo ?? '—' }}</td>
                    <td>
                        <span class="badge badge-secondary">
                            {{ config('lanchonete.funcionario.tipos.' . $func->tipo, $func->tipo) }}
                        </span>
                    </td>
                    <td>
                        @if($func->e_entregador)
                        <span class="badge {{ $func->disponivel_entregas ? 'badge-success' : 'badge-secondary' }}">
                            <i class="bi bi-bicycle"></i>
                            {{ $func->disponivel_entregas ? 'Disponível' : 'Offline' }}
                        </span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $func->ativo ? 'badge-success' : 'badge-danger' }}">
                            {{ $func->ativo ? 'Ativo' : 'Inativo' }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.funcionarios.edit', $func) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button onclick="confirmarExclusao('{{ route('admin.funcionarios.destroy', $func) }}', 'Excluir funcionário {{ addslashes($func->usuario->nome) }}?')"
                                class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-people fs-1 d-block mb-2"></i>
                        Nenhum funcionário cadastrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $funcionarios->links('vendor.pagination.simple-bootstrap') }}
@endsection
