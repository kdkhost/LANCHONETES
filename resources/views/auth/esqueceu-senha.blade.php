@extends('layouts.pwa')
@section('titulo', 'Recuperar Senha')

@section('conteudo')
<div class="auth-container">
    <div class="auth-logo">
        <i class="bi bi-lock-fill auth-logo-icon" style="color:var(--cor-primaria)"></i>
        <h1 class="auth-titulo">Recuperar Senha</h1>
        <p class="auth-subtitulo">Informe seu e-mail para receber o link de redefinição</p>
    </div>

    @if(session('status'))
    <div class="alerta alerta-sucesso mb-3"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <form class="auth-form" id="formEsqueceu">
        @csrf
        <div class="campo-grupo">
            <label class="campo-label">E-mail cadastrado</label>
            <div class="campo-icone">
                <i class="bi bi-envelope"></i>
                <input type="email" name="email" class="campo-input" placeholder="seu@email.com"
                    value="{{ old('email') }}" required autocomplete="email">
            </div>
            @error('email')<span class="campo-erro">{{ $message }}</span>@enderror
        </div>

        <div id="erroEsqueceu" class="alerta alerta-erro" style="display:none"></div>
        <div id="sucessoEsqueceu" class="alerta alerta-sucesso" style="display:none"></div>

        <button type="submit" class="btn-auth" id="btnEsqueceu">
            <span id="btnEsqueceuTxt">Enviar Link</span>
            <div class="spinner-btn" id="spinnerEsqueceu" style="display:none"></div>
        </button>
    </form>

    <p class="auth-cadastro-link"><a href="{{ route('login') }}">← Voltar ao login</a></p>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('formEsqueceu').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnEsqueceu');
    const spin = document.getElementById('spinnerEsqueceu');
    const txt  = document.getElementById('btnEsqueceuTxt');
    const erro = document.getElementById('erroEsqueceu');
    const ok   = document.getElementById('sucessoEsqueceu');
    btn.disabled = true; spin.style.display = ''; txt.style.display = 'none';
    erro.style.display = 'none'; ok.style.display = 'none';

    const fd = new FormData(this);
    try {
        const res  = await fetch('/esqueceu-senha', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        if (data.sucesso) { ok.textContent = data.mensagem || 'Link enviado! Verifique seu e-mail.'; ok.style.display = ''; }
        else { erro.textContent = data.errors?.email?.[0] || 'Erro ao enviar.'; erro.style.display = ''; }
    } catch { erro.textContent = 'Erro de conexão.'; erro.style.display = ''; }
    finally { btn.disabled = false; spin.style.display = 'none'; txt.style.display = ''; }
});
</script>
@endpush
