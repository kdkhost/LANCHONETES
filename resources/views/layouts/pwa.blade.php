<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="{{ $lojaAtual?->cor_primaria ?? '#FF6B35' }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $lojaAtual?->nome ?? config('app.name') }}">
    <meta name="description" content="{{ $lojaAtual?->descricao ?? 'Peça sua comida favorita com facilidade' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ config('app.url') }}">

    <title>@yield('titulo', $lojaAtual?->nome ?? config('app.name'))</title>

    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    <!-- Ícones -->
    <link rel="icon" type="image/png" href="/img/icones/icon-192x192.png">
    <link rel="apple-touch-icon" href="/img/icones/icon-192x192.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- CSS do sistema -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- Variáveis de cor da loja -->
    <style>
        :root {
            --cor-primaria: {{ $lojaAtual?->cor_primaria ?? '#FF6B35' }};
            --cor-secundaria: {{ $lojaAtual?->cor_secundaria ?? '#2C3E50' }};
        }
    </style>

    @stack('styles')
</head>
<body class="pwa-body" data-loja-id="{{ $lojaAtual?->id ?? '' }}" data-loja-slug="{{ $lojaAtual?->slug ?? '' }}">

    <!-- Navegação Desktop -->
    <header class="site-navbar desktop-only">
        <div class="site-navbar__brand">
            <a href="{{ url('/') }}" class="site-navbar__logo">
                @if(isset($lojaAtual) && $lojaAtual->logo)
                    <img src="{{ $lojaAtual->logo_url }}" alt="{{ $lojaAtual->nome }}">
                @else
                    <span>{{ $lojaAtual->nome ?? config('app.name') }}</span>
                @endif
            </a>
            <div class="site-navbar__info">
                <strong>{{ $lojaAtual->nome ?? config('app.name') }}</strong>
                <small>{{ $lojaAtual->descricao ?? 'Experiência completa de delivery online' }}</small>
            </div>
        </div>

        <nav class="site-navbar__links">
            <a href="{{ isset($lojaAtual) ? route('cliente.loja', $lojaAtual->slug) : url('/') }}" class="{{ request()->routeIs('cliente.loja') ? 'active' : '' }}">Início</a>
            <a href="{{ route('cliente.lojas') }}" class="{{ request()->routeIs('cliente.lojas') ? 'active' : '' }}">Restaurantes</a>
            <a href="{{ route('cliente.buscar') }}" class="{{ request()->routeIs('cliente.buscar') ? 'active' : '' }}">Buscar</a>
            <a href="{{ route('marketing.landing') }}" target="_blank" rel="noopener">Apresentação</a>
            @auth
            <a href="{{ route('cliente.pedidos.index') }}">Pedidos</a>
            @endauth
        </nav>

        <div class="site-navbar__actions">
            @auth
                <div class="site-navbar__user">
                    <img src="{{ Auth::user()->foto_perfil_url }}" alt="{{ Auth::user()->nome }}">
                    <div>
                        <span>{{ Auth::user()->nome_abreviado }}</span>
                        <small>{{ Auth::user()->email }}</small>
                    </div>
                </div>
                <a href="{{ route('cliente.checkout') }}" class="btn btn-primario">Meu carrinho</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline">Sair</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline">Entrar</a>
                <a href="{{ route('registro') }}" class="btn btn-primario">Criar conta</a>
            @endauth
        </div>
    </header>

    <!-- Overlay do Sidebar -->
    <div class="sidebar-overlay mobile-only" id="sidebarOverlay" onclick="fecharSidebar()"></div>

    <!-- Sidebar Esquerda -->
    <aside class="sidebar sidebar-left mobile-only" id="sidebarLeft">
        <div class="sidebar-header">
            <div class="sidebar-user">
                @auth
                <img src="{{ Auth::user()->foto_perfil_url }}" alt="{{ Auth::user()->nome }}" class="sidebar-avatar">
                <div>
                    <div class="sidebar-user-nome">{{ Auth::user()->nome_abreviado }}</div>
                    <div class="sidebar-user-email">{{ Auth::user()->email }}</div>
                </div>
                @else
                <div class="sidebar-guest">
                    <i class="bi bi-person-circle fs-2"></i>
                    <div>
                        <a href="{{ route('login') }}" class="btn btn-sm btn-primario">Entrar</a>
                        <a href="{{ route('registro') }}" class="btn btn-sm btn-outline ms-1">Cadastrar</a>
                    </div>
                </div>
                @endauth
            </div>
            <button class="sidebar-close" onclick="fecharSidebar()"><i class="bi bi-x-lg"></i></button>
        </div>

        <nav class="sidebar-nav">
            @if(isset($lojaAtual))
            <a href="{{ route('cliente.loja', $lojaAtual->slug) }}" class="sidebar-link">
                <i class="bi bi-house"></i> Início
            </a>
            @endif
            <a href="{{ route('cliente.lojas') }}" class="sidebar-link">
                <i class="bi bi-shop"></i> Restaurantes
            </a>
            <a href="{{ route('marketing.landing') }}" target="_blank" class="sidebar-link">
                <i class="bi bi-megaphone"></i> Apresentação
            </a>
            @auth
            <a href="{{ route('cliente.pedidos.index') }}" class="sidebar-link">
                <i class="bi bi-bag"></i> Meus Pedidos
            </a>
            <a href="{{ route('perfil.index') }}" class="sidebar-link">
                <i class="bi bi-person"></i> Meu Perfil
            </a>
            <a href="{{ route('notificacoes.index') }}" class="sidebar-link">
                <i class="bi bi-bell"></i> Notificações
                @php $naoLidas = Auth::user()->notificacoesNaoLidas()->count(); @endphp
                @if($naoLidas > 0)
                <span class="badge-notif">{{ $naoLidas }}</span>
                @endif
            </a>
            @if(Auth::user()->isAdmin() || Auth::user()->isGerente())
            <div class="sidebar-divider"></div>
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link">
                <i class="bi bi-speedometer2"></i> Painel Admin
            </a>
            @endif
            @if(Auth::user()->isEntregador())
            <div class="sidebar-divider"></div>
            <a href="{{ route('entregador.dashboard') }}" class="sidebar-link">
                <i class="bi bi-bicycle"></i> Painel Entregador
            </a>
            @endif
            <div class="sidebar-divider"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-link sidebar-link-danger w-100 text-start border-0 bg-transparent">
                    <i class="bi bi-box-arrow-right"></i> Sair
                </button>
            </form>
            @endauth
        </nav>

        @if(isset($lojaAtual) && $lojaAtual->estaAberta())
        <div class="sidebar-loja-status aberta">
            <i class="bi bi-circle-fill"></i> Aberta agora
        </div>
        @elseif(isset($lojaAtual))
        <div class="sidebar-loja-status fechada">
            <i class="bi bi-circle-fill"></i> Fechada no momento
        </div>
        @endif
    </aside>

    <!-- Header Principal -->
    <header class="pwa-header mobile-only" id="pwaHeader">
        <button class="header-btn" onclick="abrirSidebar()" aria-label="Menu">
            <i class="bi bi-list fs-4"></i>
        </button>

        <div class="header-logo">
            @if(isset($lojaAtual) && $lojaAtual->logo)
                <img src="{{ $lojaAtual->logo_url }}" alt="{{ $lojaAtual->nome }}" class="header-logo-img">
            @else
                <span class="header-logo-text">{{ $lojaAtual->nome ?? config('app.name') }}</span>
            @endif
        </div>

        <div class="header-acoes">
            <a href="{{ route('cliente.buscar') }}" class="header-btn" aria-label="Buscar">
                <i class="bi bi-search fs-5"></i>
            </a>
            @auth
            <button class="header-btn position-relative" onclick="toggleCarrinho()" aria-label="Carrinho">
                <i class="bi bi-bag fs-5"></i>
                <span class="badge-carrinho" id="badgeCarrinho" style="display:none">0</span>
            </button>
            @endauth
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="pwa-main">
        @if(session('sucesso'))
        <div class="alerta alerta-sucesso animate-slide-down" id="alertaSucesso">
            <i class="bi bi-check-circle"></i> {{ session('sucesso') }}
        </div>
        @endif
        @if(session('erro'))
        <div class="alerta alerta-erro animate-slide-down">
            <i class="bi bi-exclamation-circle"></i> {{ session('erro') }}
        </div>
        @endif
        @if(session('info'))
        <div class="alerta alerta-info animate-slide-down">
            <i class="bi bi-info-circle"></i> {{ session('info') }}
        </div>
        @endif

        @yield('conteudo')
    </main>

    <!-- Footer Navigation (Android-like) -->
    <nav class="footer-nav mobile-only" id="footerNav">
        <a href="{{ isset($lojaAtual) ? route('cliente.loja', $lojaAtual->slug) : route('cliente.home') }}"
           class="footer-nav-item {{ request()->routeIs('cliente.loja', 'cliente.home') ? 'active' : '' }}">
            <i class="bi bi-house-fill"></i>
            <span>Início</span>
        </a>
        <a href="{{ route('cliente.buscar') }}"
           class="footer-nav-item {{ request()->routeIs('cliente.buscar') ? 'active' : '' }}">
            <i class="bi bi-search"></i>
            <span>Buscar</span>
        </a>
        @auth
        <a href="{{ route('cliente.checkout') }}"
           class="footer-nav-item footer-nav-carrinho {{ request()->routeIs('cliente.checkout') ? 'active' : '' }}">
            <div class="footer-carrinho-btn">
                <i class="bi bi-bag-fill"></i>
                <span class="footer-carrinho-badge" id="footerCarrinhoBadge" style="display:none">0</span>
            </div>
            <span>Carrinho</span>
        </a>
        <a href="{{ route('cliente.pedidos.index') }}"
           class="footer-nav-item {{ request()->routeIs('cliente.pedidos.*') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i>
            <span>Pedidos</span>
        </a>
        <a href="{{ route('perfil.index') }}"
           class="footer-nav-item {{ request()->routeIs('perfil.*') ? 'active' : '' }}">
            <i class="bi bi-person-circle"></i>
            <span>Perfil</span>
        </a>
        @else
        <a href="{{ route('cliente.lojas') }}"
           class="footer-nav-item {{ request()->routeIs('cliente.lojas') ? 'active' : '' }}">
            <i class="bi bi-shop"></i>
            <span>Lojas</span>
        </a>
        <a href="{{ route('login') }}"
           class="footer-nav-item {{ request()->routeIs('login') ? 'active' : '' }}">
            <i class="bi bi-box-arrow-in-right"></i>
            <span>Entrar</span>
        </a>
        <a href="{{ route('registro') }}"
           class="footer-nav-item {{ request()->routeIs('registro') ? 'active' : '' }}">
            <i class="bi bi-person-plus"></i>
            <span>Cadastrar</span>
        </a>
        @endauth
    </nav>

    <!-- Painel do Carrinho (slide bottom) -->
    @auth
    <div class="carrinho-overlay mobile-only" id="carrinhoOverlay" onclick="fecharCarrinho()"></div>
    <div class="carrinho-panel mobile-only" id="carrinhoPanel">
        <div class="carrinho-header">
            <h6 class="m-0"><i class="bi bi-bag"></i> Meu Carrinho</h6>
            <button onclick="fecharCarrinho()" class="btn-fechar"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="carrinho-itens" id="carrinhoItens">
            <div class="carrinho-vazio" id="carrinhoVazio">
                <i class="bi bi-bag-x"></i>
                <p>Seu carrinho está vazio</p>
            </div>
        </div>
        <div class="carrinho-footer" id="carrinhoFooter" style="display:none">
            <div class="carrinho-total">
                <span>Total</span>
                <strong id="carrinhoTotal">R$ 0,00</strong>
            </div>
            <a href="{{ route('cliente.checkout') }}" class="btn btn-primario w-100 mt-2">
                Finalizar Pedido <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    @endauth

    <!-- Toast de notificação -->
    <div class="toast-container" id="toastContainer"></div>

    <div class="support-fab">
        <a href="https://wa.me/5521981325441" target="_blank" rel="noopener">
            <i class="bi bi-whatsapp"></i>
            <span>Precisa de suporte?</span>
        </a>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         POP-UP DE SAÍDA (Exit Intent) — configurável por lojista
    ═══════════════════════════════════════════════════════════ --}}
    @if(isset($lojaAtual) && $lojaAtual->popup_saida_ativo)
    @php
        $ps = $lojaAtual;
        $cookieSaida = 'popup_saida_' . $lojaAtual->id;
        $tipoDesc = match($ps->popup_saida_desconto_tipo) {
            'percentual'   => number_format($ps->popup_saida_desconto_valor, 0) . '% OFF',
            'fixo'         => 'R$ ' . number_format($ps->popup_saida_desconto_valor, 2, ',', '.') . ' de desconto',
            'frete_gratis' => 'Frete Grátis',
            default        => number_format($ps->popup_saida_desconto_valor, 0) . '% OFF',
        };
    @endphp
    <div id="popupSaidaOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:8000;align-items:center;justify-content:center;padding:16px">
        <div style="background:#fff;border-radius:24px;max-width:400px;width:100%;overflow:hidden;position:relative;box-shadow:0 20px 60px rgba(0,0,0,.3)">
            <button onclick="fecharPopupSaida()" style="position:absolute;top:12px;right:12px;background:rgba(0,0,0,.1);border:none;border-radius:50%;width:32px;height:32px;cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center;z-index:1">✕</button>
            @if($ps->popup_saida_imagem)
            <img src="{{ asset('storage/' . $ps->popup_saida_imagem) }}" alt="" style="width:100%;height:160px;object-fit:cover">
            @else
            <div style="background:linear-gradient(135deg,{{ $lojaAtual->cor_primaria ?? '#FF6B35' }},{{ $lojaAtual->cor_secundaria ?? '#2C3E50' }});height:120px;display:flex;align-items:center;justify-content:center;font-size:3rem">🎁</div>
            @endif
            <div style="padding:24px;text-align:center">
                <h2 style="font-size:1.4rem;font-weight:900;color:#2C3E50;margin:0 0 8px">{{ $ps->popup_saida_titulo ?: 'Espere! Temos um presente para você!' }}</h2>
                <p style="color:#6c757d;margin:0 0 16px;line-height:1.5">{{ $ps->popup_saida_texto ?: 'Não vá embora sem aproveitar este desconto especial!' }}</p>
                <div style="background:{{ $lojaAtual->cor_primaria ?? '#FF6B35' }}15;border:2px dashed {{ $lojaAtual->cor_primaria ?? '#FF6B35' }};border-radius:12px;padding:12px;margin-bottom:16px">
                    <div style="font-size:.8rem;color:#6c757d;margin-bottom:4px">Use o cupom:</div>
                    <div style="font-size:1.5rem;font-weight:900;color:{{ $lojaAtual->cor_primaria ?? '#FF6B35' }};letter-spacing:3px;cursor:pointer" onclick="copiarCupomSaida()" title="Clique para copiar">
                        {{ $ps->popup_saida_cupom ?: 'FIQUE10' }}
                    </div>
                    <div style="font-size:.75rem;color:#6c757d;margin-top:2px">{{ $tipoDesc }}</div>
                </div>
                @if($ps->popup_saida_validade_min)
                <div style="font-size:.8rem;color:#dc3545;margin-bottom:12px">⏱ Oferta válida por <strong id="countdownSaida">{{ $ps->popup_saida_validade_min }}:00</strong></div>
                @endif
                <button onclick="usarCupomSaida()" style="width:100%;padding:14px;background:{{ $lojaAtual->cor_primaria ?? '#FF6B35' }};color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:800;cursor:pointer;font-family:inherit">
                    🎉 Quero este desconto!
                </button>
                <button onclick="fecharPopupSaida()" style="width:100%;margin-top:8px;padding:10px;background:transparent;color:#6c757d;border:none;cursor:pointer;font-family:inherit;font-size:.85rem">
                    Não, obrigado
                </button>
            </div>
        </div>
    </div>
    <script>
    (function(){
        const COOKIE_KEY = '{{ $cookieSaida }}';
        const CUPOM      = '{{ $ps->popup_saida_cupom ?: "FIQUE10" }}';
        const VALIDADE   = {{ $ps->popup_saida_validade_min ?? 30 }};

        if (document.cookie.includes(COOKIE_KEY)) return;

        let mostrou = false;

        function mostrarPopupSaida() {
            if (mostrou) return;
            mostrou = true;
            const el = document.getElementById('popupSaidaOverlay');
            if (el) { el.style.display = 'flex'; iniciarCountdown(); }
        }

        // Exit intent (desktop)
        document.addEventListener('mouseleave', function(e) {
            if (e.clientY < 10) mostrarPopupSaida();
        });

        // Saída mobile (visibilidade)
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'hidden') mostrarPopupSaida();
        });

        let countSeg = VALIDADE * 60;
        let intervalCd;
        function iniciarCountdown() {
            if (!VALIDADE) return;
            intervalCd = setInterval(() => {
                countSeg--;
                const el = document.getElementById('countdownSaida');
                if (!el) { clearInterval(intervalCd); return; }
                if (countSeg <= 0) { clearInterval(intervalCd); window.fecharPopupSaida(); return; }
                const m = Math.floor(countSeg / 60);
                const s = countSeg % 60;
                el.textContent = m + ':' + String(s).padStart(2, '0');
            }, 1000);
        }

        window.fecharPopupSaida = function() {
            const el = document.getElementById('popupSaidaOverlay');
            if (el) el.style.display = 'none';
            clearInterval(intervalCd);
            document.cookie = COOKIE_KEY + '=1;path=/;max-age=' + (VALIDADE * 60);
        };

        window.copiarCupomSaida = function() {
            navigator.clipboard?.writeText(CUPOM).then(() => {
                if (typeof mostrarToast === 'function') mostrarToast('Cupom copiado! ' + CUPOM, 'sucesso');
            });
        };

        window.usarCupomSaida = function() {
            copiarCupomSaida();
            fecharPopupSaida();
            const c = document.getElementById('campoCupom');
            if (c) { c.value = CUPOM; c.dispatchEvent(new Event('input')); }
            else { sessionStorage.setItem('cupom_auto', CUPOM); }
        };
    })();
    </script>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         POP-UP DE PROMOÇÃO / RELÂMPAGO — configurável por lojista
    ═══════════════════════════════════════════════════════════ --}}
    @if(isset($lojaAtual) && $lojaAtual->popup_promo_ativo)
    @php
        $pp = $lojaAtual;
        $promoExpirada = $pp->popup_promo_expira_em && $pp->popup_promo_expira_em->isPast();
        $cookiePromo   = 'popup_promo_' . $lojaAtual->id;
    @endphp
    @if(!$promoExpirada)
    <div id="popupPromoOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:8001;align-items:center;justify-content:center;padding:16px">
        <div style="background:#fff;border-radius:24px;max-width:380px;width:100%;overflow:hidden;position:relative;box-shadow:0 20px 60px rgba(0,0,0,.4)">
            <button onclick="fecharPopupPromo()" style="position:absolute;top:12px;right:12px;background:rgba(0,0,0,.1);border:none;border-radius:50%;width:32px;height:32px;cursor:pointer;font-size:1rem;z-index:1">✕</button>
            @if($pp->popup_promo_imagem)
            <img src="{{ asset('storage/' . $pp->popup_promo_imagem) }}" alt="" style="width:100%;max-height:200px;object-fit:cover">
            @else
            <div style="background:linear-gradient(135deg,#dc3545,#FF6B35);padding:24px;text-align:center;color:#fff">
                <div style="font-size:2.5rem;margin-bottom:4px">⚡</div>
                <div style="font-size:.9rem;font-weight:700;letter-spacing:2px;text-transform:uppercase">Promoção Relâmpago</div>
            </div>
            @endif
            <div style="padding:24px;text-align:center">
                <h2 style="font-size:1.4rem;font-weight:900;color:#2C3E50;margin:0 0 8px">{{ $pp->popup_promo_titulo ?: '⚡ Promoção Relâmpago!' }}</h2>
                <p style="color:#6c757d;margin:0 0 16px;line-height:1.5">{{ $pp->popup_promo_texto ?: 'Aproveite agora! Oferta por tempo limitado.' }}</p>
                @if($pp->popup_promo_expira_em)
                <div style="background:#dc354515;border:1px solid #dc354540;border-radius:10px;padding:10px;margin-bottom:16px">
                    <div style="font-size:.8rem;color:#dc3545;font-weight:700">⏱ Termina em:</div>
                    <div style="font-size:1.8rem;font-weight:900;color:#dc3545;font-family:monospace" id="countdownPromo">--:--:--</div>
                </div>
                @endif
                @if($pp->popup_promo_url)
                <a href="{{ $pp->popup_promo_url }}" onclick="fecharPopupPromo()" style="display:block;width:100%;padding:14px;background:#dc3545;color:#fff;border-radius:12px;font-size:1rem;font-weight:800;text-decoration:none;text-align:center">
                    ⚡ Ver Promoção!
                </a>
                @endif
                <button onclick="fecharPopupPromo()" style="width:100%;margin-top:8px;padding:10px;background:transparent;color:#6c757d;border:none;cursor:pointer;font-family:inherit;font-size:.85rem">
                    Fechar
                </button>
            </div>
        </div>
    </div>
    <script>
    (function(){
        const COOKIE_KEY  = '{{ $cookiePromo }}';
        const DELAY       = {{ $pp->popup_promo_delay_seg ?? 5 }} * 1000;
        const EXPIRA_ISO  = '{{ $pp->popup_promo_expira_em?->toISOString() ?? '' }}';

        if (document.cookie.includes(COOKIE_KEY)) return;

        setTimeout(function() {
            const el = document.getElementById('popupPromoOverlay');
            if (el) { el.style.display = 'flex'; iniciarCountdownPromo(); }
        }, DELAY);

        function iniciarCountdownPromo() {
            if (!EXPIRA_ISO) return;
            const alvo = new Date(EXPIRA_ISO).getTime();
            const el = document.getElementById('countdownPromo');
            if (!el) return;
            const t = setInterval(() => {
                const diff = alvo - Date.now();
                if (diff <= 0) { clearInterval(t); el.textContent = 'ENCERRADA'; return; }
                const h = Math.floor(diff / 3600000);
                const m = Math.floor((diff % 3600000) / 60000);
                const s = Math.floor((diff % 60000) / 1000);
                el.textContent = [h, m, s].map(v => String(v).padStart(2,'0')).join(':');
            }, 1000);
        }

        window.fecharPopupPromo = function() {
            const el = document.getElementById('popupPromoOverlay');
            if (el) el.style.display = 'none';
            document.cookie = COOKIE_KEY + '=1;path=/;max-age=86400';
        };
    })();
    </script>
    @endif
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         BANNER LGPD / COOKIES
    ═══════════════════════════════════════════════════════════ --}}
    @if(isset($lojaAtual))
    @php $cookieLgpd = 'lgpd_aceito_' . ($lojaAtual->id ?? 0); @endphp
    <div id="bannerLgpd" style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:7999;background:#1a1a2e;color:#e6edf3;padding:16px 20px;font-family:'Nunito',sans-serif;border-top:3px solid {{ $lojaAtual->cor_primaria ?? '#FF6B35' }}">
        <div style="max-width:800px;margin:0 auto;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
            <div style="flex:1;min-width:240px">
                <strong style="font-size:.95rem">🍪 Usamos cookies</strong>
                <p style="font-size:.82rem;margin:4px 0 0;color:#8b949e;line-height:1.5">
                    {{ $lojaAtual->lgpd_texto_cookies ?: 'Utilizamos cookies para melhorar sua experiência, personalizar conteúdo e analisar o tráfego. Ao continuar, você concorda com nossa Política de Privacidade.' }}
                    @if($lojaAtual->lgpd_url_politica)
                    <a href="{{ $lojaAtual->lgpd_url_politica }}" target="_blank" style="color:{{ $lojaAtual->cor_primaria ?? '#FF6B35' }}">Política de Privacidade</a>
                    @endif
                    @if($lojaAtual->lgpd_url_termos)
                     · <a href="{{ $lojaAtual->lgpd_url_termos }}" target="_blank" style="color:{{ $lojaAtual->cor_primaria ?? '#FF6B35' }}">Termos de Uso</a>
                    @endif
                </p>
            </div>
            <div style="display:flex;gap:8px;flex-shrink:0">
                <button onclick="recusarCookies()" style="padding:8px 16px;background:transparent;color:#8b949e;border:1px solid #30363d;border-radius:8px;cursor:pointer;font-family:inherit;font-size:.85rem">Recusar</button>
                <button onclick="aceitarCookies()" style="padding:8px 20px;background:{{ $lojaAtual->cor_primaria ?? '#FF6B35' }};color:#fff;border:none;border-radius:8px;cursor:pointer;font-family:inherit;font-size:.85rem;font-weight:700">Aceitar</button>
            </div>
        </div>
    </div>
    <script>
    (function(){
        const COOKIE_KEY = '{{ $cookieLgpd }}';
        const LOJA_ID    = {{ $lojaAtual->id ?? 'null' }};

        if (!document.cookie.includes(COOKIE_KEY)) {
            setTimeout(() => {
                const el = document.getElementById('bannerLgpd');
                if (el) el.style.display = 'block';
            }, 1500);
        }

        window.aceitarCookies = function() {
            document.cookie = COOKIE_KEY + '=1;path=/;max-age=' + (365 * 86400);
            document.getElementById('bannerLgpd').style.display = 'none';
            fetch('/lgpd/aceitar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ tipo: 'cookies', loja_id: LOJA_ID })
            }).catch(() => {});
        };

        window.recusarCookies = function() {
            document.cookie = COOKIE_KEY + '=recusado;path=/;max-age=' + (7 * 86400);
            document.getElementById('bannerLgpd').style.display = 'none';
        };
    })();
    </script>
    @endif

    <!-- Scripts -->
    <script>
        const APP_URL   = '{{ config("app.url") }}';
        const CSRF_TOKEN= '{{ csrf_token() }}';
        const USUARIO_ID= {{ auth()->id() ?? 'null' }};
        const LOJA_ID   = {{ $lojaAtual->id ?? 'null' }};
        const LOJA_SLUG = '{{ $lojaAtual->slug ?? "" }}';
    </script>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')

    <!-- Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .catch(err => console.warn('SW não registrado:', err));
            });
        }
    </script>
</body>
</html>
