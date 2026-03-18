@extends('layouts.pwa')
@section('titulo', 'Entrar')

@section('conteudo')
<div class="auth-container">
    <div class="auth-logo">
        @if(isset($lojaAtual) && $lojaAtual->logo)
            <img src="{{ $lojaAtual->logo_url }}" alt="{{ $lojaAtual->nome }}" class="auth-logo-img">
        @else
            <i class="bi bi-fire auth-logo-icon"></i>
        @endif
        <h1 class="auth-titulo">Bem-vindo(a)!</h1>
        <p class="auth-subtitulo">Entre para fazer seu pedido</p>
    </div>

    <form class="auth-form" id="formLogin">
        @csrf
        <div class="campo-grupo">
            <label class="campo-label">E-mail</label>
            <div class="campo-icone">
                <i class="bi bi-envelope"></i>
                <input type="email" name="email" class="campo-input" placeholder="seu@email.com"
                    value="{{ old('email') }}" autocomplete="email" required>
            </div>
            @error('email')<span class="campo-erro">{{ $message }}</span>@enderror
        </div>

        <div class="campo-grupo">
            <label class="campo-label">Senha</label>
            <div class="campo-icone">
                <i class="bi bi-lock"></i>
                <input type="password" name="senha" id="inputSenha" class="campo-input" placeholder="Sua senha"
                    autocomplete="current-password" required>
                <button type="button" class="campo-ver-senha" onclick="toggleSenha('inputSenha', this)">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            @error('senha')<span class="campo-erro">{{ $message }}</span>@enderror
        </div>

        <div class="auth-opcoes">
            <label class="checkbox-label">
                <input type="checkbox" name="lembrar"> Lembrar-me
            </label>
            <a href="{{ route('auth.esqueceu-senha') }}" class="link-esqueceu">Esqueceu a senha?</a>
        </div>

        <div id="erroLogin" class="alerta alerta-erro" style="display:none"></div>

        <button type="submit" class="btn-auth" id="btnLogin">
            <span id="btnLoginTxt">Entrar</span>
            <div class="spinner-btn" id="spinnerLogin" style="display:none"></div>
        </button>
    </form>

    <div class="auth-divider"><span>ou</span></div>

    <p class="auth-cadastro-link">
        Não tem conta? <a href="{{ route('registro') }}">Cadastre-se grátis</a>
    </p>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('formLogin').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn     = document.getElementById('btnLogin');
    const spinner = document.getElementById('spinnerLogin');
    const txtBtn  = document.getElementById('btnLoginTxt');
    const erro    = document.getElementById('erroLogin');
    btn.disabled  = true; spinner.style.display = ''; txtBtn.style.display = 'none'; erro.style.display = 'none';

    const formData = new FormData(this);
    try {
        const res  = await fetch('/login', { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        if (data.sucesso) {
            window.location.href = data.redirect || '/';
        } else {
            erro.textContent = data.errors?.email?.[0] || 'E-mail ou senha incorretos.';
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
