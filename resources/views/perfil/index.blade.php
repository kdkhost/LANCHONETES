@extends('layouts.pwa')
@section('titulo', 'Meu Perfil')

@section('conteudo')
<div class="page-header">
    <button onclick="history.back()" class="btn-voltar"><i class="bi bi-arrow-left"></i></button>
    <h1 class="page-title">Meu Perfil</h1>
</div>

<div class="perfil-container">

    {{-- Avatar --}}
    <div class="perfil-avatar-section">
        <div class="perfil-avatar-wrapper" onclick="document.getElementById('inputFotoPerfil').click()">
            <img src="{{ Auth::user()->foto_perfil_url }}" alt="{{ Auth::user()->nome }}" class="perfil-avatar-img" id="avatarPreview">
            <div class="perfil-avatar-overlay"><i class="bi bi-camera"></i></div>
        </div>
        <input type="file" id="inputFotoPerfil" accept="image/*" capture="user" style="display:none">
        <p class="perfil-nome">{{ Auth::user()->nome }}</p>
        <p class="perfil-email">{{ Auth::user()->email }}</p>
    </div>

    {{-- Dados Pessoais --}}
    <form id="formPerfil" class="perfil-form">
        @csrf
        @method('PUT')
        <div class="perfil-secao">
            <h3 class="perfil-secao-titulo"><i class="bi bi-person"></i> Dados Pessoais</h3>
            <div class="campo-grupo">
                <label class="campo-label">Nome completo</label>
                <div class="campo-icone">
                    <i class="bi bi-person"></i>
                    <input type="text" name="nome" class="campo-input" value="{{ Auth::user()->nome }}" required>
                </div>
            </div>
            <div class="campo-grupo">
                <label class="campo-label">E-mail</label>
                <div class="campo-icone">
                    <i class="bi bi-envelope"></i>
                    <input type="email" name="email" class="campo-input" value="{{ Auth::user()->email }}" required>
                </div>
            </div>
            <div class="campo-grupo">
                <label class="campo-label">WhatsApp</label>
                <div class="campo-icone">
                    <i class="bi bi-whatsapp"></i>
                    <input type="tel" name="telefone" class="campo-input mascara-telefone" value="{{ Auth::user()->telefone }}">
                </div>
            </div>
            <div class="campo-grupo">
                <label class="campo-label">CPF</label>
                <div class="campo-icone">
                    <i class="bi bi-card-text"></i>
                    <input type="text" name="cpf" class="campo-input mascara-cpf" value="{{ Auth::user()->cpf }}">
                </div>
            </div>
        </div>

        <div class="perfil-secao">
            <h3 class="perfil-secao-titulo"><i class="bi bi-lock"></i> Alterar Senha</h3>
            <div class="campo-grupo">
                <label class="campo-label">Senha Atual</label>
                <div class="campo-icone">
                    <i class="bi bi-lock"></i>
                    <input type="password" name="senha_atual" id="senhaAtual" class="campo-input" placeholder="Deixe em branco para não alterar">
                    <button type="button" class="campo-ver-senha" onclick="toggleSenha('senhaAtual',this)"><i class="bi bi-eye"></i></button>
                </div>
            </div>
            <div class="campo-grupo">
                <label class="campo-label">Nova Senha</label>
                <div class="campo-icone">
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" name="nova_senha" id="novaSenha" class="campo-input" placeholder="Mínimo 6 caracteres">
                    <button type="button" class="campo-ver-senha" onclick="toggleSenha('novaSenha',this)"><i class="bi bi-eye"></i></button>
                </div>
            </div>
        </div>

        <div id="erroPerfil" class="alerta alerta-erro" style="display:none"></div>
        <div id="sucessoPerfil" class="alerta alerta-sucesso" style="display:none"></div>

        <button type="submit" class="btn-auth" id="btnSalvarPerfil">
            <span id="btnSalvarTxt">Salvar Alterações</span>
            <div class="spinner-btn" id="spinnerPerfil" style="display:none"></div>
        </button>
    </form>

    {{-- Endereços --}}
    <div class="perfil-secao" style="margin-top:8px">
        <div class="perfil-secao-header">
            <h3 class="perfil-secao-titulo"><i class="bi bi-geo-alt"></i> Meus Endereços</h3>
            <button onclick="abrirFormEndereco()" class="btn btn-sm btn-primario"><i class="bi bi-plus"></i> Adicionar</button>
        </div>
        <div id="listaEnderecos">
            @forelse($enderecos as $end)
            <div class="endereco-item" id="end-{{ $end->id }}">
                <div class="endereco-item-info">
                    <strong>{{ $end->apelido ?? 'Endereço' }}</strong>
                    <span>{{ $end->logradouro }}, {{ $end->numero }}{{ $end->complemento ? ' — '.$end->complemento : '' }}</span>
                    <span>{{ $end->bairro }}, {{ $end->cidade }}/{{ $end->estado }} — CEP: {{ $end->cep }}</span>
                </div>
                <div class="endereco-item-acoes">
                    <button onclick="definirEnderecoPrincipal({{ $end->id }})" class="{{ $end->principal ? 'btn-estrela-ativo' : 'btn-estrela' }}" title="Principal">
                        <i class="bi bi-{{ $end->principal ? 'star-fill' : 'star' }}"></i>
                    </button>
                    <button onclick="removerEndereco({{ $end->id }})" class="btn-remover-end" title="Remover">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </div>
            @empty
            <p class="text-muted small text-center py-3">Nenhum endereço cadastrado ainda.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Modal Novo Endereço --}}
<div class="modal-overlay" id="modalEnderecoOverlay" onclick="fecharFormEndereco()"></div>
<div class="modal-bottom" id="modalEndereco">
    <div class="modal-bottom-handle"></div>
    <div style="padding:16px">
        <h3 style="font-size:1rem;font-weight:800;margin:0 0 14px"><i class="bi bi-geo-alt-fill text-primaria"></i> Novo Endereço</h3>
        <form id="formNovoEndereco">
            <div class="campo-grupo">
                <label class="campo-label">Apelido</label>
                <input type="text" id="endApelido" name="apelido" class="campo-input" placeholder="Ex: Casa, Trabalho">
            </div>
            <div class="campo-grupo">
                <label class="campo-label">CEP *</label>
                <div class="input-cep-grupo">
                    <input type="text" id="endCep" name="cep" class="campo-input mascara-cep" placeholder="00000-000" required>
                    <button type="button" class="btn-buscar-cep" onclick="buscarCepEndereco()"><i class="bi bi-search"></i></button>
                </div>
            </div>
            <div id="camposEndModal" style="display:none">
                <div class="campo-grupo"><input type="text" id="endLogradouro" name="logradouro" class="campo-input" placeholder="Logradouro *" required></div>
                <div class="campo-row">
                    <div class="campo-grupo"><input type="text" id="endNumero" name="numero" class="campo-input" placeholder="Número *" required></div>
                    <div class="campo-grupo"><input type="text" id="endComplemento" name="complemento" class="campo-input" placeholder="Complemento"></div>
                </div>
                <div class="campo-grupo"><input type="text" id="endBairro" name="bairro" class="campo-input" placeholder="Bairro *" required></div>
                <div class="campo-row">
                    <div class="campo-grupo"><input type="text" id="endCidade" name="cidade" class="campo-input" placeholder="Cidade *" required></div>
                    <div class="campo-grupo campo-grupo-sm"><input type="text" id="endEstado" name="estado" class="campo-input" placeholder="UF" maxlength="2" required></div>
                </div>
                <input type="hidden" id="endLat" name="latitude">
                <input type="hidden" id="endLng" name="longitude">
            </div>
            <button type="submit" class="btn-auth" id="btnSalvarEnd">
                <span>Salvar Endereço</span>
                <div class="spinner-btn" id="spinnerEnd" style="display:none"></div>
            </button>
        </form>
    </div>
</div>

<style>
.perfil-container { padding: 0 0 20px; }
.perfil-avatar-section { text-align: center; padding: 24px 16px 16px; background: var(--cor-card); }
.perfil-avatar-wrapper { position: relative; display: inline-block; margin-bottom: 12px; cursor: pointer; }
.perfil-avatar-img { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid var(--cor-primaria); }
.perfil-avatar-overlay {
    position: absolute; inset: 0; border-radius: 50%; background: rgba(0,0,0,.4);
    display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.3rem; opacity: 0;
    transition: opacity .2s;
}
.perfil-avatar-wrapper:hover .perfil-avatar-overlay { opacity: 1; }
.perfil-nome  { font-size: 1.1rem; font-weight: 800; margin: 0 0 4px; }
.perfil-email { font-size: 0.82rem; color: var(--cor-texto-muted); margin: 0; }
.perfil-form  { padding: 0 12px; }
.perfil-secao { background: var(--cor-card); border-radius: 14px; padding: 16px; margin-bottom: 10px; box-shadow: var(--sombra-sm); }
.perfil-secao-titulo { font-size: 0.9rem; font-weight: 800; margin: 0 0 12px; display: flex; align-items: center; gap: 6px; }
.perfil-secao-titulo i { color: var(--cor-primaria); }
.perfil-secao-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.perfil-secao-header .perfil-secao-titulo { margin: 0; }
.endereco-item { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; padding: 10px 0; border-bottom: 1px solid var(--cor-borda); }
.endereco-item:last-child { border: none; }
.endereco-item-info strong { display: block; font-size: 0.9rem; font-weight: 700; }
.endereco-item-info span  { display: block; font-size: 0.78rem; color: var(--cor-texto-muted); }
.endereco-item-acoes { display: flex; gap: 4px; flex-shrink: 0; }
.btn-estrela, .btn-estrela-ativo, .btn-remover-end {
    width: 32px; height: 32px; border: none; border-radius: 8px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; font-size: 0.95rem; transition: all .15s;
}
.btn-estrela      { background: var(--cor-fundo); color: var(--cor-texto-muted); }
.btn-estrela-ativo{ background: #fff3cd; color: #ffc107; }
.btn-remover-end  { background: #f8d7da; color: var(--cor-erro); }
.text-primaria { color: var(--cor-primaria); }
</style>
@endsection

@push('scripts')
<script>
// Upload de foto
document.getElementById('inputFotoPerfil').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const preview = document.getElementById('avatarPreview');
    preview.src = URL.createObjectURL(file);

    const fd = new FormData();
    fd.append('foto', file);
    fetch('/perfil/foto', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest' }, body: fd })
        .then(r => r.json())
        .then(data => { if (data.sucesso) mostrarToast('Foto atualizada!', 'sucesso'); else mostrarToast(data.erro, 'erro'); })
        .catch(() => mostrarToast('Erro ao enviar foto.', 'erro'));
});

// Salvar perfil
document.getElementById('formPerfil').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSalvarPerfil');
    const spin = document.getElementById('spinnerPerfil');
    const txt  = document.getElementById('btnSalvarTxt');
    const erro = document.getElementById('erroPerfil');
    const ok   = document.getElementById('sucessoPerfil');
    btn.disabled = true; spin.style.display = ''; txt.style.display = 'none'; erro.style.display = 'none'; ok.style.display = 'none';

    const fd = new FormData(this);
    try {
        const res  = await fetch('/perfil', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        if (data.sucesso) { ok.textContent = 'Perfil atualizado!'; ok.style.display = ''; }
        else {
            const erros = data.errors ? Object.values(data.errors).flat().join('<br>') : 'Erro ao salvar.';
            erro.innerHTML = erros; erro.style.display = '';
        }
    } catch { erro.textContent = 'Erro de conexão.'; erro.style.display = ''; }
    finally { btn.disabled = false; spin.style.display = 'none'; txt.style.display = ''; }
});

// Endereços
function abrirFormEndereco() {
    document.getElementById('modalEnderecoOverlay').classList.add('ativo');
    document.getElementById('modalEndereco').classList.add('ativo');
}
function fecharFormEndereco() {
    document.getElementById('modalEnderecoOverlay').classList.remove('ativo');
    document.getElementById('modalEndereco').classList.remove('ativo');
}

async function buscarCepEndereco() {
    const cep = document.getElementById('endCep').value.replace(/\D/g, '');
    if (cep.length !== 8) return;
    const data = await fetch(`/api/cep/${cep}`).then(r => r.json());
    if (data.sucesso) {
        document.getElementById('endLogradouro').value = data.logradouro || '';
        document.getElementById('endBairro').value     = data.bairro || '';
        document.getElementById('endCidade').value     = data.cidade || '';
        document.getElementById('endEstado').value     = data.estado || '';
        if (data.latitude)  document.getElementById('endLat').value = data.latitude;
        if (data.longitude) document.getElementById('endLng').value = data.longitude;
        document.getElementById('camposEndModal').style.display = '';
        document.getElementById('endNumero').focus();
    } else { mostrarToast('CEP não encontrado', 'erro'); }
}

document.getElementById('formNovoEndereco').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSalvarEnd');
    btn.disabled = true;
    const fd = new FormData(this);
    const res  = await fetch('/perfil/enderecos', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const data = await res.json();
    btn.disabled = false;
    if (data.sucesso) { mostrarToast('Endereço salvo!', 'sucesso'); fecharFormEndereco(); setTimeout(() => location.reload(), 700); }
    else mostrarToast(data.erro || 'Erro ao salvar.', 'erro');
});

async function removerEndereco(id) {
    if (!confirm('Remover este endereço?')) return;
    const res  = await fetch(`/perfil/enderecos/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest' } });
    const data = await res.json();
    if (data.sucesso) { document.getElementById('end-' + id)?.remove(); mostrarToast('Endereço removido.', 'info'); }
    else mostrarToast('Erro ao remover.', 'erro');
}

async function definirEnderecoPrincipal(id) {
    const res  = await fetch(`/perfil/enderecos/${id}/principal`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest' } });
    const data = await res.json();
    if (data.sucesso) { mostrarToast('Endereço principal definido.', 'sucesso'); setTimeout(() => location.reload(), 500); }
}
</script>
@endpush
