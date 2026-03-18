@extends('layouts.pwa')
@section('titulo', 'Criar Conta')

@section('conteudo')
<div class="auth-container">
    <div class="auth-logo">
        <i class="bi bi-person-plus auth-logo-icon"></i>
        <h1 class="auth-titulo">Criar Conta</h1>
        <p class="auth-subtitulo">Cadastre-se e peça agora</p>
    </div>

    <form class="auth-form" id="formRegistro">
        @csrf
        <div class="campo-grupo">
            <label class="campo-label">Nome completo *</label>
            <div class="campo-icone">
                <i class="bi bi-person"></i>
                <input type="text" name="nome" class="campo-input" placeholder="Seu nome completo" required autocomplete="name">
            </div>
        </div>
        <div class="campo-grupo">
            <label class="campo-label">E-mail *</label>
            <div class="campo-icone">
                <i class="bi bi-envelope"></i>
                <input type="email" name="email" class="campo-input" placeholder="seu@email.com" required autocomplete="email">
            </div>
        </div>
        <div class="campo-grupo">
            <label class="campo-label">WhatsApp *</label>
            <div class="campo-icone">
                <i class="bi bi-whatsapp"></i>
                <input type="tel" name="telefone" class="campo-input mascara-telefone" placeholder="(00) 00000-0000" required autocomplete="tel">
            </div>
        </div>
        <div class="campo-grupo">
            <label class="campo-label">CPF</label>
            <div class="campo-icone">
                <i class="bi bi-card-text"></i>
                <input type="text" name="cpf" class="campo-input mascara-cpf" placeholder="000.000.000-00" autocomplete="off">
            </div>
        </div>
        <div class="campo-grupo">
            <label class="campo-label">Senha *</label>
            <div class="campo-icone">
                <i class="bi bi-lock"></i>
                <input type="password" name="senha" id="inputSenha" class="campo-input" placeholder="Mínimo 6 caracteres" required>
                <button type="button" class="campo-ver-senha" onclick="toggleSenha('inputSenha', this)"><i class="bi bi-eye"></i></button>
            </div>
        </div>
        <div class="campo-grupo">
            <label class="campo-label">Confirmar Senha *</label>
            <div class="campo-icone">
                <i class="bi bi-lock-fill"></i>
                <input type="password" name="senha_confirmation" id="inputSenhaConf" class="campo-input" placeholder="Repita a senha" required>
                <button type="button" class="campo-ver-senha" onclick="toggleSenha('inputSenhaConf', this)"><i class="bi bi-eye"></i></button>
            </div>
        </div>

        <div id="erroRegistro" class="alerta alerta-erro" style="display:none"></div>

        <button type="submit" class="btn-auth" id="btnRegistro">
            <span id="btnRegistroTxt">Criar Conta</span>
            <div class="spinner-btn" id="spinnerRegistro" style="display:none"></div>
        </button>
    </form>

    <p class="auth-cadastro-link">Já tem conta? <a href="{{ route('login') }}">Entrar</a></p>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('formRegistro').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnRegistro');
    const spinner = document.getElementById('spinnerRegistro');
    const txtBtn = document.getElementById('btnRegistroTxt');
    const erro = document.getElementById('erroRegistro');
    btn.disabled = true; spinner.style.display = ''; txtBtn.style.display = 'none'; erro.style.display = 'none';

    const formData = new FormData(this);
    try {
        const res  = await fetch('/registro', { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        if (data.sucesso) {
            window.location.href = data.redirect || '/';
        } else {
            const erros = data.errors ? Object.values(data.errors).flat().join('<br>') : 'Erro ao cadastrar.';
            erro.innerHTML = erros;
            erro.style.display = '';
        }
    } catch {
        erro.textContent = 'Erro de conexão. Tente novamente.';
        erro.style.display = '';
    } finally {
        btn.disabled = false; spinner.style.display = 'none'; txtBtn.style.display = '';
    }
});
</script>
@endpush
