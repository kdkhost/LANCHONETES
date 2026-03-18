@extends('layouts.admin')
@section('titulo', 'Categorias')

@section('conteudo')
<div class="d-flex align-items-center justify-content-between mb-4">
    <p class="text-muted mb-0">Arraste para reordenar as categorias.</p>
    <button onclick="document.getElementById('modalNovaCategoria').classList.add('ativo')" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nova Categoria
    </button>
</div>

<div class="card-admin">
    <div class="card-admin-body p-0">
        <table class="tabela-admin" id="tabelaCategorias">
            <thead>
                <tr>
                    <th style="width:40px"></th>
                    <th style="width:60px">Img</th>
                    <th>Nome</th>
                    <th>Ícone</th>
                    <th>Produtos</th>
                    <th>Status</th>
                    <th style="width:120px">Ações</th>
                </tr>
            </thead>
            <tbody id="sortableCategorias">
                @forelse($categorias as $categoria)
                <tr data-id="{{ $categoria->id }}">
                    <td class="drag-handle" style="cursor:grab;color:#aaa;font-size:1.2rem">
                        <i class="bi bi-grip-vertical"></i>
                    </td>
                    <td>
                        @if($categoria->imagem)
                        <img src="{{ $categoria->imagem_url }}" alt="{{ $categoria->nome }}"
                            style="width:44px;height:44px;object-fit:cover;border-radius:8px;">
                        @else
                        <div style="width:44px;height:44px;border-radius:8px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;font-size:1.3rem">
                            <i class="bi bi-{{ $categoria->icone ?? 'tag' }}"></i>
                        </div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $categoria->nome }}</strong>
                        @if($categoria->destaque)<span class="badge badge-warning ms-1">Destaque</span>@endif
                    </td>
                    <td><code>{{ $categoria->icone ?? '—' }}</code></td>
                    <td>{{ $categoria->produtos_count ?? 0 }}</td>
                    <td>
                        <label class="switch-label">
                            <input type="checkbox" class="switch-input toggle-status" data-id="{{ $categoria->id }}" {{ $categoria->ativo ? 'checked' : '' }}>
                            <span class="switch-slider"></span>
                            <span>{{ $categoria->ativo ? 'Ativa' : 'Inativa' }}</span>
                        </label>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button onclick="editarCategoria({{ $categoria->id }}, '{{ addslashes($categoria->nome) }}', '{{ $categoria->icone }}')"
                                class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></button>
                            <button onclick="confirmarExclusao('{{ route('admin.categorias.destroy', $categoria) }}', 'Excluir categoria {{ addslashes($categoria->nome) }}?')"
                                class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="bi bi-tags fs-1 d-block mb-2"></i>
                        Nenhuma categoria cadastrada.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Nova/Editar Categoria --}}
<div class="admin-modal-overlay" id="modalOverlayCategoria" onclick="fecharModalCategoria()"></div>
<div class="admin-modal" id="modalNovaCategoria">
    <div class="admin-modal-header">
        <h3 id="modalCatTitulo">Nova Categoria</h3>
        <button onclick="fecharModalCategoria()" class="btn-fechar-modal"><i class="bi bi-x-lg"></i></button>
    </div>
    <form id="formCategoria" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="categoriaId" name="_categoria_id">
        <div class="campo-grupo">
            <label class="campo-label">Nome *</label>
            <input type="text" name="nome" id="catNome" class="campo-input" placeholder="Ex: Lanches" required>
        </div>
        <div class="campo-grupo">
            <label class="campo-label">Ícone (Bootstrap Icons)</label>
            <input type="text" name="icone" id="catIcone" class="campo-input" placeholder="Ex: burger, cup-straw, box-seam">
            <span class="campo-hint">Consulte em <a href="https://icons.getbootstrap.com" target="_blank">icons.getbootstrap.com</a></span>
        </div>
        <div class="campo-grupo">
            <label class="campo-label">Imagem (opcional)</label>
            <div class="upload-area" data-url="{{ route('admin.categorias.upload') }}">
                <input type="file" accept="image/*" name="imagem">
                <div class="upload-area-icon"><i class="bi bi-image"></i></div>
                <p>Arraste ou <span>clique para selecionar</span></p>
            </div>
            <div class="upload-progresso"><div class="upload-progresso-barra"></div></div>
            <div class="upload-preview" id="previewCategoria"></div>
        </div>
        <div class="campo-grupo">
            <label class="switch-label">
                <input type="checkbox" class="switch-input" name="destaque" id="catDestaque">
                <span class="switch-slider"></span>
                Categoria em destaque
            </label>
        </div>
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary" id="btnSalvarCat">Salvar</button>
            <button type="button" onclick="fecharModalCategoria()" class="btn btn-secondary">Cancelar</button>
        </div>
    </form>
</div>

<style>
.admin-modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:199;display:none; }
.admin-modal-overlay.ativo { display:block; }
.admin-modal { position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(.95);background:#fff;border-radius:12px;padding:24px;width:95%;max-width:500px;z-index:200;box-shadow:0 8px 32px rgba(0,0,0,.2);display:none;max-height:90vh;overflow-y:auto;transition:.2s; }
.admin-modal.ativo { display:block;transform:translate(-50%,-50%) scale(1); }
.admin-modal-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:16px; }
.admin-modal-header h3 { font-size:1.05rem;font-weight:800;margin:0; }
.btn-fechar-modal { background:none;border:none;cursor:pointer;font-size:1.1rem;color:#6c757d; }
</style>
@endsection

@push('scripts')
<script>
// Toggle ativo/inativo
document.querySelectorAll('.toggle-status').forEach(cb => {
    cb.addEventListener('change', async function() {
        const id = this.dataset.id;
        const data = await ajaxAdmin(`/admin/categorias/${id}`, { ativo: this.checked }, 'PUT');
        mostrarToast(data.mensagem || 'Status atualizado', 'sucesso');
    });
});

function editarCategoria(id, nome, icone) {
    document.getElementById('categoriaId').value = id;
    document.getElementById('catNome').value  = nome;
    document.getElementById('catIcone').value = icone || '';
    document.getElementById('modalCatTitulo').textContent = 'Editar Categoria';
    document.getElementById('modalNovaCategoria').classList.add('ativo');
    document.getElementById('modalOverlayCategoria').classList.add('ativo');
}

function fecharModalCategoria() {
    document.getElementById('modalNovaCategoria').classList.remove('ativo');
    document.getElementById('modalOverlayCategoria').classList.remove('ativo');
    document.getElementById('formCategoria').reset();
    document.getElementById('categoriaId').value = '';
    document.getElementById('previewCategoria').innerHTML = '';
    document.getElementById('modalCatTitulo').textContent = 'Nova Categoria';
}

document.getElementById('formCategoria').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('categoriaId').value;
    const url = id ? `/admin/categorias/${id}` : '/admin/categorias';
    const btn = document.getElementById('btnSalvarCat');
    btn.disabled = true; btn.textContent = 'Salvando...';

    const fd = new FormData(this);
    if (id) fd.append('_method', 'PUT');

    try {
        const res  = await fetch(url, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        if (data.sucesso) { mostrarToast('Categoria salva!', 'sucesso'); fecharModalCategoria(); setTimeout(() => location.reload(), 700); }
        else mostrarToast(data.erro || 'Erro ao salvar.', 'erro');
    } catch { mostrarToast('Erro de conexão.', 'erro'); }
    finally { btn.disabled = false; btn.textContent = 'Salvar'; }
});

inicializarUploadAdmin('.upload-area[data-url]', '/admin/categorias/upload', 'imagem_path', resp => {
    const prev = document.getElementById('previewCategoria');
    prev.innerHTML = `<div class="upload-preview-item"><img src="${resp.url}"><input type="hidden" name="imagem_path" value="${resp.caminho}"></div>`;
});
</script>
@endpush
