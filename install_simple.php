<?php
/**
 * 🚀 Instalador Simplificado - Sistema de Lanchonetes
 * Versão robusta e sem erros
 */

// Configurações
define('MIN_PHP_VERSION', '8.4.0');
define('REQUIRED_EXTENSIONS', ['pdo', 'pdo_mysql', 'mbstring', 'curl', 'zip', 'gd', 'bcmath', 'xml', 'fileinfo']);

// Função para verificar se é AJAX
function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
}

// Função para verificar requisitos
function checkRequirements() {
    $results = [];
    
    // Versão PHP
    $phpVersion = PHP_VERSION;
    $phpOk = version_compare($phpVersion, MIN_PHP_VERSION, '>=');
    $results['php'] = [
        'name' => 'Versão PHP',
        'current' => $phpVersion,
        'required' => MIN_PHP_VERSION,
        'status' => $phpOk,
        'message' => $phpOk ? '✅ OK' : '❌ Incompatível'
    ];
    
    // Extensões
    $extensionsOk = true;
    $results['extensions'] = [];
    foreach (REQUIRED_EXTENSIONS as $ext) {
        $loaded = extension_loaded($ext);
        $results['extensions'][$ext] = [
            'name' => $ext,
            'status' => $loaded,
            'message' => $loaded ? '✅ Instalada' : '❌ Faltando'
        ];
        if (!$loaded) $extensionsOk = false;
    }
    $results['extensions']['all_ok'] = $extensionsOk;
    
    // Permissões
    $dirs = ['storage', 'storage/logs', 'storage/framework', 'bootstrap/cache', 'public'];
    $permissionsOk = true;
    $results['permissions'] = [];
    foreach ($dirs as $dir) {
        $exists = is_dir($dir);
        $writable = $exists && is_writable($dir);
        $results['permissions'][$dir] = [
            'name' => $dir,
            'exists' => $exists,
            'writable' => $writable,
            'status' => $writable,
            'message' => $writable ? '✅ OK' : ($exists ? '❌ Sem permissão' : '❌ Não existe')
        ];
        if (!$writable) $permissionsOk = false;
    }
    $results['permissions']['all_ok'] = $permissionsOk;
    
    // Composer
    $composerOutput = shell_exec('composer --version 2>&1');
    $hasComposer = !empty($composerOutput) && strpos($composerOutput, 'Composer') !== false;
    $results['composer'] = [
        'name' => 'Composer',
        'status' => $hasComposer,
        'message' => $hasComposer ? '✅ Disponível' : '❌ Não encontrado'
    ];
    
    // Status geral
    $allOk = $phpOk && $extensionsOk && $permissionsOk && $hasComposer;
    $results['all_ok'] = $allOk;
    
    return $results;
}

// Função para instalar sistema
function installSystem($dbConfig, $adminConfig) {
    try {
        $steps = [];
        
        // Criar .env se não existir
        if (!file_exists('.env')) {
            if (file_exists('.env.example')) {
                copy('.env.example', '.env');
                $steps[] = ['status' => 'success', 'message' => 'Arquivo .env criado'];
            } else {
                return ['success' => false, 'message' => 'Arquivo .env.example não encontrado'];
            }
        }
        
        // Atualizar .env com configurações do banco
        $envContent = file_get_contents('.env');
        $envContent = preg_replace('/^DB_HOST=.*/m', 'DB_HOST=' . $dbConfig['host'], $envContent);
        $envContent = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=' . $dbConfig['database'], $envContent);
        $envContent = preg_replace('/^DB_USERNAME=.*/m', 'DB_USERNAME=' . $dbConfig['username'], $envContent);
        $envContent = preg_replace('/^DB_PASSWORD=.*/m', 'DB_PASSWORD=' . $dbConfig['password'], $envContent);
        file_put_contents('.env', $envContent);
        $steps[] = ['status' => 'success', 'message' => 'Configurações do banco atualizadas'];
        
        // Gerar chave
        ob_start();
        system('php artisan key:generate --force 2>&1', $returnCode);
        ob_end_clean();
        if ($returnCode === 0) {
            $steps[] = ['status' => 'success', 'message' => 'Chave da aplicação gerada'];
        }
        
        // Instalar dependências
        ob_start();
        system('composer install --no-dev --optimize-autoloader 2>&1', $returnCode);
        ob_end_clean();
        if ($returnCode === 0) {
            $steps[] = ['status' => 'success', 'message' => 'Dependências instaladas'];
        }
        
        // Link storage
        ob_start();
        system('php artisan storage:link 2>&1', $returnCode);
        ob_end_clean();
        $steps[] = ['status' => 'success', 'message' => 'Storage linkado'];
        
        // Executar migrations
        ob_start();
        system('php artisan migrate --force 2>&1', $returnCode);
        ob_end_clean();
        $steps[] = ['status' => 'success', 'message' => 'Migrations executadas'];
        
        // Limpar cache
        ob_start();
        system('php artisan cache:clear 2>&1', $returnCode);
        ob_end_clean();
        $steps[] = ['status' => 'success', 'message' => 'Cache limpo'];
        
        // Criar usuário admin
        if (file_exists('vendor/autoload.php')) {
            require_once 'vendor/autoload.php';
            $app = require_once 'bootstrap/app.php';
            $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
            $kernel->bootstrap();
            
            // Verificar se usuário já existe
            $existingUser = \App\Models\User::where('email', $adminConfig['email'])->first();
            if (!$existingUser) {
                $user = new \App\Models\User();
                $user->name = $adminConfig['name'];
                $user->email = $adminConfig['email'];
                $user->password = \Hash::make($adminConfig['password']);
                $user->role = 'super_admin';
                $user->email_verified_at = now();
                $user->save();
                $steps[] = ['status' => 'success', 'message' => 'Usuário administrador criado'];
            } else {
                $steps[] = ['status' => 'info', 'message' => 'Usuário administrador já existe'];
            }
        }
        
        return ['success' => true, 'steps' => $steps];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
    }
}

// Processar requisições AJAX
if (isAjaxRequest()) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'check':
            echo json_encode(['success' => true, 'data' => checkRequirements()]);
            exit;
            
        case 'install':
            $dbConfig = [
                'host' => $_POST['db_host'] ?? 'localhost',
                'database' => $_POST['db_database'] ?? '',
                'username' => $_POST['db_username'] ?? '',
                'password' => $_POST['db_password'] ?? ''
            ];
            $adminConfig = [
                'name' => $_POST['admin_name'] ?? '',
                'email' => $_POST['admin_email'] ?? '',
                'password' => $_POST['admin_password'] ?? ''
            ];
            echo json_encode(installSystem($dbConfig, $adminConfig));
            exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 Instalador Simplificado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .install-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #FF6B35, #F7931E);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .content {
            padding: 40px;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e9ecef;
            z-index: 0;
        }
        
        .step {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }
        
        .step.active {
            background: #FF6B35;
            border-color: #FF6B35;
            color: white;
        }
        
        .step.completed {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }
        
        .step-content {
            display: none;
        }
        
        .step-content.active {
            display: block;
        }
        
        .check-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .check-item.ok {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        
        .check-item.error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #FF6B35, #F7931E);
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255,107,53,0.3);
            color: white;
        }
        
        .progress-step {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .progress-step.success {
            background: #d4edda;
        }
        
        .progress-step.error {
            background: #f8d7da;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="header">
            <h1><i class="bi bi-shop"></i> Sistema de Lanchonetes</h1>
            <p>Instalador Simplificado</p>
        </div>
        
        <div class="content">
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step active" data-step="1">1</div>
                <div class="step" data-step="2">2</div>
                <div class="step" data-step="3">3</div>
            </div>
            
            <!-- Step 1: Welcome -->
            <div class="step-content active" id="step1">
                <h3>🎉 Bem-vindo!</h3>
                <p>Vamos instalar seu sistema de lanchonetes em poucos minutos.</p>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Certifique-se de que seu servidor atende aos requisitos mínimos.
                </div>
                <button class="btn btn-primary" onclick="goToStep(2)">
                    <i class="bi bi-arrow-right"></i> Começar Verificação
                </button>
            </div>
            
            <!-- Step 2: Check Requirements -->
            <div class="step-content" id="step2">
                <h3>🔍 Verificando Requisitos</h3>
                <div id="requirementsList">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-3">Verificando...</p>
                    </div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-secondary" onclick="goToStep(1)">
                        <i class="bi bi-arrow-left"></i> Anterior
                    </button>
                    <button class="btn btn-primary" id="continueBtn" onclick="goToStep(3)" disabled>
                        <i class="bi bi-arrow-right"></i> Continuar
                    </button>
                </div>
            </div>
            
            <!-- Step 3: Install -->
            <div class="step-content" id="step3">
                <h3>⚙️ Configuração do Sistema</h3>
                <form id="installForm">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Banco de Dados</h5>
                            <div class="mb-3">
                                <label class="form-label">Host</label>
                                <input type="text" class="form-control" name="db_host" value="localhost" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Banco</label>
                                <input type="text" class="form-control" name="db_database" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Usuário</label>
                                <input type="text" class="form-control" name="db_username" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Senha</label>
                                <input type="password" class="form-control" name="db_password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>Usuário Administrador</h5>
                            <div class="mb-3">
                                <label class="form-label">Nome</label>
                                <input type="text" class="form-control" name="admin_name" value="Administrador" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="admin_email" value="admin@localhost" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Senha</label>
                                <input type="password" class="form-control" name="admin_password" value="admin123" required>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-secondary" onclick="goToStep(2)">
                            <i class="bi bi-arrow-left"></i> Anterior
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-play"></i> Instalar Sistema
                        </button>
                    </div>
                </form>
                <div id="installProgress" style="display: none;">
                    <h4>🚀 Instalando...</h4>
                    <div id="progressSteps"></div>
                </div>
                <div id="installResult" style="display: none;">
                    <!-- Results will be shown here -->
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentStep = 1;
        let requirementsOk = false;
        
        function goToStep(step) {
            // Hide all steps
            document.querySelectorAll('.step-content').forEach(el => {
                el.classList.remove('active');
            });
            
            // Update indicators
            document.querySelectorAll('.step').forEach((el, index) => {
                el.classList.remove('active', 'completed');
                if (index + 1 < step) {
                    el.classList.add('completed');
                } else if (index + 1 === step) {
                    el.classList.add('active');
                }
            });
            
            // Show target step
            document.getElementById('step' + step).classList.add('active');
            currentStep = step;
            
            if (step === 2) {
                checkRequirements();
            }
        }
        
        async function checkRequirements() {
            try {
                const response = await fetch('install_simple.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'action=check'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    displayRequirements(data.data);
                    requirementsOk = data.data.all_ok;
                    document.getElementById('continueBtn').disabled = !requirementsOk;
                }
            } catch (error) {
                console.error('Erro:', error);
                document.getElementById('requirementsList').innerHTML = `
                    <div class="alert alert-danger">
                        Erro ao verificar requisitos: ${error.message}
                    </div>
                `;
            }
        }
        
        function displayRequirements(data) {
            let html = '';
            
            // PHP Version
            html += createCheckItem('Versão PHP', data.php.current, data.php.status);
            
            // Extensions
            html += '<h5 class="mt-3">Extensões PHP</h5>';
            for (const [ext, info] of Object.entries(data.extensions)) {
                if (typeof info === 'object' && info.status !== undefined) {
                    html += createCheckItem(ext, info.status ? 'Instalada' : 'Não instalada', info.status);
                }
            }
            
            // Permissions
            html += '<h5 class="mt-3">Permissões</h5>';
            for (const [dir, info] of Object.entries(data.permissions)) {
                if (typeof info === 'object' && info.status !== undefined) {
                    html += createCheckItem(dir, info.message, info.status);
                }
            }
            
            // Composer
            html += '<h5 class="mt-3">Composer</h5>';
            html += createCheckItem('Composer', data.composer.message, data.composer.status);
            
            document.getElementById('requirementsList').innerHTML = html;
        }
        
        function createCheckItem(name, status, ok) {
            const cssClass = ok ? 'ok' : 'error';
            const icon = ok ? 'bi-check-circle' : 'bi-x-circle';
            return `
                <div class="check-item ${cssClass}">
                    <div>
                        <i class="bi ${icon} me-2"></i>
                        <strong>${name}</strong>
                    </div>
                    <div>${status}</div>
                </div>
            `;
        }
        
        // Form submit
        document.getElementById('installForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Show progress
            document.getElementById('installForm').style.display = 'none';
            document.getElementById('installProgress').style.display = 'block';
            
            try {
                const response = await fetch('install_simple.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'action=install&' + new URLSearchParams(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    displayProgress(result.steps);
                    setTimeout(() => {
                        showSuccess();
                    }, 2000);
                } else {
                    showError(result.message);
                }
            } catch (error) {
                showError('Erro na instalação: ' + error.message);
            }
        });
        
        function displayProgress(steps) {
            const container = document.getElementById('progressSteps');
            let html = '';
            
            steps.forEach(step => {
                const cssClass = step.status === 'success' ? 'success' : 
                                 step.status === 'error' ? 'error' : '';
                html += `
                    <div class="progress-step ${cssClass}">
                        <div>
                            <i class="bi ${step.status === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                            ${step.message}
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }
        
        function showSuccess() {
            document.getElementById('installProgress').style.display = 'none';
            document.getElementById('installResult').innerHTML = `
                <div class="text-center">
                    <div style="font-size: 4rem; color: #28a745;">🎉</div>
                    <h3 class="mt-3">Instalação Concluída!</h3>
                    <p>Seu sistema foi instalado com sucesso.</p>
                    <a href="/admin" class="btn btn-primary">
                        <i class="bi bi-arrow-right"></i> Acessar Sistema
                    </a>
                </div>
            `;
            document.getElementById('installResult').style.display = 'block';
        }
        
        function showError(message) {
            document.getElementById('installProgress').style.display = 'none';
            document.getElementById('installResult').innerHTML = `
                <div class="text-center">
                    <div style="font-size: 4rem; color: #dc3545;">❌</div>
                    <h3 class="mt-3">Erro na Instalação</h3>
                    <p>${message}</p>
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Tentar Novamente
                    </button>
                </div>
            `;
            document.getElementById('installResult').style.display = 'block';
        }
    </script>
</body>
</html>
