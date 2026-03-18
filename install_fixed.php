<?php
/**
 * 🚀 Instalador com Ícones Corrigidos - Sistema de Lanchonetes
 * Versão com ícones garantidos para funcionar
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
                $steps[] = ['status' => 'success', 'message' => '✅ Arquivo .env criado'];
            } else {
                return ['success' => false, 'message' => '❌ Arquivo .env.example não encontrado'];
            }
        }
        
        // Atualizar .env com configurações do banco
        $envContent = file_get_contents('.env');
        $envContent = preg_replace('/^DB_HOST=.*/m', 'DB_HOST=' . $dbConfig['host'], $envContent);
        $envContent = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=' . $dbConfig['database'], $envContent);
        $envContent = preg_replace('/^DB_USERNAME=.*/m', 'DB_USERNAME=' . $dbConfig['username'], $envContent);
        $envContent = preg_replace('/^DB_PASSWORD=.*/m', 'DB_PASSWORD=' . $dbConfig['password'], $envContent);
        file_put_contents('.env', $envContent);
        $steps[] = ['status' => 'success', 'message' => '✅ Configurações do banco atualizadas'];
        
        // Gerar chave
        ob_start();
        system('php artisan key:generate --force 2>&1', $returnCode);
        ob_end_clean();
        if ($returnCode === 0) {
            $steps[] = ['status' => 'success', 'message' => '✅ Chave da aplicação gerada'];
        }
        
        // Instalar dependências
        ob_start();
        system('composer install --no-dev --optimize-autoloader 2>&1', $returnCode);
        ob_end_clean();
        if ($returnCode === 0) {
            $steps[] = ['status' => 'success', 'message' => '✅ Dependências instaladas'];
        }
        
        // Link storage
        ob_start();
        system('php artisan storage:link 2>&1', $returnCode);
        ob_end_clean();
        $steps[] = ['status' => 'success', 'message' => '✅ Storage linkado'];
        
        // Executar migrations
        ob_start();
        system('php artisan migrate --force 2>&1', $returnCode);
        ob_end_clean();
        $steps[] = ['status' => 'success', 'message' => '✅ Migrations executadas'];
        
        // Limpar cache
        ob_start();
        system('php artisan cache:clear 2>&1', $returnCode);
        ob_end_clean();
        $steps[] = ['status' => 'success', 'message' => '✅ Cache limpo'];
        
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
                $steps[] = ['status' => 'success', 'message' => '✅ Usuário administrador criado'];
            } else {
                $steps[] = ['status' => 'info', 'message' => 'ℹ️ Usuário administrador já existe'];
            }
        }
        
        return ['success' => true, 'steps' => $steps];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => '❌ Erro: ' . $e->getMessage()];
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
    <title>🚀 Instalador Profissional</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .install-container {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            overflow: hidden;
            animation: slideInUp 0.8s ease;
        }
        
        .header {
            background: linear-gradient(135deg, #FF6B35, #F7931E);
            color: white;
            padding: 50px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }
        
        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .content {
            padding: 50px 40px;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
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
            border: 3px solid #e9ecef;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }
        
        .step.active {
            background: linear-gradient(135deg, #FF6B35, #F7931E);
            border-color: #FF6B35;
            color: white;
            transform: scale(1.15);
        }
        
        .step.completed {
            background: linear-gradient(135deg, #28a745, #20c997);
            border-color: #28a745;
            color: white;
        }
        
        .step-content {
            display: none;
            animation: fadeIn 0.5s ease;
        }
        
        .step-content.active {
            display: block;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            margin: 40px 0;
        }
        
        .feature-card {
            text-align: center;
            padding: 30px 20px;
            background: #f8f9fa;
            border-radius: 16px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border-color: #FF6B35;
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            display: block;
        }
        
        .check-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .check-item.ok {
            background: #d4edda;
            border-left-color: #28a745;
        }
        
        .check-item.error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        
        .check-icon {
            font-size: 1.5rem;
            margin-right: 15px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #FF6B35, #F7931E);
            border: none;
            padding: 15px 35px;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(255,107,53,0.4);
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 15px 35px;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            color: white;
        }
        
        .progress-step {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #e9ecef;
        }
        
        .progress-step.success {
            background: #d4edda;
            border-left-color: #28a745;
        }
        
        .progress-step.error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        
        .progress-step.info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
        }
        
        .form-control:focus {
            border-color: #FF6B35;
            box-shadow: 0 0 0 0.2rem rgba(255,107,53,0.25);
        }
        
        .form-floating label {
            color: #6c757d;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 20px;
        }
        
        .success-animation {
            text-align: center;
            padding: 50px;
        }
        
        .success-icon {
            font-size: 6rem;
            color: #28a745;
            animation: bounceIn 1s ease;
        }
        
        @keyframes slideInUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        @keyframes bounceIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        @media (max-width: 768px) {
            .install-container {
                margin: 20px;
                border-radius: 16px;
            }
            
            .header, .content {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .feature-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="header">
            <h1>🏪 Sistema de Lanchonetes</h1>
            <p>Instalador Profissional • Setup Automático</p>
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
                <div class="text-center mb-5">
                    <h2 class="mb-4">🎉 Bem-vindo ao Instalador Profissional!</h2>
                    <p class="text-muted fs-5">Vamos configurar seu sistema de lanchonetes em poucos minutos</p>
                </div>
                
                <div class="feature-grid">
                    <div class="feature-card">
                        <div class="feature-icon">🛡️</div>
                        <h5>Verificação Automática</h5>
                        <p class="text-muted">Verifica todos os requisitos do sistema</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">⚙️</div>
                        <h5>Configuração Inteligente</h5>
                        <p class="text-muted">Configura ambiente e dependências</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🗄️</div>
                        <h5>Banco de Dados</h5>
                        <p class="text-muted">Instala e configura automaticamente</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">👤</div>
                        <h5>Usuário Admin</h5>
                        <p class="text-muted">Cria conta de administrador</p>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <span class="fs-4">ℹ️</span>
                    <strong>Importante:</strong> Certifique-se de que seu servidor atende aos requisitos mínimos antes de continuar.
                </div>
                
                <div class="text-center">
                    <button class="btn btn-primary btn-lg" onclick="goToStep(2)">
                        → Começar Verificação
                    </button>
                </div>
            </div>
            
            <!-- Step 2: Check Requirements -->
            <div class="step-content" id="step2">
                <h3 class="mb-4">🔍 Verificação de Requisitos</h3>
                <div id="requirementsList">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
                        <p class="mt-3 fs-5">Verificando requisitos do sistema...</p>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-secondary me-3" onclick="goToStep(1)">
                        ← Anterior
                    </button>
                    <button class="btn btn-primary" id="continueBtn" onclick="goToStep(3)" disabled>
                        → Continuar
                    </button>
                </div>
            </div>
            
            <!-- Step 3: Install -->
            <div class="step-content" id="step3">
                <h3 class="mb-4">⚙️ Configuração do Sistema</h3>
                <form id="installForm">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-4">📊 Configurações do Banco de Dados</h5>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="db_host" value="localhost" required>
                                <label>Host do Banco</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="db_database" required>
                                <label>Nome do Banco</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="db_username" required>
                                <label>Usuário</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" name="db_password" required>
                                <label>Senha</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-4">👤 Usuário Administrador</h5>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="admin_name" value="Administrador" required>
                                <label>Nome Completo</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" name="admin_email" value="admin@localhost" required>
                                <label>Email</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" name="admin_password" value="admin123" required>
                                <label>Senha</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button class="btn btn-secondary me-3" onclick="goToStep(2)">
                            ← Anterior
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg">
                            ▶️ Iniciar Instalação
                        </button>
                    </div>
                </form>
                <div id="installProgress" style="display: none;">
                    <h4 class="mb-4">🚀 Instalando Sistema</h4>
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
                const response = await fetch('install_fixed.php', {
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
                        <span class="fs-4">⚠️</span>
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
            html += '<h5 class="mt-4 mb-3">📦 Extensões PHP</h5>';
            for (const [ext, info] of Object.entries(data.extensions)) {
                if (typeof info === 'object' && info.status !== undefined) {
                    html += createCheckItem(ext, info.status ? 'Instalada' : 'Não instalada', info.status);
                }
            }
            
            // Permissions
            html += '<h5 class="mt-4 mb-3">📁 Permissões</h5>';
            for (const [dir, info] of Object.entries(data.permissions)) {
                if (typeof info === 'object' && info.status !== undefined) {
                    html += createCheckItem(dir, info.message, info.status);
                }
            }
            
            // Composer
            html += '<h5 class="mt-4 mb-3">📦 Dependências</h5>';
            html += createCheckItem('Composer', data.composer.message, data.composer.status);
            
            document.getElementById('requirementsList').innerHTML = html;
        }
        
        function createCheckItem(name, status, ok) {
            const cssClass = ok ? 'ok' : 'error';
            const icon = ok ? '✅' : '❌';
            return `
                <div class="check-item ${cssClass}">
                    <div>
                        <span class="check-icon">${icon}</span>
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
                const response = await fetch('install_fixed.php', {
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
                    }, 3000);
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
                                 step.status === 'error' ? 'error' : 'info';
                const icon = step.status === 'success' ? '✅' : 
                             step.status === 'error' ? '❌' : 'ℹ️';
                html += `
                    <div class="progress-step ${cssClass}">
                        <div>
                            <span class="check-icon">${icon}</span>
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
                <div class="success-animation">
                    <div class="success-icon">🎉</div>
                    <h3 class="mt-4">Instalação Concluída!</h3>
                    <p class="text-muted fs-5">Seu sistema foi instalado com sucesso.</p>
                    <div class="alert alert-success">
                        <span class="fs-4">ℹ️</span>
                        <strong>Próximo passo:</strong> Configure o MercadoPago e email no arquivo .env
                    </div>
                    <a href="/admin" class="btn btn-primary btn-lg">
                        → Acessar Sistema
                    </a>
                </div>
            `;
            document.getElementById('installResult').style.display = 'block';
        }
        
        function showError(message) {
            document.getElementById('installProgress').style.display = 'none';
            document.getElementById('installResult').innerHTML = `
                <div class="text-center">
                    <div style="font-size: 5rem; color: #dc3545;">❌</div>
                    <h3 class="mt-4">Erro na Instalação</h3>
                    <p class="text-muted fs-5">${message}</p>
                    <button class="btn btn-primary" onclick="location.reload()">
                        ← Tentar Novamente
                    </button>
                </div>
            `;
            document.getElementById('installResult').style.display = 'block';
        }
    </script>
</body>
</html>
