@extends('layouts.admin')
@section('titulo', 'Lojas')

@section('conteudo')
<div class="d-flex align-items-center justify-content-between mb-4">
    <p class="text-muted mb-0">Gerencie todas as lojas do sistema.</p>
    <a href="{{ route('admin.lojas.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nova Loja
    </a>
</div>

<div class="card-admin">
    <div class="card-admin-body p-0">
        <table class="tabela-admin">
            <thead>
                <tr>
                    <th style="width:60px">Logo</th>
                    <th>Nome</th>
                    <th>Slug / URL</th>
                    <th>Cidade</th>
                    <th>Taxa Entrega</th>
                    <th>Status</th>
                    <th style="width:140px">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lojas as $loja)
                <tr>
                    <td>
                        @if($loja->logo)
                        <img src="{{ $loja->logo_url }}" style="width:44px;height:44px;border-radius:8px;object-fit:cover">
                        @else
                        <div style="width:44px;height:44px;border-radius:8px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;font-size:1.5rem">🍔</div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $loja->nome }}</strong><br>
                        <small class="text-muted">{{ $loja->telefone }}</small>
                    </td>
                    <td>
                        <code>{{ $loja->slug }}</code><br>
                        <a href="{{ url('/' . $loja->slug) }}" target="_blank" class="text-muted small">
                            <i class="bi bi-box-arrow-up-right"></i> Ver loja
                        </a>
                    </td>
                    <td>{{ $loja->cidade }}/{{ $loja->estado }}</td>
                    <td>
                        @if($loja->tipo_taxa_entrega === 'gratis')
                        <span class="badge badge-success">Grátis</span>
                        @elseif($loja->tipo_taxa_entrega === 'bairro')
                        <span class="badge badge-info">Por bairro</span>
                        @elseif($loja->tipo_taxa_entrega === 'por_km')
                        <span class="badge badge-secondary">Por km</span>
                        @else
                        R$ {{ number_format($loja->taxa_entrega_fixa, 2, ',', '.') }}
                        @endif
                    </td>
                    <td>
                        @if($loja->ativo)
                        <span class="badge badge-success"><i class="bi bi-circle-fill"></i> Ativa</span>
                        @else
                        <span class="badge badge-danger"><i class="bi bi-circle-fill"></i> Inativa</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.lojas.edit', $loja) }}" class="btn btn-sm btn-primary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="{{ route('admin.lojas.bairros', $loja) }}" class="btn btn-sm btn-secondary" title="Bairros">
                                <i class="bi bi-geo-alt"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-shop fs-1 d-block mb-2"></i>
                        Nenhuma loja cadastrada.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
