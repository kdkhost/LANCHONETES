@extends('layouts.pwa')
@section('titulo', 'Redefinir Senha')

@section('conteudo')
<div class="auth-container">
    <div class="auth-logo">
        <i class="bi bi-shield-lock-fill auth-logo-icon" style="color:var(--cor-primaria)"></i>
        <h1 class="auth-titulo">Redefinir Senha</h1>
        <p class="auth-subtitulo">Crie uma nova senha segura para sua conta</p>
    </div>

    <form class="auth-form" id="formRedefinir">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div class="campo-grupo">
            <label class="campo-label">Nova Senha *</label>
            <div class="campo-icone">
                <i class="bi bi-lock-fill"></i>
                <input type="password" name="senha" id="novaSenha" class="campo-input"
                    placeholder="Mínimo 6 caracteres" required minlength="6">
                <button type="button" class="campo-ver-senha" onclick="toggleSenha('novaSenha',this)">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <div class="campo-grupo">
            <label class="campo-label">Confirmar Nova Senha *</label>
            <div class="campo-icone">
                <i class="bi bi-lock"></i>
                <input type="password" name="senha_confirmation" id="confirmaSenha" class="campo-input"
                    placeholder="Repita a nova senha" required>
                <button type="button" class="campo-ver-senha" onclick="toggleSenha('confirmaSenha',this)">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <div id="erroRedefinir" class="alerta alerta-erro" style="display:none"></div>
        <div id="sucessoRedefinir" class="alerta alerta-sucesso" style="display:none"></div>

        <button type="submit" class="btn-auth" id="btnRedefinir">
            <span id="btnRedefinirTxt">Redefinir Senha</span>
            <div class="spinner-btn" id="spinnerRedefinir" style="display:none"></div>
        </button>
    </form>

    <p class="auth-cadastro-link"><a href="{{ route('login') }}">← Voltar ao login</a></p>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('formRedefinir').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn  = document.getElementById('btnRedefinir');
    const spin = document.getElementById('spinnerRedefinir');
    const txt  = document.getElementById('btnRedefinirTxt');
    const erro = document.getElementById('erroRedefinir');
    const ok   = document.getElementById('sucessoRedefinir');

    const senha  = document.getElementById('novaSenha').value;
    const conf   = document.getElementById('confirmaSenha').value;
    if (senha !== conf) {
        erro.textContent = 'As senhas não conferem.';
        erro.style.display = '';
        return;
    }

    btn.disabled = true; spin.style.display = ''; txt.style.display = 'none';
    erro.style.display = 'none'; ok.style.display = 'none';

    const fd = new FormData(this);
    try {
        const res  = await fetch('/redefinir-senha', {
            method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data.sucesso) {
            ok.textContent = data.mensagem || 'Senha redefinida! Redirecionando...';
            ok.style.display = '';
            setTimeout(() => window.location.href = data.redirect || '/', 1500);
        } else {
            const erros = data.errors ? Object.values(data.errors).flat().join(' ') : (data.erro || 'Erro ao redefinir.');
            erro.textContent = erros; erro.style.display = '';
        }
    } catch { erro.textContent = 'Erro de conexão.'; erro.style.display = ''; }
    finally { btn.disabled = false; spin.style.display = 'none'; txt.style.display = ''; }
});
</script>
@endpush
