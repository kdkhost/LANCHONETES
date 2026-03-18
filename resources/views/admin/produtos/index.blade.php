@extends('layouts.admin')
@section('titulo', 'Produtos')

@section('conteudo')
<div class="d-flex align-items-center justify-content-between gap-2 mb-4">
    <div class="d-flex gap-2">
        <input type="text" id="buscaProduto" class="campo-input" placeholder="Buscar produto..." style="width:220px">
        <select id="filtroCat" class="campo-input" style="width:auto">
            <option value="">Todas as categorias</option>
            @foreach($categorias as $cat)
            <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
            @endforeach
        </select>
    </div>
    <a href="{{ route('admin.produtos.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Novo Produto
    </a>
</div>

<div class="card-admin">
    <div class="card-admin-body p-0">
        <table class="tabela-admin" id="tabelaProdutos">
            <thead>
                <tr>
                    <th style="width:60px">Img</th>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Preço</th>
                    <th>Estoque/Status</th>
                    <th>Destaque</th>
                    <th style="width:120px">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($produtos as $produto)
                <tr data-cat="{{ $produto->categoria_id }}" data-nome="{{ strtolower($produto->nome) }}">
                    <td>
                        <img src="{{ $produto->imagem_url }}" alt="{{ $produto->nome }}"
                            style="width:44px;height:44px;object-fit:cover;border-radius:8px;">
                    </td>
                    <td>
                        <strong>{{ $produto->nome }}</strong>
                        @if($produto->novo)<span class="badge badge-warning ms-1">Novo</span>@endif
                        <br><small class="text-muted">{{ Str::limit($produto->descricao, 50) }}</small>
                    </td>
                    <td><span class="badge badge-secondary">{{ $produto->categoria->nome ?? '—' }}</span></td>
                    <td>
                        @if($produto->tem_promocao)
                        <small class="text-muted" style="text-decoration:line-through">R$ {{ number_format($produto->preco, 2, ',', '.') }}</small><br>
                        <strong class="text-success">R$ {{ number_format($produto->preco_atual, 2, ',', '.') }}</strong>
                        @else
                        <strong>R$ {{ number_format($produto->preco, 2, ',', '.') }}</strong>
                        @endif
                    </td>
                    <td>
                        <button onclick="toggleStatusProduto({{ $produto->id }}, this)"
                            class="btn btn-sm {{ $produto->ativo ? 'btn-success' : 'btn-secondary' }}"
                            title="{{ $produto->ativo ? 'Ativo' : 'Inativo' }}">
                            <i class="bi bi-{{ $produto->ativo ? 'check-circle' : 'x-circle' }}"></i>
                            {{ $produto->ativo ? 'Ativo' : 'Inativo' }}
                        </button>
                    </td>
                    <td class="text-center">
                        @if($produto->destaque)
                        <i class="bi bi-star-fill text-warning"></i>
                        @else
                        <i class="bi bi-star text-muted"></i>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.produtos.edit', $produto) }}" class="btn btn-sm btn-primary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button onclick="confirmarExclusao('{{ route('admin.produtos.destroy', $produto) }}', 'Excluir produto {{ addslashes($produto->nome) }}?')"
                                class="btn btn-sm btn-outline-danger" title="Excluir">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                        Nenhum produto cadastrado ainda.
                        <a href="{{ route('admin.produtos.create') }}" class="d-block mt-2">Adicionar primeiro produto</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $produtos->links('vendor.pagination.simple-bootstrap') }}
@endsection

@push('scripts')
<script>
document.getElementById('buscaProduto').addEventListener('input', filtrar);
document.getElementById('filtroCat').addEventListener('change', filtrar);

function filtrar() {
    const busca = document.getElementById('buscaProduto').value.toLowerCase();
    const cat   = document.getElementById('filtroCat').value;
    document.querySelectorAll('#tabelaProdutos tbody tr').forEach(tr => {
        const nome = tr.dataset.nome || '';
        const catId= tr.dataset.cat || '';
        const ok   = (!busca || nome.includes(busca)) && (!cat || catId === cat);
        tr.style.display = ok ? '' : 'none';
    });
}
</script>
@endpush
