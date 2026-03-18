<?php
// Instalador Ultra-Simples
if ($_POST['action'] ?? false) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'check') {
        // Verificar PHP
        $phpOk = version_compare(PHP_VERSION, '8.4.0', '>=');
        
        // Verificar extensões
        $extensions = ['pdo', 'pdo_mysql', 'mbstring', 'curl', 'zip', 'gd', 'bcmath', 'xml', 'fileinfo'];
        $extOk = true;
        foreach ($extensions as $ext) {
            if (!extension_loaded($ext)) $extOk = false;
        }
        
        // Verificar permissões
        $dirs = ['storage', 'storage/logs', 'storage/framework', 'bootstrap/cache', 'public'];
        $permOk = true;
        foreach ($dirs as $dir) {
            if (!is_dir($dir) || !is_writable($dir)) $permOk = false;
        }
        
        // Verificar Composer
        $composer = shell_exec('composer --version 2>&1');
        $composerOk = !empty($composer) && strpos($composer, 'Composer') !== false;
        
        echo json_encode([
            'success' => true,
            'php' => ['status' => $phpOk, 'version' => PHP_VERSION],
            'extensions' => ['status' => $extOk],
            'permissions' => ['status' => $permOk],
            'composer' => ['status' => $composerOk],
            'all_ok' => $phpOk && $extOk && $permOk && $composerOk
        ]);
        exit;
    }
    
    if ($_POST['action'] === 'install') {
        try {
            // Criar .env
            if (!file_exists('.env') && file_exists('.env.example')) {
                copy('.env.example', '.env');
            }
            
            // Atualizar .env
            if (file_exists('.env')) {
                $content = file_get_contents('.env');
                $content = preg_replace('/^DB_HOST=.*/m', 'DB_HOST=' . $_POST['db_host'], $content);
                $content = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=' . $_POST['db_database'], $content);
                $content = preg_replace('/^DB_USERNAME=.*/m', 'DB_USERNAME=' . $_POST['db_username'], $content);
                $content = preg_replace('/^DB_PASSWORD=.*/m', 'DB_PASSWORD=' . $_POST['db_password'], $content);
                file_put_contents('.env', $content);
            }
            
            // Executar comandos
            $steps = [];
            exec('php artisan key:generate --force', $output, $code);
            $steps[] = ['msg' => 'Chave gerada', 'ok' => $code === 0];
            
            exec('composer install --no-dev', $output, $code);
            $steps[] = ['msg' => 'Dependências instaladas', 'ok' => $code === 0];
            
            exec('php artisan storage:link', $output, $code);
            $steps[] = ['msg' => 'Storage linkado', 'ok' => $code === 0];
            
            exec('php artisan migrate --force', $output, $code);
            $steps[] = ['msg' => 'Migrations executadas', 'ok' => $code === 0];
            
            // Criar usuário admin
            if (file_exists('vendor/autoload.php')) {
                require_once 'vendor/autoload.php';
                $app = require_once 'bootstrap/app.php';
                $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
                $kernel->bootstrap();
                
                $user = new \App\Models\User();
                $user->name = $_POST['admin_name'];
                $user->email = $_POST['admin_email'];
                $user->password = \Hash::make($_POST['admin_password']);
                $user->role = 'super_admin';
                $user->email_verified_at = now();
                $user->save();
                $steps[] = ['msg' => 'Usuário admin criado', 'ok' => true];
            }
            
            echo json_encode(['success' => true, 'steps' => $steps]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Instalador Sistema</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #FF6B35, #F7931E);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 { font-size: 2rem; margin-bottom: 10px; }
        .content { padding: 40px; }
        .step { display: none; }
        .step.active { display: block; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; }
        .form-group input:focus { border-color: #FF6B35; outline: none; }
        .row { display: flex; gap: 20px; }
        .row .col { flex: 1; }
        .btn {
            background: linear-gradient(135deg, #FF6B35, #F7931E);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
            transition: transform 0.2s;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn-secondary { background: #6c757d; }
        .check-item {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .check-item.ok { background: #d4edda; }
        .check-item.error { background: #f8d7da; }
        .progress-step {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .progress-step.success { background: #d4edda; }
        .progress-step.error { background: #f8d7da; }
        .text-center { text-align: center; }
        .mt-3 { margin-top: 20px; }
        .mb-3 { margin-bottom: 20px; }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-info { background: #d1ecf1; color: #0c5460; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-danger { background: #f8d7da; color: #721c24; }
        .hidden { display: none; }
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #FF6B35;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @media (max-width: 768px) {
            .row { flex-direction: column; }
            .header, .content { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏪 Sistema de Lanchonetes</h1>
            <p>Instalador Automático</p>
        </div>
        
        <div class="content">
            <!-- Step 1: Welcome -->
            <div class="step active" id="step1">
                <h2>🎉 Bem-vindo!</h2>
                <p>Vamos instalar seu sistema em poucos minutos.</p>
                <div class="alert alert-info">
                    ℹ️ Certifique-se de que seu servidor atende aos requisitos.
                </div>
                <div class="text-center">
                    <button class="btn" onclick="goToStep(2)">→ Começar</button>
                </div>
            </div>
            
            <!-- Step 2: Check -->
            <div class="step" id="step2">
                <h2>🔍 Verificando Requisitos</h2>
                <div id="requirements">
                    <div class="spinner"></div>
                    <p class="text-center">Verificando...</p>
                </div>
                <div class="mt-3 text-center">
                    <button class="btn btn-secondary" onclick="goToStep(1)">← Anterior</button>
                    <button class="btn" id="continueBtn" onclick="goToStep(3)" disabled>→ Continuar</button>
                </div>
            </div>
            
            <!-- Step 3: Install -->
            <div class="step" id="step3">
                <h2>⚙️ Configuração</h2>
                <form id="installForm">
                    <div class="row">
                        <div class="col">
                            <h3>Banco de Dados</h3>
                            <div class="form-group">
                                <label>Host</label>
                                <input name="db_host" value="localhost" required>
                            </div>
                            <div class="form-group">
                                <label>Banco</label>
                                <input name="db_database" required>
                            </div>
                            <div class="form-group">
                                <label>Usuário</label>
                                <input name="db_username" required>
                            </div>
                            <div class="form-group">
                                <label>Senha</label>
                                <input type="password" name="db_password" required>
                            </div>
                        </div>
                        <div class="col">
                            <h3>Usuário Admin</h3>
                            <div class="form-group">
                                <label>Nome</label>
                                <input name="admin_name" value="Administrador" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="admin_email" value="admin@localhost" required>
                            </div>
                            <div class="form-group">
                                <label>Senha</label>
                                <input type="password" name="admin_password" value="admin123" required>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <button class="btn btn-secondary" onclick="goToStep(2)">← Anterior</button>
                        <button type="submit" class="btn">▶️ Instalar</button>
                    </div>
                </form>
                
                <div id="progress" class="hidden">
                    <h3>🚀 Instalando...</h3>
                    <div id="progressSteps"></div>
                </div>
                
                <div id="result" class="hidden"></div>
            </div>
        </div>
    </div>
    
    <script>
        let currentStep = 1;
        
        function goToStep(step) {
            document.querySelectorAll('.step').forEach(el => el.classList.remove('active'));
            document.getElementById('step' + step).classList.add('active');
            currentStep = step;
            
            if (step === 2) {
                checkRequirements();
            }
        }
        
        const installerEndpoint = window.location.pathname.replace(/\/[^\/]*$/, '/install.php');

        async function checkRequirements() {
            try {
                const response = await fetch(installerEndpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=check'
                });

                if (!response.ok) {
                    throw new Error('Servidor respondeu com status ' + response.status);
                }

                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (jsonError) {
                    throw new Error('Resposta inválida do servidor: ' + text.substring(0, 120));
                }

                if (data.success) {
                    displayRequirements(data);
                    document.getElementById('continueBtn').disabled = !data.all_ok;
                }
            } catch (error) {
                document.getElementById('requirements').innerHTML =
                    '<div class="alert alert-danger">Erro: ' + error.message + '</div>';
            }
        }
        
        function displayRequirements(data) {
            let html = '';
            
            html += createCheckItem('PHP ' + data.php.version, data.php.status);
            html += createCheckItem('Extensões PHP', data.extensions.status);
            html += createCheckItem('Permissões', data.permissions.status);
            html += createCheckItem('Composer', data.composer.status);
            
            document.getElementById('requirements').innerHTML = html;
        }
        
        function createCheckItem(name, ok) {
            return `
                <div class="check-item ${ok ? 'ok' : 'error'}">
                    <span><strong>${name}</strong></span>
                    <span>${ok ? '✅' : '❌'}</span>
                </div>
            `;
        }
        
        document.getElementById('installForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            document.getElementById('installForm').classList.add('hidden');
            document.getElementById('progress').classList.remove('hidden');
            
            try {
                const response = await fetch(installerEndpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=install&' + new URLSearchParams(data)
            });

            if (!response.ok) {
                throw new Error('Servidor respondeu com status ' + response.status);
            }

            const text = await response.text();
            let result;
            try {
                result = JSON.parse(text);
            } catch (jsonError) {
                throw new Error('Resposta inválida do servidor: ' + text.substring(0, 120));
            }

            if (result.success) {
                displayProgress(result.steps);
                setTimeout(() => showSuccess(), 3000);
            } else {
                    showError(result.error);
                }
            } catch (error) {
                showError(error.message);
            }
        });
        
        function displayProgress(steps) {
            let html = '';
            steps.forEach(step => {
                html += `
                    <div class="progress-step ${step.ok ? 'success' : 'error'}">
                        <span>${step.msg}</span>
                        <span>${step.ok ? '✅' : '❌'}</span>
                    </div>
                `;
            });
            document.getElementById('progressSteps').innerHTML = html;
        }
        
        function showSuccess() {
            document.getElementById('progress').classList.add('hidden');
            document.getElementById('result').innerHTML = `
                <div class="text-center">
                    <h2 style="font-size: 4rem;">🎉</h2>
                    <h3>Instalação Concluída!</h3>
                    <p>Seu sistema foi instalado com sucesso.</p>
                    <a href="/admin" class="btn">→ Acessar Sistema</a>
                </div>
            `;
            document.getElementById('result').classList.remove('hidden');
        }
        
        function showError(message) {
            document.getElementById('progress').classList.add('hidden');
            document.getElementById('result').innerHTML = `
                <div class="text-center">
                    <h2 style="font-size: 4rem;">❌</h2>
                    <h3>Erro na Instalação</h3>
                    <p>${message}</p>
                    <button class="btn" onclick="location.reload()">← Tentar Novamente</button>
                </div>
            `;
            document.getElementById('result').classList.remove('hidden');
        }
    </script>
</body>
</html>
