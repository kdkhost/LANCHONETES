<?php

namespace App\Providers;

use App\Models\Usuario;
use App\Models\Loja;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Services\CepService;
use App\Services\UploadService;
use App\Services\EvolutionApiService;
use App\Services\EntregaService;
use App\Services\PedidoService;
use App\Services\MercadoPagoService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CepService::class);
        $this->app->singleton(UploadService::class);
        $this->app->singleton(EvolutionApiService::class);

        $this->app->bind(EntregaService::class, function ($app) {
            return new EntregaService(
                $app->make(CepService::class),
                $app->make(EvolutionApiService::class)
            );
        });

        $this->app->bind(PedidoService::class, function ($app) {
            return new PedidoService(
                $app->make(EntregaService::class),
                $app->make(MercadoPagoService::class, ['loja' => null]),
                $app->make(EvolutionApiService::class)
            );
        });
    }

    public function boot(): void
    {
        // Usar Bootstrap para paginação
        Paginator::useBootstrap();

        // Configurar autenticação para usar o modelo Usuario
        Auth::provider('eloquent', function ($app, array $config) {
            return new \Illuminate\Auth\EloquentUserProvider($app['hash'], $config['model']);
        });

        // Blade directives customizadas
        Blade::directive('moeda', function ($valor) {
            return "<?php echo 'R$ ' . number_format($valor, 2, ',', '.'); ?>";
        });

        Blade::directive('dataHora', function ($data) {
            return "<?php echo \Carbon\Carbon::parse($data)->format('d/m/Y H:i'); ?>";
        });

        // Gates de permissão
        Gate::define('admin', fn(Usuario $u) => $u->isAdmin() || $u->isSuperAdmin());
        Gate::define('gerente', fn(Usuario $u) => in_array($u->role, ['admin', 'super_admin', 'gerente']));
        Gate::define('entregador', fn(Usuario $u) => $u->isEntregador());
        Gate::define('super_admin', fn(Usuario $u) => $u->isSuperAdmin());

        // Compartilhar variáveis globais com todas as views
        View::composer('*', function ($view) {
            $lojaAtual = null;

            if (app()->bound('loja_atual')) {
                $lojaAtual = app('loja_atual');
            }

            if (Auth::check()) {
                $usuario = Auth::user();
                $view->with('usuarioLogado', $usuario);
                $view->with('notificacoesNaoLidas', $usuario->notificacoesNaoLidas()->count());

                if ($usuario->loja_id && !$lojaAtual) {
                    $lojaAtual = cache()->remember(
                        'loja_' . $usuario->loja_id,
                        60,
                        fn() => Loja::find($usuario->loja_id)
                    );
                }
            }

            if (!$lojaAtual) {
                $route = request()->route();
                $slug = $route?->parameter('loja')
                    ?? $route?->parameter('lojaSlug')
                    ?? $route?->parameter('slug');

                if ($slug) {
                    $lojaAtual = Loja::where('slug', $slug)
                        ->where('ativo', true)
                        ->first();
                }
            }

            if (!$lojaAtual) {
                $lojaAtual = cache()->remember('loja_padrao_ativa', 60, fn() => Loja::where('ativo', true)->orderBy('ordem')->first());
            }

            if ($lojaAtual) {
                $view->with('lojaAtual', $lojaAtual);

                if (!app()->bound('loja_atual')) {
                    app()->instance('loja_atual', $lojaAtual);
                }
            }
        });

        // Configurar timezone do Carbon para BR
        \Carbon\Carbon::setLocale('pt_BR');
    }
}
