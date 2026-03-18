<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @php
        $marketingCssPath = public_path('css/marketing.css');
        $marketingCssVersion = file_exists($marketingCssPath) ? filemtime($marketingCssPath) : time();
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema completo de delivery white-label para redes e franquias.">
    <title>@yield('titulo', 'Sistema de Lanchonetes — Plataforma para franquias')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/marketing.css') }}?v={{ $marketingCssVersion }}">
</head>
<body>
    <header class="mk-header">
        <div class="mk-container mk-header__inner">
            <a href="{{ url('/') }}" class="mk-logo">
                <span>🍔 Sistema Lanchonete</span>
            </a>
            <nav class="mk-nav">
                <a href="#beneficios">Benefícios</a>
                <a href="#como-funciona">Como funciona</a>
                <a href="#planos">Planos</a>
                <a href="#cases">Cases</a>
                <a href="#faq">FAQ</a>
                <a href="#contato" class="mk-btn mk-btn--ghost">Contato</a>
            </nav>
            <div class="mk-nav__cta">
                <a href="https://wa.me/5521981325441" class="mk-btn mk-btn--outline" target="_blank" rel="noopener">Falar no WhatsApp</a>
                <a href="{{ url('/') }}" class="mk-btn">Acessar sistema</a>
            </div>
        </div>
    </header>

    <main>
        @yield('conteudo')
    </main>

    <footer class="mk-footer">
        <div class="mk-container mk-footer__grid">
            <div>
                <h4>🍔 Sistema Lanchonete</h4>
                <p>Plataforma white-label para delivery, franquias e redes omnichannel.</p>
            </div>
            <div>
                <h5>Contato</h5>
                <p><i class="bi bi-telephone"></i> (21) 98132-5441</p>
                <p><i class="bi bi-envelope"></i> contato@kdkhost.com.br</p>
            </div>
            <div>
                <h5>Links úteis</h5>
                <a href="{{ route('marketing.landing') }}">Apresentação</a>
                <a href="{{ route('cliente.lojas') }}">Lojas demo</a>
                <a href="{{ route('cliente.home') }}">Cardápio PWA</a>
            </div>
        </div>
        <div class="mk-container mk-footer__bottom">
            <span>&copy; {{ date('Y') }} Sistema Lanchonete — Todos os direitos reservados.</span>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
