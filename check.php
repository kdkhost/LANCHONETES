<?php
/**
 * 🔍 Verificador Profissional de Requisitos
 * Interface moderna e robusta para verificar requisitos do sistema
 */

// Configurações
$requirements = [
    'php_version' => [
        'required' => '8.4.0',
        'current' => PHP_VERSION,
        'name' => 'Versão PHP'
    ],
    'extensions' => [
        'pdo' => 'PDO',
        'pdo_mysql' => 'PDO MySQL',
        'mbstring' => 'Multibyte String',
        'curl' => 'cURL',
        'zip' => 'ZIP',
        'gd' => 'GD',
        'bcmath' => 'BCMath',
        'xml' => 'XML',
        'fileinfo' => 'File Information',
        'json' => 'JSON',
        'session' => 'Session',
        'tokenizer' => 'Tokenizer'
    ],
    'php_ini' => [
        'memory_limit' => ['min' => '256M', 'current' => ini_get('memory_limit')],
        'max_execution_time' => ['min' => '300', 'current' => ini_get('max_execution_time')],
        'upload_max_filesize' => ['min' => '64M', 'current' => ini_get('upload_max_filesize')],
        'post_max_size' => ['min' => '64M', 'current' => ini_get('post_max_size')],
        'max_input_vars' => ['min' => '3000', 'current' => ini_get('max_input_vars')]
    ],
    'directories' => [
        'storage' => 'Storage',
        'storage/logs' => 'Logs',
        'storage/framework' => 'Framework Cache',
        'bootstrap/cache' => 'Bootstrap Cache',
        'public' => 'Public Uploads'
    ]
];

function checkVersion($required, $current) {
    return version_compare($current, $required, '>=');
}

function formatBytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

function checkPhpIni($min, $current) {
    return formatBytes($current) >= formatBytes($min);
}

// Verificar requisitos
$status = [
    'php' => checkVersion($requirements['php_version']['required'], $requirements['php_version']['current']),
    'extensions' => true,
    'php_ini' => true,
    'directories' => true,
    'composer' => false
];

// Verificar extensões
$missingExtensions = [];
foreach ($requirements['extensions'] as $ext => $name) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $name;
        $status['extensions'] = false;
    }
}

// Verificar php.ini
$iniProblems = [];
foreach ($requirements['php_ini'] as $setting => $values) {
    if (!checkPhpIni($values['min'], $values['current'])) {
        $iniProblems[] = $setting;
        $status['php_ini'] = false;
    }
}

// Verificar diretórios
$dirProblems = [];
foreach ($requirements['directories'] as $dir => $name) {
    if (!is_dir($dir) || !is_writable($dir)) {
        $dirProblems[] = $name;
        $status['directories'] = false;
    }
}

// Verificar Composer
$composerAvailable = false;
$output = shell_exec('composer --version 2>&1');
if (!empty($output) && strpos($output, 'Composer') !== false) {
    $composerAvailable = true;
    $status['composer'] = true;
}

$allOk = $status['php'] && $status['extensions'] && $status['directories'] && $status['composer'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 Verificador Profissional - Sistema de Lanchonetes</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
            --secondary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            --danger-gradient: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .check-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
            margin-bottom: 30px;
            animation: slideInDown 0.8s ease;
        }
        
        .header-icon {
            font-size: 4rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }
        
        .requirements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .requirement-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
            animation: slideInUp 0.8s ease;
            transition: all 0.3s ease;
        }
        
        .requirement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .requirement-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .requirement-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 15px;
        }
        
        .requirement-icon.success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .requirement-icon.error {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            color: white;
        }
        
        .requirement-icon.warning {
            background: linear-gradient(135deg, #ffc107, #ff9800);
            color: white;
        }
        
        .requirement-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .check-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .check-item:last-child {
            border-bottom: none;
        }
        
        .check-name {
            font-weight: 500;
            color: #495057;
        }
        
        .check-status {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-badge.success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-badge.warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .summary-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
            animation: slideInUp 0.8s ease 0.2s both;
        }
        
        .summary-card.all-ok {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .summary-card.has-errors {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            color: white;
        }
        
        .summary-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .btn-gradient {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin: 10px;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255,107,53,0.3);
            color: white;
        }
        
        .btn-success-gradient {
            background: var(--success-gradient);
        }
        
        .btn-success-gradient:hover {
            box-shadow: 0 10px 25px rgba(40,167,69,0.3);
        }
        
        .btn-danger-gradient {
            background: var(--danger-gradient);
        }
        
        .btn-danger-gradient:hover {
            box-shadow: 0 10px 25px rgba(220,53,69,0.3);
        }
        
        .instructions-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-top: 30px;
        }
        
        .instructions-title {
            color: #2c3e50;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .instructions-list {
            list-style: none;
            padding: 0;
        }
        
        .instructions-list li {
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: flex-start;
        }
        
        .instructions-list li:last-child {
            border-bottom: none;
        }
        
        .instructions-list i {
            color: #FF6B35;
            margin-right: 12px;
            margin-top: 2px;
        }
        
        .code-block {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            margin: 10px 0;
        }
        
        @keyframes slideInDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
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
        
        @media (max-width: 768px) {
            .requirements-grid {
                grid-template-columns: 1fr;
            }
            
            .header-card, .requirement-card, .summary-card {
                padding: 20px;
            }
            
            .header-icon {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="check-container">
        <!-- Header -->
        <div class="header-card">
            <div class="header-icon">
                <i class="bi bi-shield-check"></i>
            </div>
            <h1 class="mb-3">🔍 Verificador Profissional</h1>
            <p class="text-muted mb-0">Análise completa dos requisitos do sistema</p>
        </div>
        
        <!-- Requirements Grid -->
        <div class="requirements-grid">
            <!-- PHP Version -->
            <div class="requirement-card">
                <div class="requirement-header">
                    <div class="requirement-icon <?= $status['php'] ? 'success' : 'error' ?>">
                        <i class="bi bi-<?= $status['php'] ? 'check' : 'x' ?>"></i>
                    </div>
                    <div class="requirement-title">Versão PHP</div>
                </div>
                <div class="check-item">
                    <span class="check-name">Versão Atual</span>
                    <div class="check-status">
                        <span><?= $requirements['php_version']['current'] ?></span>
                        <span class="status-badge <?= $status['php'] ? 'success' : 'error' ?>">
                            <?= $status['php'] ? 'OK' : 'Incompatível' ?>
                        </span>
                    </div>
                </div>
                <div class="check-item">
                    <span class="check-name">Mínima Requerida</span>
                    <div class="check-status">
                        <span><?= $requirements['php_version']['required'] ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Extensions -->
            <div class="requirement-card">
                <div class="requirement-header">
                    <div class="requirement-icon <?= $status['extensions'] ? 'success' : 'error' ?>">
                        <i class="bi bi-<?= $status['extensions'] ? 'check' : 'x' ?>"></i>
                    </div>
                    <div class="requirement-title">Extensões PHP</div>
                </div>
                <?php foreach ($requirements['extensions'] as $ext => $name): ?>
                <div class="check-item">
                    <span class="check-name"><?= $name ?></span>
                    <div class="check-status">
                        <span class="status-badge <?= extension_loaded($ext) ? 'success' : 'error' ?>">
                            <?= extension_loaded($ext) ? 'Instalada' : 'Faltando' ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- PHP Configuration -->
            <div class="requirement-card">
                <div class="requirement-header">
                    <div class="requirement-icon <?= $status['php_ini'] ? 'success' : 'warning' ?>">
                        <i class="bi bi-<?= $status['php_ini'] ? 'check' : 'exclamation' ?>"></i>
                    </div>
                    <div class="requirement-title">Configurações php.ini</div>
                </div>
                <?php foreach ($requirements['php_ini'] as $setting => $values): ?>
                <?php
                $isOk = checkPhpIni($values['min'], $values['current']);
                ?>
                <div class="check-item">
                    <span class="check-name"><?= str_replace('_', ' ', ucfirst($setting)) ?></span>
                    <div class="check-status">
                        <span><?= $values['current'] ?></span>
                        <span class="status-badge <?= $isOk ? 'success' : 'warning' ?>">
                            <?= $isOk ? 'OK' : 'Recomendado: ' . $values['min'] ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Directory Permissions -->
            <div class="requirement-card">
                <div class="requirement-header">
                    <div class="requirement-icon <?= $status['directories'] ? 'success' : 'error' ?>">
                        <i class="bi bi-<?= $status['directories'] ? 'check' : 'x' ?>"></i>
                    </div>
                    <div class="requirement-title">Permissões de Diretórios</div>
                </div>
                <?php foreach ($requirements['directories'] as $dir => $name): ?>
                <?php
                $writable = is_dir($dir) && is_writable($dir);
                ?>
                <div class="check-item">
                    <span class="check-name"><?= $name ?></span>
                    <div class="check-status">
                        <span class="status-badge <?= $writable ? 'success' : 'error' ?>">
                            <?= $writable ? 'Escrita OK' : 'Sem permissão' ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Composer -->
            <div class="requirement-card">
                <div class="requirement-header">
                    <div class="requirement-icon <?= $status['composer'] ? 'success' : 'error' ?>">
                        <i class="bi bi-<?= $status['composer'] ? 'check' : 'x' ?>"></i>
                    </div>
                    <div class="requirement-title">Composer</div>
                </div>
                <div class="check-item">
                    <span class="check-name">Gerenciador de Pacotes</span>
                    <div class="check-status">
                        <span class="status-badge <?= $composerAvailable ? 'success' : 'error' ?>">
                            <?= $composerAvailable ? 'Disponível' : 'Não encontrado' ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Summary -->
        <div class="summary-card <?= $allOk ? 'all-ok' : 'has-errors' ?>">
            <div class="summary-icon">
                <i class="bi bi-<?= $allOk ? 'check-circle' : 'exclamation-triangle' ?>"></i>
            </div>
            <h3>
                <?= $allOk ? '🎉 Todos os Requisitos OK!' : '⚠️ Requisitos Precisam de Ajuste' ?>
            </h3>
            
            <?php if ($allOk): ?>
            <p class="mb-4">Seu servidor está pronto para instalar o sistema!</p>
            <div style="margin-top: 30px;">
                <a href="install.php" class="btn-gradient">
                    <i class="bi bi-play"></i> Instalar Sistema Agora
                </a>
                <a href="https://getcomposer.org/download/" class="btn-gradient" target="_blank">
                    <i class="bi bi-download"></i> Baixar Composer
                </a>
            </div>
            <?php else: ?>
            <p class="mb-4">Por favor, ajuste os requisitos listados acima antes de prosseguir.</p>
            
            <?php if (!empty($missingExtensions)): ?>
            <div class="instructions-card">
                <h5 class="instructions-title">📦 Para instalar extensões faltantes:</h5>
                <ul class="instructions-list">
                    <li><i class="bi bi-terminal"></i>
                        <div>
                            <strong>Ubuntu/Debian:</strong>
                            <div class="code-block">sudo apt install php8.4-<?= implode(' php8.4-', array_keys($missingExtensions)) ?></div>
                        </div>
                    </li>
                    <li><i class="bi bi-terminal"></i>
                        <div>
                            <strong>CentOS/RHEL:</strong>
                            <div class="code-block">sudo yum install php8.4-<?= implode(' php8.4-', array_keys($missingExtensions)) ?></div>
                        </div>
                    </li>
                    <li><i class="bi bi-gear"></i>
                        <div>
                            <strong>Windows:</strong> Edite php.ini e descomente as linhas
                            <div class="code-block">extension=<?= implode('.dll', $missingExtensions) ?></div>
                        </div>
                    </li>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($dirProblems)): ?>
            <div class="instructions-card">
                <h5 class="instructions-title">📁 Para corrigir permissões:</h5>
                <ul class="instructions-list">
                    <li><i class="bi bi-terminal"></i>
                        <div>
                            <strong>Linux/Mac:</strong>
                            <div class="code-block">chmod -R 755 storage bootstrap/cache</div>
                            <div class="code-block">chown -R www-data:www-data storage bootstrap/cache</div>
                        </div>
                    </li>
                    <li><i class="bi bi-gear"></i>
                        <div>
                            <strong>Windows:</strong> Clique com botão direito nas pastas → Propriedades → Segurança → Editar permissões
                        </div>
                    </li>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php if (!$composerAvailable): ?>
            <div class="instructions-card">
                <h5 class="instructions-title">🎼 Para instalar Composer:</h5>
                <ul class="instructions-list">
                    <li><i class="bi bi-download"></i>
                        <div>
                            <strong>Windows:</strong> <a href="https://getcomposer.org/Composer-Setup.exe" target="_blank">Baixe o instalador</a>
                        </div>
                    </li>
                    <li><i class="bi bi-terminal"></i>
                        <div>
                            <strong>Linux/Mac:</strong>
                            <div class="code-block">curl -sS https://getcomposer.org/installer | php</div>
                            <div class="code-block">mv composer.phar /usr/local/bin/composer</div>
                        </div>
                    </li>
                </ul>
            </div>
            <?php endif; ?>
            
            <div style="margin-top: 30px;">
                <button class="btn-gradient" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise"></i> Verificar Novamente
                </button>
                <a href="https://www.php.net/manual/pt_BR/install.php" class="btn-gradient" target="_blank">
                    <i class="bi bi-book"></i> Ajuda PHP
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-refresh every 30 seconds if there are errors
        <?php if (!$allOk): ?>
        setTimeout(() => {
            location.reload();
        }, 30000);
        <?php endif; ?>
        
        // Add smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
