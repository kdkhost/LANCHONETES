<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Cliente\HomeController;
use App\Http\Controllers\Cliente\CheckoutController;
use App\Http\Controllers\Cliente\PedidoClienteController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PedidoAdminController;
use App\Http\Controllers\Admin\ProdutoAdminController;
use App\Http\Controllers\Admin\LojaAdminController;
use App\Http\Controllers\Entregador\EntregadorController;
use App\Http\Controllers\RastreamentoController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\LgpdController;
use App\Http\Controllers\Admin\CozinhaController;
use App\Http\Controllers\Admin\NfeAdminController;
use App\Http\Controllers\MarketingController;

// ─── Autenticação ────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/registro', [AuthController::class, 'showRegistro'])->name('registro');
    Route::post('/registro',[AuthController::class, 'registro']);
    Route::get('/esqueceu-senha',  [AuthController::class, 'showEsqueceuSenha'])->name('auth.esqueceu-senha');
    Route::post('/esqueceu-senha', [AuthController::class, 'enviarRedefinicao']);
    Route::get('/redefinir-senha/{token}',  [AuthController::class, 'showRedefinirSenha'])->name('auth.redefinir-senha');
    Route::post('/redefinir-senha',         [AuthController::class, 'redefinirSenha']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ─── Rastreamento público (sem autenticação) ─────────────────────────────────
Route::prefix('rastreamento')->group(function () {
    Route::get('/{token}',       [RastreamentoController::class, 'publico'])->name('rastreamento.publico');
    Route::get('/{token}/status',[RastreamentoController::class, 'status'])->name('rastreamento.status');
});

// ─── Webhooks ────────────────────────────────────────────────────────────────
Route::prefix('webhook')->name('webhook.')->group(function () {
    Route::post('/mercadopago', [WebhookController::class, 'mercadoPago'])->name('mercadopago');
    Route::post('/mercadopago/plano', [WebhookController::class, 'mercadoPagoPlano'])->name('mercadopago.plano');
});

// ─── Painel Administrativo ───────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_admin,admin,gerente,atendente,cozinheiro'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('pedidos')->name('pedidos.')->group(function () {
        Route::get('/',                            [PedidoAdminController::class, 'index'])->name('index');
        Route::get('/kanban',                      [PedidoAdminController::class, 'kanban'])->name('kanban');
        Route::get('/ultimos',                     [PedidoAdminController::class, 'ultimos'])->name('ultimos');
        Route::get('/{pedido}',                    [PedidoAdminController::class, 'show'])->name('show');
        Route::post('/{pedido}/status',            [PedidoAdminController::class, 'atualizarStatus'])->name('status');
        Route::post('/{pedido}/entregador',        [PedidoAdminController::class, 'atribuirEntregador'])->name('entregador');
        Route::post('/{pedido}/mensagem',          [PedidoAdminController::class, 'enviarMensagem'])->name('mensagem');
    });

    Route::prefix('produtos')->name('produtos.')->group(function () {
        Route::get('/',                    [ProdutoAdminController::class, 'index'])->name('index');
        Route::get('/{produto}/editar',    [ProdutoAdminController::class, 'edit'])->name('edit');
        Route::get('/criar',               [ProdutoAdminController::class, 'create'])->name('create')->middleware('plano.ativo:produtos');
        Route::post('/',                   [ProdutoAdminController::class, 'store'])->name('store')->middleware('plano.ativo:produtos');
        Route::put('/{produto}',           [ProdutoAdminController::class, 'update'])->name('update')->middleware('plano.ativo:produtos');
        Route::delete('/{produto}',        [ProdutoAdminController::class, 'destroy'])->name('destroy')->middleware('plano.ativo:produtos');
        Route::post('/upload',             [ProdutoAdminController::class, 'uploadImagem'])->name('upload')->middleware('plano.ativo:produtos');
        Route::post('/{produto}/toggle',   [ProdutoAdminController::class, 'toggleStatus'])->name('toggle')->middleware('plano.ativo:produtos');
        Route::post('/reordenar',          [ProdutoAdminController::class, 'reordenar'])->name('reordenar')->middleware('plano.ativo:produtos');
    });

    Route::prefix('lojas')->name('lojas.')->middleware('role:super_admin,admin')->group(function () {
        Route::get('/',                          [LojaAdminController::class, 'index'])->name('index');
        Route::get('/criar',                     [LojaAdminController::class, 'create'])->name('create');
        Route::post('/',                         [LojaAdminController::class, 'store'])->name('store');
        Route::get('/{loja}/editar',             [LojaAdminController::class, 'edit'])->name('edit');
        Route::put('/{loja}',                    [LojaAdminController::class, 'update'])->name('update');
        Route::post('/{loja}/mercadopago',       [LojaAdminController::class, 'configuracoesMercadoPago'])->name('mercadopago')->middleware('plano.ativo:pagamento');
        Route::get('/{loja}/bairros',            [LojaAdminController::class, 'bairros'])->name('bairros');
        Route::post('/{loja}/bairros',           [LojaAdminController::class, 'salvarBairros'])->name('bairros.salvar');
        Route::post('/upload',                   [LojaAdminController::class, 'uploadImagem'])->name('upload');
    });

    Route::prefix('categorias')->name('categorias.')->group(function () {
        Route::get('/',                        [\App\Http\Controllers\Admin\CategoriaAdminController::class, 'index'])->name('index');
        Route::post('/',                       [\App\Http\Controllers\Admin\CategoriaAdminController::class, 'store'])->name('store');
        Route::put('/{categoria}',             [\App\Http\Controllers\Admin\CategoriaAdminController::class, 'update'])->name('update');
        Route::delete('/{categoria}',          [\App\Http\Controllers\Admin\CategoriaAdminController::class, 'destroy'])->name('destroy');
        Route::post('/upload',                 [\App\Http\Controllers\Admin\CategoriaAdminController::class, 'uploadImagem'])->name('upload');
        Route::post('/reordenar',              [\App\Http\Controllers\Admin\CategoriaAdminController::class, 'reordenar'])->name('reordenar');
    });

    Route::prefix('funcionarios')->name('funcionarios.')->group(function () {
        Route::get('/',                        [\App\Http\Controllers\Admin\FuncionarioAdminController::class, 'index'])->name('index');
        Route::get('/criar',                   [\App\Http\Controllers\Admin\FuncionarioAdminController::class, 'create'])->name('create');
        Route::post('/',                       [\App\Http\Controllers\Admin\FuncionarioAdminController::class, 'store'])->name('store');
        Route::get('/{funcionario}/editar',    [\App\Http\Controllers\Admin\FuncionarioAdminController::class, 'edit'])->name('edit');
        Route::put('/{funcionario}',           [\App\Http\Controllers\Admin\FuncionarioAdminController::class, 'update'])->name('update');
        Route::delete('/{funcionario}',        [\App\Http\Controllers\Admin\FuncionarioAdminController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('cupons')->name('cupons.')->group(function () {
        Route::get('/',                    [\App\Http\Controllers\Admin\CupomAdminController::class, 'index'])->name('index');
        Route::post('/',                   [\App\Http\Controllers\Admin\CupomAdminController::class, 'store'])->name('store');
        Route::put('/{cupom}',             [\App\Http\Controllers\Admin\CupomAdminController::class, 'update'])->name('update');
        Route::delete('/{cupom}',          [\App\Http\Controllers\Admin\CupomAdminController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('banners')->name('banners.')->group(function () {
        Route::get('/',               [\App\Http\Controllers\Admin\BannerAdminController::class, 'index'])->name('index');
        Route::post('/',              [\App\Http\Controllers\Admin\BannerAdminController::class, 'store'])->name('store');
        Route::put('/{banner}',       [\App\Http\Controllers\Admin\BannerAdminController::class, 'update'])->name('update');
        Route::delete('/{banner}',    [\App\Http\Controllers\Admin\BannerAdminController::class, 'destroy'])->name('destroy');
        Route::post('/upload',        [\App\Http\Controllers\Admin\BannerAdminController::class, 'upload'])->name('upload');
    });

    Route::prefix('relatorios')->name('relatorios.')->group(function () {
        Route::get('/vendas',        [\App\Http\Controllers\Admin\RelatorioAdminController::class, 'vendas'])->name('vendas');
        Route::get('/exportar-csv',  [\App\Http\Controllers\Admin\RelatorioAdminController::class, 'exportarCsv'])->name('exportar-csv');
    });

    // ── Estatísticas ──────────────────────────────────────────────────────────
    Route::get('/estatisticas/visitas', [\App\Http\Controllers\Admin\EstatisticasController::class, 'visitas'])->name('estatisticas.visitas');

    // ── Planos ───────────────────────────────────────────────────────────────
    Route::prefix('planos')->name('planos.')->group(function () {
        Route::get('/',                         [\App\Http\Controllers\Admin\PlanoAdminController::class, 'index'])->name('index');
        Route::get('/upgrade',                  [\App\Http\Controllers\Admin\PlanoAdminController::class, 'upgrade'])->name('upgrade');
        Route::get('/assinaturas',              [\App\Http\Controllers\Admin\PlanoAdminController::class, 'assinaturas'])->name('assinaturas');
        Route::get('/checkout/{plano}',        [\App\Http\Controllers\Admin\PlanoAdminController::class, 'checkout'])->name('checkout');
        Route::post('/checkout/{plano}',       [\App\Http\Controllers\Admin\PlanoAdminController::class, 'processarPagamento'])->name('pagar');
        Route::get('/mercadopago/{plano}',     [\App\Http\Controllers\Admin\PlanoAdminController::class, 'mercadoPagoCheckout'])->name('checkout.mercadopago');
        Route::get('/mercadopago/sucesso',    [\App\Http\Controllers\Admin\PlanoAdminController::class, 'mercadoPagoSucesso'])->name('mercadopago.sucesso');
        Route::get('/mercadopago/falha',      [\App\Http\Controllers\Admin\PlanoAdminController::class, 'mercadoPagoFalha'])->name('mercadopago.falha');
        Route::get('/mercadopago/pendente',   [\App\Http\Controllers\Admin\PlanoAdminController::class, 'mercadoPagoPendente'])->name('mercadopago.pendente');
        Route::get('/pagamento/{paymentId}/detalhes', [\App\Http\Controllers\Admin\PlanoAdminController::class, 'detalhesPagamento'])->name('pagamento.detalhes');
        Route::post('/{assinatura}/cancelar',   [\App\Http\Controllers\Admin\PlanoAdminController::class, 'cancelarAssinatura'])->name('cancelar');
        
        // Super Admin only
        Route::get('/gerenciar',                [\App\Http\Controllers\Admin\PlanoAdminController::class, 'planos'])->name('gerenciar')->middleware('role:super_admin');
        Route::post('/criar',                   [\App\Http\Controllers\Admin\PlanoAdminController::class, 'criarPlano'])->name('criar')->middleware('role:super_admin');
        Route::put('/{plano}',                  [\App\Http\Controllers\Admin\PlanoAdminController::class, 'editarPlano'])->name('editar')->middleware('role:super_admin');
        Route::delete('/{plano}',               [\App\Http\Controllers\Admin\PlanoAdminController::class, 'excluirPlano'])->name('excluir')->middleware('role:super_admin');
        Route::post('/{assinatura}/aprovar',    [\App\Http\Controllers\Admin\PlanoAdminController::class, 'aprovarPagamento'])->name('aprovar')->middleware('role:super_admin');
    });

    // ── Tours Guiados ───────────────────────────────────────────────────────────
    Route::prefix('tours')->name('tours.')->group(function () {
        Route::get('/',                         [\App\Http\Controllers\Admin\TourController::class, 'index'])->name('index');
        Route::get('/progresso',                [\App\Http\Controllers\Admin\TourController::class, 'progresso'])->name('progresso');
        Route::get('/listar',                   [\App\Http\Controllers\Admin\TourController::class, 'listar'])->name('listar')->middleware('role:super_admin');
        Route::post('/',                        [\App\Http\Controllers\Admin\TourController::class, 'criar'])->name('criar')->middleware('role:super_admin');
        Route::put('/{tour}',                   [\App\Http\Controllers\Admin\TourController::class, 'atualizar'])->name('atualizar')->middleware('role:super_admin');
        Route::delete('/{tour}',                [\App\Http\Controllers\Admin\TourController::class, 'excluir'])->name('excluir')->middleware('role:super_admin');
        
        // Tour actions
        Route::post('/{tour}/iniciar',          [\App\Http\Controllers\Admin\TourController::class, 'iniciar'])->name('iniciar');
        Route::post('/{tour}/avancar',          [\App\Http\Controllers\Admin\TourController::class, 'avancar'])->name('avancar');
        Route::post('/{tour}/voltar',           [\App\Http\Controllers\Admin\TourController::class, 'voltar'])->name('voltar');
        Route::post('/{tour}/pular',            [\App\Http\Controllers\Admin\TourController::class, 'pular'])->name('pular');
        Route::post('/{tour}/reiniciar',        [\App\Http\Controllers\Admin\TourController::class, 'reiniciar'])->name('reiniciar');
    });

    // ── Cozinha ──────────────────────────────────────────────────────────────
    Route::get('/cozinha',              [CozinhaController::class, 'index'])->name('cozinha');
    Route::post('/cozinha/pin',         [CozinhaController::class, 'verificarPin'])->name('cozinha.pin');
    Route::get('/cozinha/pedidos',      [CozinhaController::class, 'pedidosAtivos'])->name('cozinha.pedidos');
    Route::post('/cozinha/{pedido}/avancar', [CozinhaController::class, 'avancarStatus'])->name('cozinha.avancar');

    // ── Notas Fiscais ─────────────────────────────────────────────────────────
    Route::prefix('nfe')->name('nfe.')->group(function () {
        Route::get('/',                         [NfeAdminController::class, 'index'])->name('index');
        Route::post('/pedido/{pedido}/emitir',  [NfeAdminController::class, 'emitir'])->name('emitir');
        Route::post('/{nota}/cancelar',         [NfeAdminController::class, 'cancelar'])->name('cancelar');
        Route::get('/{nota}/danfe',             [NfeAdminController::class, 'danfe'])->name('danfe');
    });
});

// ─── Painel do Entregador ────────────────────────────────────────────────────
Route::prefix('entregador')->name('entregador.')->middleware(['auth', 'role:entregador'])->group(function () {
    Route::get('/dashboard',               [EntregadorController::class, 'dashboard'])->name('dashboard');
    Route::post('/disponibilidade',        [EntregadorController::class, 'toggleDisponibilidade'])->name('disponibilidade');
    Route::post('/entregas/{entrega}/aceitar',   [EntregadorController::class, 'aceitarEntrega'])->name('aceitar');
    Route::post('/entregas/{entrega}/coletar',   [EntregadorController::class, 'coletarPedido'])->name('coletar');
    Route::post('/entregas/{entrega}/localizacao',[EntregadorController::class, 'atualizarLocalizacao'])->name('localizacao');
    Route::post('/entregas/{entrega}/confirmar', [EntregadorController::class, 'confirmarEntrega'])->name('confirmar');
    Route::get('/historico',               [EntregadorController::class, 'historico'])->name('historico');
    Route::get('/entregas/{entrega}/mapa', [EntregadorController::class, 'mapa'])->name('mapa');
});

// ─── Área do Cliente ─────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/perfil',          [PerfilController::class, 'index'])->name('perfil.index');
    Route::put('/perfil',          [PerfilController::class, 'update'])->name('perfil.update');
    Route::post('/perfil/foto',    [PerfilController::class, 'uploadFoto'])->name('perfil.foto');
    Route::get('/perfil/enderecos',[PerfilController::class, 'enderecos'])->name('perfil.enderecos');
    Route::post('/perfil/enderecos',[PerfilController::class, 'salvarEndereco'])->name('perfil.enderecos.salvar');
    Route::post('/perfil/enderecos/{endereco}/principal', [PerfilController::class, 'definirPrincipal'])->name('perfil.enderecos.principal');
    Route::delete('/perfil/enderecos/{endereco}', [PerfilController::class, 'removerEndereco'])->name('perfil.enderecos.remover');

    Route::prefix('pedidos')->name('cliente.pedidos.')->group(function () {
        Route::get('/',                         [PedidoClienteController::class, 'index'])->name('index');
        Route::get('/{pedido}',                 [PedidoClienteController::class, 'show'])->name('show');
        Route::post('/{pedido}/cancelar',       [PedidoClienteController::class, 'cancelar'])->name('cancelar');
        Route::get('/{pedido}/rastrear',        [PedidoClienteController::class, 'rastrear'])->name('rastrear');
        Route::get('/{pedido}/localizacao',     [PedidoClienteController::class, 'localizacao'])->name('localizacao');
    });

    Route::get('/avaliar/{pedido}',        [PedidoClienteController::class, 'avaliar'])->name('cliente.avaliar');
    Route::post('/avaliar/{pedido}',       [PedidoClienteController::class, 'salvarAvaliacao']);

    Route::get('/checkout',                [CheckoutController::class, 'index'])->name('cliente.checkout');
    Route::post('/checkout/calcular-frete',[CheckoutController::class, 'calcularFrete'])->name('cliente.checkout.frete');
    Route::post('/checkout/criar',         [CheckoutController::class, 'criar'])->name('cliente.checkout.criar')->middleware('plano.ativo:vendas');
    Route::get('/checkout/sucesso/{pedido}', [CheckoutController::class, 'sucesso'])->name('cliente.pedido.sucesso');
    Route::get('/checkout/falha/{pedido}',   [CheckoutController::class, 'falha'])->name('cliente.pedido.falha');
    Route::get('/checkout/pendente/{pedido}',[CheckoutController::class, 'pendente'])->name('cliente.pedido.pendente');
    Route::get('/checkout/verificar/{pedido}', [CheckoutController::class, 'verificarPagamento'])->name('cliente.checkout.verificar');

    Route::get('/notificacoes',                [NotificacaoController::class, 'index'])->name('notificacoes.index');
    Route::post('/notificacoes/{id}/lida',     [NotificacaoController::class, 'marcarLida'])->name('notificacoes.lida');
    Route::post('/notificacoes/todas-lidas',   [NotificacaoController::class, 'marcarTodasLidas'])->name('notificacoes.todas-lidas');
});

// ─── LGPD / Privacidade ───────────────────────────────────────────────────────
Route::get('/termos-de-uso',       [LgpdController::class, 'termos'])->name('lgpd.termos');
Route::get('/politica-privacidade',[LgpdController::class, 'politica'])->name('lgpd.politica');
Route::post('/lgpd/aceitar',       [LgpdController::class, 'aceitar'])->name('lgpd.aceitar');

// ─── Landing institucional / página comercial ─────────────────────────────────
Route::get('/', [MarketingController::class, 'landing'])->name('marketing.landing');
Route::get('/apresentacao', [MarketingController::class, 'landing']);
Route::post('/apresentacao/contato', [MarketingController::class, 'contato'])->name('marketing.contato');
Route::redirect('/vendas', '/');

// ─── Loja pública (homepage cliente) ─────────────────────────────────────────
Route::get('/app',                      [HomeController::class, 'index'])->name('cliente.home');
Route::get('/lojas',                    [HomeController::class, 'listaLojas'])->name('cliente.lojas');
Route::get('/buscar',                   [HomeController::class, 'buscar'])->name('cliente.buscar');
Route::get('/{lojaSlug}',               [HomeController::class, 'index'])->name('cliente.loja');
Route::get('/{lojaSlug}/produto/{slug}',[HomeController::class, 'produto'])->name('cliente.produto');
