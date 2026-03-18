<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Painel Admin') — {{ config('app.name') }}</title>
    <link rel="icon" href="/img/icones/icon-192x192.png">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
</head>
<body class="admin-body">
<div class="admin-layout" id="adminLayout">

    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-brand">
            <i class="bi bi-fire text-warning"></i>
            <span>{{ config('app.name') }}</span>
            <button class="admin-sidebar-toggle d-md-none" onclick="toggleAdminSidebar()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="admin-sidebar-user">
            <img src="{{ Auth::user()->foto_perfil_url }}" alt="" class="admin-avatar">
            <div>
                <div class="admin-user-nome">{{ Auth::user()->nome_abreviado }}</div>
                <div class="admin-user-role">{{ ucfirst(Auth::user()->role) }}</div>
            </div>
        </div>

        <nav class="admin-nav">
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <div class="admin-nav-group">Pedidos</div>
            <a href="{{ route('admin.pedidos.index') }}" class="admin-nav-link {{ request()->routeIs('admin.pedidos.index') ? 'active' : '' }}">
                <i class="bi bi-bag"></i> Lista de Pedidos
            </a>
            @if(isset($lojaAtual) && $lojaAtual?->cozinha_ativo)
            <a href="{{ route('admin.cozinha') }}" class="admin-nav-link {{ request()->routeIs('admin.cozinha') ? 'active' : '' }}" target="_blank">
                <i class="bi bi-egg-fried"></i> Tela da Cozinha
            </a>
            @endif
            <a href="{{ route('admin.pedidos.kanban') }}" class="admin-nav-link {{ request()->routeIs('admin.pedidos.kanban') ? 'active' : '' }}">
                <i class="bi bi-kanban"></i> Kanban
            </a>
            <div class="admin-nav-group">Cardápio</div>
            <a href="{{ route('admin.categorias.index') }}" class="admin-nav-link {{ request()->routeIs('admin.categorias.*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i> Categorias
            </a>
            <a href="{{ route('admin.produtos.index') }}" class="admin-nav-link {{ request()->routeIs('admin.produtos.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> Produtos
            </a>
            <div class="admin-nav-group">Operação</div>
            <a href="{{ route('admin.funcionarios.index') }}" class="admin-nav-link {{ request()->routeIs('admin.funcionarios.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Funcionários
            </a>
            <a href="{{ route('admin.cupons.index') }}" class="admin-nav-link {{ request()->routeIs('admin.cupons.*') ? 'active' : '' }}">
                <i class="bi bi-ticket-perforated"></i> Cupons
            </a>
            <a href="{{ route('admin.banners.index') }}" class="admin-nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                <i class="bi bi-image"></i> Banners
            </a>
            @if(isset($lojaAtual) && $lojaAtual?->nfe_ativo)
            <a href="{{ route('admin.nfe.index') }}" class="admin-nav-link {{ request()->routeIs('admin.nfe.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Notas Fiscais
            </a>
            @endif
            @if(Auth::user()->isSuperAdmin() || Auth::user()->isAdmin())
            <div class="admin-nav-group">Configurações</div>
            <a href="{{ route('admin.lojas.index') }}" class="admin-nav-link {{ request()->routeIs('admin.lojas.*') ? 'active' : '' }}">
                <i class="bi bi-shop"></i> Lojas
            </a>
            @endif
            <div class="admin-nav-group">Planos</div>
            <a href="{{ route('admin.planos.index') }}" class="admin-nav-link {{ request()->routeIs('admin.planos.*') ? 'active' : '' }}">
                <i class="bi bi-credit-card"></i> Meu Plano
            </a>
            <div class="admin-nav-group">Ajuda</div>
            <a href="#" class="admin-nav-link" onclick="window.tourSystem && window.tourSystem.init(); return false;">
                <i class="bi bi-question-circle"></i> Tour Guiado
            </a>
            <div class="admin-nav-group">Relatórios</div>
            <a href="{{ route('admin.relatorios.vendas') }}" class="admin-nav-link {{ request()->routeIs('admin.relatorios.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i> Vendas
            </a>
            <a href="{{ route('admin.estatisticas.visitas') }}" class="admin-nav-link {{ request()->routeIs('admin.estatisticas.*') ? 'active' : '' }}">
                <i class="bi bi-eye"></i> Visitas
            </a>
        </nav>

        <div class="admin-sidebar-footer">
            <a href="{{ route('cliente.home') }}" class="admin-nav-link" target="_blank">
                <i class="bi bi-shop"></i> Ver Loja
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="admin-nav-link w-100 text-start border-0 bg-transparent text-danger">
                    <i class="bi bi-box-arrow-right"></i> Sair
                </button>
            </form>
        </div>
    </aside>

    <!-- Conteúdo Principal -->
    <div class="admin-content" id="adminContent">
        <header class="admin-topbar">
            <button class="admin-menu-toggle" onclick="toggleAdminSidebar()">
                <i class="bi bi-list fs-4"></i>
            </button>
            <h1 class="admin-page-title">@yield('titulo', 'Dashboard')</h1>
            <div class="admin-topbar-actions">
                <div class="admin-notif-btn position-relative" title="Pedidos pendentes" id="btnPedidosPendentes" onclick="window.location='{{ route('admin.pedidos.kanban') }}'" style="cursor:pointer">
                <i class="bi bi-bell fs-5"></i>
                <span class="badge-pedidos" id="badgePedidos" style="display:none">0</span>
            </div>
                <div class="admin-topbar-user">
                    <img src="{{ Auth::user()->foto_perfil_url }}" alt="" class="admin-avatar-sm">
                </div>
            </div>
        </header>

        <div class="admin-page-content">
            @if(session('sucesso'))
            <div class="alerta alerta-sucesso mb-3"><i class="bi bi-check-circle"></i> {{ session('sucesso') }}</div>
            @endif
            @if(session('erro'))
            <div class="alerta alerta-erro mb-3"><i class="bi bi-exclamation-circle"></i> {{ session('erro') }}</div>
            @endif

            @yield('conteudo')
        </div>
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

{{-- Alerta visual de novo pedido --}}
<div id="alertaNovoPedido" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;background:rgba(255,107,53,.12);pointer-events:none;animation:flashAdmin .6s ease-in-out 4">
</div>
<div id="toastNovoPedido" style="display:none;position:fixed;top:80px;right:24px;z-index:10000;background:#FF6B35;color:#fff;border-radius:16px;padding:16px 24px;font-family:'Nunito',sans-serif;font-weight:800;font-size:1rem;box-shadow:0 8px 32px rgba(255,107,53,.5);cursor:pointer;min-width:280px" onclick="irParaPedidos()">
    <div style="display:flex;align-items:center;gap:12px">
        <span style="font-size:2rem">🔔</span>
        <div>
            <div style="font-size:1.1rem">NOVO PEDIDO!</div>
            <div id="toastNovoPedidoInfo" style="font-size:.85rem;font-weight:600;opacity:.9">Clique para ver</div>
        </div>
    </div>
</div>

{{-- Audio --}}
<audio id="audioNovoPedidoAdmin" preload="auto" loop>
    <source src="{{ asset('sounds/novo-pedido.mp3') }}" type="audio/mpeg">
</audio>

<style>
@keyframes flashAdmin{0%,100%{background:rgba(255,107,53,0)}50%{background:rgba(255,107,53,.18)}}
.badge-pedidos{position:absolute;top:-4px;right:-4px;background:#dc3545;color:#fff;border-radius:50%;width:18px;height:18px;font-size:.65rem;font-weight:900;display:flex;align-items:center;justify-content:center}
</style>

<script>
    const APP_URL    = '{{ config("app.url") }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';
    const USUARIO_ID = {{ auth()->id() ?? 'null' }};
    const LOJA_ID    = {{ auth()->user()?->loja_id ?? 'null' }};
</script>
<script src="{{ asset('js/admin.js') }}"></script>
<script>
(function() {
    let ultimoPedidoId = 0;
    let somAtivadoAdmin = false;
    let somLiberado = false;

    // Liberar audio apos primeira interacao do usuario
    document.addEventListener('click', function() { somLiberado = true; }, { once: true });

    function tocarSomAdmin() {
        if (!somLiberado) return;
        const audio = document.getElementById('audioNovoPedidoAdmin');
        if (!audio) return;
        audio.currentTime = 0;
        audio.play().then(() => {
            setTimeout(() => { audio.pause(); audio.currentTime = 0; }, 3000);
        }).catch(() => {});
    }

    function mostrarAlertaAdmin(numero, pedidoId) {
        // Flash visual
        const alerta = document.getElementById('alertaNovoPedido');
        if (alerta) {
            alerta.style.display = 'block';
            setTimeout(() => { alerta.style.display = 'none'; }, 3000);
        }

        // Toast
        const toast = document.getElementById('toastNovoPedido');
        const info  = document.getElementById('toastNovoPedidoInfo');
        if (toast) {
            if (info) info.textContent = 'Pedido ' + numero + ' aguarda confirmação';
            toast.style.display = 'block';
            tocarSomAdmin();

            // Titulo piscando
            let blink = true;
            const originalTitle = document.title;
            const blinkInterval = setInterval(() => {
                document.title = blink ? '🔔 NOVO PEDIDO!' : originalTitle;
                blink = !blink;
            }, 800);
            setTimeout(() => {
                clearInterval(blinkInterval);
                document.title = originalTitle;
                toast.style.display = 'none';
            }, 15000);
        }
    }

    function irParaPedidos() {
        window.location = '{{ route("admin.pedidos.kanban") }}';
    }
    window.irParaPedidos = irParaPedidos;

    function verificarNovosPedidos() {
        if (!LOJA_ID) return;
        fetch('/admin/pedidos/ultimos?loja_id=' + LOJA_ID, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN }
        })
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('badgePedidos');
            if (data.pendentes > 0) {
                if (badge) { badge.textContent = data.pendentes; badge.style.display = 'flex'; }
            } else {
                if (badge) badge.style.display = 'none';
            }

            if (data.ultimo_id && data.ultimo_id > ultimoPedidoId) {
                if (ultimoPedidoId > 0) {
                    mostrarAlertaAdmin(data.ultimo_numero, data.ultimo_id);
                }
                ultimoPedidoId = data.ultimo_id;
            } else if (ultimoPedidoId === 0 && data.ultimo_id) {
                ultimoPedidoId = data.ultimo_id;
            }
        })
        .catch(() => {});
    }

    // Iniciar polling a cada 20 segundos
    setTimeout(verificarNovosPedidos, 2000);
    setInterval(verificarNovosPedidos, 20000);
})();
</script>
@stack('scripts')
</body>
</html>

{{-- Sistema de Tours Guiados --}}
<script src="https://cdn.jsdelivr.net/npm/shepherd.js@11.0.1/dist/js/shepherd.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@11.0.1/dist/css/shepherd.css">

<script>
// Sistema de Tours Guiados
class TourSystem {
    constructor() {
        this.tour = null;
        this.tourAtual = null;
        this.tourUsuario = null;
        this.isInitialized = false;
    }

    async init() {
        if (this.isInitialized) return;
        
        try {
            // Carregar dados dos tours
            const response = await fetch('/admin/tours');
            const data = await response.json();
            
            this.toursDisponiveis = data.tours_disponiveis;
            this.progressoUsuario = data.progresso_usuario;
            this.tourPendente = data.tour_pendente;
            
            // Se há tour pendente, mostrar notificação
            if (this.tourPendente) {
                this.mostrarNotificacaoTour();
            }
            
            // Se há tour em andamento, continuar
            if (data.tour_atual) {
                this.continuarTour(data.tour_atual);
            }
            
            this.isInitialized = true;
        } catch (error) {
            console.error('Erro ao inicializar sistema de tours:', error);
        }
    }

    mostrarNotificacaoTour() {
        if (!this.tourPendente) return;
        
        // Criar notificação flutuante
        const notificacao = document.createElement('div');
        notificacao.className = 'tour-notification';
        notificacao.innerHTML = `
            <div class="tour-notification-content">
                <div class="tour-notification-icon">
                    <i class="bi bi-play-circle"></i>
                </div>
                <div class="tour-notification-text">
                    <h4>🎉 Tour Guiado Disponível!</h4>
                    <p>Aprenda a usar o sistema com nosso tour interativo</p>
                </div>
                <div class="tour-notification-actions">
                    <button class="btn btn-sm btn-primary tour-start-btn">
                        <i class="bi bi-play"></i> Começar Tour
                    </button>
                    <button class="btn btn-sm btn-outline-secondary tour-dismiss-btn">
                        <i class="bi bi-x"></i> Agora não
                    </button>
                </div>
            </div>
        `;
        
        // Estilos
        notificacao.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            z-index: 10000;
            max-width: 350px;
            animation: slideInRight 0.5s ease;
        `;
        
        document.body.appendChild(notificacao);
        
        // Event listeners
        notificacao.querySelector('.tour-start-btn').addEventListener('click', () => {
            this.iniciarTour(this.tourPendente.id);
            document.body.removeChild(notificacao);
        });
        
        notificacao.querySelector('.tour-dismiss-btn').addEventListener('click', () => {
            document.body.removeChild(notificacao);
        });
        
        // Auto remover após 10 segundos
        setTimeout(() => {
            if (document.body.contains(notificacao)) {
                notificacao.style.animation = 'slideOutRight 0.5s ease';
                setTimeout(() => {
                    if (document.body.contains(notificacao)) {
                        document.body.removeChild(notificacao);
                    }
                }, 500);
            }
        }, 10000);
    }

    async iniciarTour(tourId) {
        try {
            const response = await fetch(`/admin/tours/${tourId}/iniciar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.tourAtual = this.toursDisponiveis.find(t => t.id === tourId);
                this.tourUsuario = data.tour_usuario;
                this.criarTourShepherd(data.passo_atual, data.total_passos);
                this.tour.start();
            }
        } catch (error) {
            console.error('Erro ao iniciar tour:', error);
        }
    }

    continuarTour(tourData) {
        this.tourAtual = tourData.tour;
        this.tourUsuario = tourData.tour_usuario;
        this.criarTourShepherd(tourData.passo_atual, tourData.proximo_passo);
        this.tour.start();
    }

    criarTourShepherd(passoAtual, proximoPasso) {
        this.tour = new Shepherd.Tour({
            tourName: this.tourAtual.nome,
            steps: this.formatarPassos(passoAtual, proximoPasso),
            useModalOverlay: true,
            exitOnEsc: true,
            keyboardNavigation: true,
            defaultStepOptions: {
                classes: 'tour-step',
                scrollTo: { behavior: 'smooth', block: 'center' },
                cancelIcon: {
                    enabled: true,
                    label: 'Fechar Tour'
                },
                buttons: [
                    {
                        text: 'Pular Tour',
                        action: () => this.pularTour(),
                        classes: 'btn btn-sm btn-outline-secondary'
                    },
                    {
                        text: 'Anterior',
                        action: () => this.voltarPasso(),
                        classes: 'btn btn-sm btn-outline-primary',
                        disabled: !this.tourUsuario.pode_voltar
                    },
                    {
                        text: this.tourUsuario.esta_no_ultimo ? 'Concluir' : 'Próximo',
                        action: () => this.avancarPasso(),
                        classes: 'btn btn-sm btn-primary'
                    }
                ]
            }
        });

        // Event listeners
        this.tour.on('cancel', () => this.pularTour());
        this.tour.on('complete', () => this.concluirTour());
    }

    formatarPassos(passoAtual, proximoPasso) {
        const passos = [];
        
        // Adicionar passo atual
        if (passoAtual) {
            passos.push({
                id: passoAtual.id,
                title: passoAtual.title,
                text: passoAtual.text,
                attachTo: {
                    element: passoAtual.element,
                    on: 'auto'
                },
                buttons: this.formatarBotoes(passoAtual.buttons)
            });
        }
        
        // Adicionar próximo passo se existir
        if (proximoPasso && proximoPasso !== passoAtual) {
            passos.push({
                id: proximoPasso.id,
                title: proximoPasso.title,
                text: proximoPasso.text,
                attachTo: {
                    element: proximoPasso.element,
                    on: 'auto'
                },
                buttons: this.formatarBotoes(proximoPasso.buttons)
            });
        }
        
        return passos;
    }

    formatarBotoes(botoes) {
        return botoes.map(botao => ({
            text: botao.text,
            action: () => this.executarAcao(botao.action),
            classes: this.getClasseBotao(botao.text)
        }));
    }

    getClasseBotao(texto) {
        if (texto.toLowerCase().includes('concluir') || texto.toLowerCase().includes('finalizar')) {
            return 'btn btn-sm btn-success';
        } else if (texto.toLowerCase().includes('próximo')) {
            return 'btn btn-sm btn-primary';
        } else if (texto.toLowerCase().includes('anterior')) {
            return 'btn btn-sm btn-outline-primary';
        } else if (texto.toLowerCase().includes('pular')) {
            return 'btn btn-sm btn-outline-secondary';
        } else {
            return 'btn btn-sm btn-primary';
        }
    }

    executarAcao(acao) {
        switch (acao) {
            case 'next':
                this.avancarPasso();
                break;
            case 'previous':
                this.voltarPasso();
                break;
            case 'complete':
                this.concluirTour();
                break;
            case 'skip':
                this.pularTour();
                break;
            default:
                this.avancarPasso();
        }
    }

    async avancarPasso() {
        if (!this.tourAtual) return;
        
        try {
            const response = await fetch(`/admin/tours/${this.tourAtual.id}/avancar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                if (data.concluido) {
                    this.tour.complete();
                } else {
                    // Atualizar botões
                    this.atualizarBotoesTour();
                    // Ir para próximo passo
                    this.tour.next();
                }
            }
        } catch (error) {
            console.error('Erro ao avançar passo:', error);
        }
    }

    async voltarPasso() {
        if (!this.tourAtual) return;
        
        try {
            const response = await fetch(`/admin/tours/${this.tourAtual.id}/voltar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.atualizarBotoesTour();
                this.tour.back();
            }
        } catch (error) {
            console.error('Erro ao voltar passo:', error);
        }
    }

    async pularTour() {
        if (!this.tourAtual) return;
        
        try {
            const response = await fetch(`/admin/tours/${this.tourAtual.id}/pular`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.tour.cancel();
                this.mostrarMensagem('Tour pulado. Você pode reiniciá-lo a qualquer momento!', 'info');
            }
        } catch (error) {
            console.error('Erro ao pular tour:', error);
        }
    }

    async concluirTour() {
        this.mostrarMensagem('🎉 Parabéns! Você concluiu o tour com sucesso!', 'success');
        
        // Registrar conclusão
        try {
            await fetch('/admin/tours/progresso', {
                headers: { 'Content-Type': 'application/json' }
            });
        } catch (error) {
            console.error('Erro ao registrar progresso:', error);
        }
    }

    atualizarBotoesTour() {
        // Atualizar estado dos botões
        const buttons = this.tour.getCurrentStep().options.buttons;
        
        buttons.forEach(button => {
            if (button.text.toLowerCase().includes('anterior')) {
                button.options.disabled = !this.tourUsuario.pode_voltar;
            }
            if (button.text.toLowerCase().includes('próximo')) {
                button.text = this.tourUsuario.esta_no_ultimo ? 'Concluir' : 'Próximo';
            }
        });
    }

    mostrarMensagem(mensagem, tipo = 'info') {
        // Criar toast de mensagem
        const toast = document.createElement('div');
        toast.className = `tour-toast tour-toast-${tipo}`;
        toast.innerHTML = `
            <div class="tour-toast-content">
                <i class="bi bi-${tipo === 'success' ? 'check-circle' : 'info-circle'}"></i>
                <span>${mensagem}</span>
            </div>
        `;
        
        // Estilos
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: ${tipo === 'success' ? '#28a745' : '#007bff'};
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 10001;
            animation: slideInUp 0.5s ease;
            max-width: 300px;
        `;
        
        document.body.appendChild(toast);
        
        // Auto remover
        setTimeout(() => {
            toast.style.animation = 'slideOutDown 0.5s ease';
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 500);
        }, 5000);
    }
}

// CSS para animações
const tourStyles = `
<style>
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes slideOutRight {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}

@keyframes slideInUp {
    from { transform: translateY(100%); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

@keyframes slideOutDown {
    from { transform: translateY(0); opacity: 1; }
    to { transform: translateY(100%); opacity: 0; }
}

.tour-notification-content {
    padding: 20px;
}

.tour-notification-icon {
    font-size: 24px;
    color: #28a745;
    margin-bottom: 10px;
}

.tour-notification-text h4 {
    margin: 0 0 5px 0;
    color: #2c3e50;
    font-size: 16px;
}

.tour-notification-text p {
    margin: 0 0 15px 0;
    color: #6c757d;
    font-size: 14px;
}

.tour-notification-actions {
    display: flex;
    gap: 10px;
}

.tour-toast-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

.tour-step {
    z-index: 9999 !important;
}

.tour-step .shepherd-button {
    margin: 0 5px;
}

.shepherd-footer {
    padding: 15px;
}

.shepherd-cancel-icon {
    color: #6c757d !important;
}

.shepherd-title {
    color: #2c3e50 !important;
}

.shepherd-text {
    color: #495057 !important;
    line-height: 1.5 !important;
}

.shepherd-element {
    background: white !important;
    border-radius: 12px !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;
}

.shepherd-arrow {
    border-top-color: white !important;
}
</style>
`;

// Inserir estilos no head
document.head.insertAdjacentHTML('beforeend', tourStyles);

// Inicializar sistema quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.tourSystem = new TourSystem();
    window.tourSystem.init();
});
</script>
