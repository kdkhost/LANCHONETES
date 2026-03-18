@extends('layouts.admin')
@section('titulo', 'Banners')

@section('conteudo')
<div class="d-flex align-items-center justify-content-between mb-4">
    <p class="text-muted mb-0">Banners exibidos na tela inicial da loja.</p>
    <button onclick="document.getElementById('modalNovoBanner').classList.add('ativo');document.getElementById('modalOverlayBanner').classList.add('ativo')" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Novo Banner
    </button>
</div>

<div class="card-admin">
    <div class="card-admin-body p-0">
        <table class="tabela-admin">
            <thead>
                <tr>
                    <th style="width:120px">Imagem</th>
                    <th>Título</th>
                    <th>Link</th>
                    <th>Ordem</th>
                    <th>Status</th>
                    <th style="width:120px">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banners as $banner)
                <tr>
                    <td>
                        <img src="{{ $banner->imagem_url }}" alt="{{ $banner->titulo }}"
                            style="width:100px;height:50px;object-fit:cover;border-radius:8px;border:1px solid var(--adm-borda)">
                    </td>
                    <td>
                        <strong>{{ $banner->titulo ?? 'Sem título' }}</strong>
                        @if($banner->subtitulo)
                        <small class="d-block text-muted">{{ $banner->subtitulo }}</small>
                        @endif
                    </td>
                    <td>
                        @if($banner->url)
                        <a href="{{ $banner->url }}" target="_blank" class="text-muted small">
                            <i class="bi bi-link-45deg"></i> {{ Str::limit($banner->url, 30) }}
                        </a>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $banner->ordem }}</td>
                    <td>
                        <label class="switch-label">
                            <input type="checkbox" class="switch-input toggle-banner" data-id="{{ $banner->id }}" {{ $banner->ativo ? 'checked' : '' }}>
                            <span class="switch-slider"></span>
                        </label>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button onclick="editarBanner({{ $banner->id }}, '{{ addslashes($banner->titulo) }}', '{{ addslashes($banner->url ?? '') }}', {{ $banner->ordem }})"
                                class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></button>
                            <button onclick="confirmarExclusao('{{ route('admin.banners.destroy', $banner) }}', 'Excluir este banner?')"
                                class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-images fs-1 d-block mb-2"></i>
                        Nenhum banner cadastrado ainda.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Banner --}}
<div class="admin-modal-overlay" id="modalOverlayBanner" onclick="fecharModalBanner()"></div>
<div class="admin-modal" id="modalNovoBanner">
    <div class="admin-modal-header">
        <h3 id="modalBannerTitulo">Novo Banner</h3>
        <button onclick="fecharModalBanner()" class="btn-fechar-modal"><i class="bi bi-x-lg"></i></button>
    </div>
    <form id="formBanner">
        @csrf
        <input type="hidden" id="bannerId" name="_banner_id">
        <div class="campo-grupo">
            <label class="campo-label">Imagem do Banner *</label>
            <div class="upload-area" id="uploadAreaBanner" data-url="{{ route('admin.banners.upload') }}">
                <input type="file" accept="image/*">
                <div class="upload-area-icon"><i class="bi bi-image"></i></div>
                <p>Arraste ou <span>clique para selecionar</span></p>
                <p><small>Recomendado: 1200×400px — máx. 20 MB</small></p>
            </div>
            <div class="upload-progresso"><div class="upload-progresso-barra"></div></div>
            <div class="upload-preview mt-2" id="previewBanner"></div>
        </div>
        <div class="campo-grupo">
            <label class="campo-label">Título</label>
            <input type="text" name="titulo" id="bannerTitulo" class="campo-input" placeholder="Ex: Promoção do dia">
        </div>
        <div class="campo-grupo">
            <label class="campo-label">Link (opcional)</label>
            <input type="url" name="url" id="bannerLink" class="campo-input" placeholder="https://...">
        </div>
        <div class="campo-grupo">
            <label class="campo-label">Ordem</label>
            <input type="number" name="ordem" id="bannerOrdem" class="campo-input" value="0" min="0">
        </div>
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary" id="btnSalvarBanner">Salvar Banner</button>
            <button type="button" onclick="fecharModalBanner()" class="btn btn-secondary">Cancelar</button>
        </div>
    </form>
</div>

<style>
.admin-modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:199;display:none; }
.admin-modal-overlay.ativo { display:block; }
.admin-modal { position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(.95);background:#fff;border-radius:12px;padding:24px;width:95%;max-width:500px;z-index:200;box-shadow:0 8px 32px rgba(0,0,0,.2);display:none;transition:.2s;max-height:90vh;overflow-y:auto; }
.admin-modal.ativo { display:block;transform:translate(-50%,-50%) scale(1); }
.admin-modal-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:16px; }
.admin-modal-header h3 { font-size:1rem;font-weight:800;margin:0; }
.btn-fechar-modal { background:none;border:none;cursor:pointer;font-size:1.1rem;color:#6c757d; }
</style>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.toggle-banner').forEach(cb => {
    cb.addEventListener('change', async function() {
        await ajaxAdmin(`/admin/banners/${this.dataset.id}`, { ativo: this.checked }, 'PUT');
        mostrarToast('Banner atualizado', 'sucesso');
    });
});

function editarBanner(id, titulo, url, ordem) {
    document.getElementById('bannerId').value     = id;
    document.getElementById('bannerTitulo').value = titulo;
    document.getElementById('bannerLink').value   = url;
    document.getElementById('bannerOrdem').value  = ordem;
    document.getElementById('modalBannerTitulo').textContent = 'Editar Banner';
    document.getElementById('modalNovoBanner').classList.add('ativo');
    document.getElementById('modalOverlayBanner').classList.add('ativo');
}

function fecharModalBanner() {
    document.getElementById('modalNovoBanner').classList.remove('ativo');
    document.getElementById('modalOverlayBanner').classList.remove('ativo');
    document.getElementById('formBanner').reset();
    document.getElementById('bannerId').value = '';
    document.getElementById('previewBanner').innerHTML = '';
    document.getElementById('modalBannerTitulo').textContent = 'Novo Banner';
}

document.getElementById('formBanner').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('bannerId').value;
    const url = id ? `/admin/banners/${id}` : '/admin/banners';
    const btn = document.getElementById('btnSalvarBanner');
    btn.disabled = true; btn.textContent = 'Salvando...';
    const fd = new FormData(this);
    if (id) fd.append('_method', 'PUT');
    try {
        const res  = await fetch(url, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        if (data.sucesso) { mostrarToast('Banner salvo!', 'sucesso'); fecharModalBanner(); setTimeout(() => location.reload(), 700); }
        else mostrarToast(data.erro || 'Erro ao salvar.', 'erro');
    } catch { mostrarToast('Erro.', 'erro'); }
    finally { btn.disabled = false; btn.textContent = 'Salvar Banner'; }
});

inicializarUploadAdmin('#uploadAreaBanner', '{{ route('admin.banners.upload') }}', 'imagem_path', resp => {
    document.getElementById('previewBanner').innerHTML =
        `<div class="upload-preview-item"><img src="${resp.url}" style="width:100%;height:60px;object-fit:cover"><input type="hidden" name="imagem_path" value="${resp.caminho}"></div>`;
});
</script>
@endpush
