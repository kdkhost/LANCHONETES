<?php

/**
 * Laravel Application Entry Point
 * Verifica se o sistema está instalado e redireciona para o instalador se necessário
 */

use Illuminate\Http\Request;

// Verificar se o sistema está instalado
function isSystemInstalled() {
    // Verificar arquivo .env
    if (!file_exists(__DIR__ . '/.env')) {
        return false;
    }
    
    // Verificar se as chaves estão configuradas
    $envContent = file_get_contents(__DIR__ . '/.env');
    if (strpos($envContent, 'APP_KEY=base64:') === false) {
        return false;
    }
    
    // Verificar se o banco está configurado
    if (strpos($envContent, 'DB_DATABASE=') === false || 
        strpos($envContent, 'DB_USERNAME=') === false) {
        return false;
    }
    
    // Verificar se as migrations foram executadas
    try {
        // Carregar o ambiente Laravel
        require_once __DIR__ . '/vendor/autoload.php';
        $app = require_once __DIR__ . '/bootstrap/app.php';
        
        // Verificar se a tabela users existe
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        
        if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
            // Verificar se há pelo menos um usuário admin
            $userCount = \App\Models\User::count();
            return $userCount > 0;
        }
        
        return false;
    } catch (\Exception $e) {
        return false;
    }
}

// Verificar se é uma requisição para o instalador
function isInstallerRequest() {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return strpos($uri, '/install') === 0 || 
           strpos($uri, '/check') === 0 ||
           strpos($uri, '/install.php') === 0 ||
           strpos($uri, '/check.php') === 0;
}

// Redirecionar para o instalador se não estiver instalado
if (!isSystemInstalled() && !isInstallerRequest()) {
    // Redirecionar para o instalador
    header('Location: /install.php');
    exit;
}

// Se estiver instalado ou for requisição do instalador, continuar com o Laravel
if (isSystemInstalled()) {
    // Sistema instalado - carregar Laravel normalmente
} else {
    // Não instalado - mostrar página de instalação ou continuar para o instalador
    if (!isInstallerRequest()) {
        // Mostrar página de boas-vindas ao instalador
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Instalação - Sistema de Lanchonetes</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
            <style>
                body {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                }
                .install-card {
                    background: white;
                    border-radius: 20px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                    padding: 40px;
                    max-width: 600px;
                    width: 100%;
                }
                .logo {
                    text-align: center;
                    margin-bottom: 30px;
                }
                .logo i {
                    font-size: 4rem;
                    color: #FF6B35;
                }
                .btn-install {
                    background: linear-gradient(135deg, #FF6B35, #F7931E);
                    border: none;
                    padding: 15px 30px;
                    font-size: 18px;
                    font-weight: 600;
                    border-radius: 10px;
                    color: white;
                    text-decoration: none;
                    display: inline-block;
                    transition: all 0.3s;
                }
                .btn-install:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 10px 20px rgba(255,107,53,0.3);
                    color: white;
                }
                .feature-list {
                    list-style: none;
                    padding: 0;
                    margin: 30px 0;
                }
                .feature-list li {
                    padding: 10px 0;
                    border-bottom: 1px solid #e9ecef;
                }
                .feature-list li:last-child {
                    border-bottom: none;
                }
                .feature-list i {
                    color: #28a745;
                    margin-right: 10px;
                }
            </style>
        </head>
        <body>
            <div class="install-card">
                <div class="logo">
                    <i class="bi bi-shop"></i>
                    <h1 class="mt-3">Sistema de Lanchonetes</h1>
                    <p class="text-muted">Bem-vindo ao seu novo sistema de gestão!</p>
                </div>
                
                <div class="text-center">
                    <h3 class="mb-4">🚀 Vamos Começar a Instalação?</h3>
                    <p class="text-muted mb-4">
                        Nosso instalador automático vai configurar tudo para você em poucos minutos.
                    </p>
                    
                    <ul class="feature-list text-start">
                        <li><i class="bi bi-check-circle-fill"></i> Verificação automática de requisitos</li>
                        <li><i class="bi bi-check-circle-fill"></i> Instalação do banco de dados</li>
                        <li><i class="bi bi-check-circle-fill"></i> Configuração do ambiente</li>
                        <li><i class="bi bi-check-circle-fill"></i> Criação do usuário administrador</li>
                        <li><i class="bi bi-check-circle-fill"></i> Teste de funcionalidades</li>
                    </ul>
                    
                    <a href="/install.php" class="btn-install">
                        <i class="bi bi-play-circle"></i> Começar Instalação
                    </a>
                    
                    <div class="mt-4">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Você também pode <a href="/check.php">verificar requisitos</a> antes de começar.
                        </small>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

/**
 * Laravel - The PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/

require __DIR__.'/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
