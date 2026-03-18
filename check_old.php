<?php
/**
 * Verificador de Requisitos do Sistema
 * Acesso via web: http://localhost/check.php
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
        $status['directories'] = = false;
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
    <title>Verificador de Requisitos - Sistema de Lanchonetes</title>
    <style>
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
        
        .container {
            max-width: 900px;
            margin: 0 auto;
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
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .status-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
        }
        
        .status-ok {
            background: #28a745;
        }
        
        .status-error {
            background: #dc3545;
        }
        
        .status-warning {
            background: #ffc107;
            color: #333;
        }
        
        .check-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin: 10px 0;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #e9ecef;
        }
        
        .check-item.ok {
            border-left-color: #28a745;
            background: #d4edda;
        }
        
        .check-item.error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        
        .check-item.warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        
        .check-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .check-value {
            color: #6c757d;
            font-family: monospace;
        }
        
        .summary {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin-top: 40px;
        }
        
        .summary h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .summary.all-ok h3 {
            color: #28a745;
        }
        
        .summary.has-errors h3 {
            color: #dc3545;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            margin: 10px;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(40,167,69,0.3);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #ff9800);
            color: #333;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            color: white;
        }
        
        .instructions {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .instructions h4 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .instructions ul {
            list-style: none;
            padding: 0;
        }
        
        .instructions li {
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .instructions li:last-child {
            border-bottom: none;
        }
        
        .instructions code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .content {
                padding: 20px;
            }
            
            .check-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Verificador de Requisitos</h1>
            <p>Sistema de Lanchonetes Multiloja</p>
        </div>
        
        <div class="content">
            <!-- Versão PHP -->
            <div class="section">
                <h2>
                    <div class="status-icon <?= $status['php'] ? 'status-ok' : 'status-error' ?>">
                        <?= $status['php'] ? '✓' : '✗' ?>
                    </div>
                    Versão PHP
                </h2>
                <div class="check-item <?= $status['php'] ? 'ok' : 'error' ?>">
                    <div>
                        <div class="check-name">Versão Atual</div>
                        <div class="check-value"><?= $requirements['php_version']['current'] ?></div>
                    </div>
                    <div>
                        <div class="check-name">Mínima Requerida</div>
                        <div class="check-value"><?= $requirements['php_version']['required'] ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Extensões PHP -->
            <div class="section">
                <h2>
                    <div class="status-icon <?= $status['extensions'] ? 'status-ok' : 'status-error' ?>">
                        <?= $status['extensions'] ? '✓' : '✗' ?>
                    </div>
                    Extensões PHP
                </h2>
                <?php foreach ($requirements['extensions'] as $ext => $name): ?>
                    <div class="check-item <?= extension_loaded($ext) ? 'ok' : 'error' ?>">
                        <div class="check-name"><?= $name ?></div>
                        <div class="check-value"><?= extension_loaded($ext) ? '✓ Instalada' : '✗ Faltando' ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Configurações PHP -->
            <div class="section">
                <h2>
                    <div class="status-icon <?= $status['php_ini'] ? 'status-ok' : 'status-warning' ?>">
                        <?= $status['php_ini'] ? '✓' : '!' ?>
                    </div>
                    Configurações php.ini
                </h2>
                <?php foreach ($requirements['php_ini'] as $setting => $values): ?>
                    <?php
                    $isOk = checkPhpIni($values['min'], $values['current']);
                    ?>
                    <div class="check-item <?= $isOk ? 'ok' : 'warning' ?>">
                        <div class="check-name"><?= str_replace('_', ' ', ucfirst($setting)) ?></div>
                        <div class="check-value">
                            <?= $values['current'] ?> 
                            <?= $isOk ? '(✓ OK)' : '(⚠️ Mín: ' . $values['min'] . ')' ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Diretórios -->
            <div class="section">
                <h2>
                    <div class="status-icon <?= $status['directories'] ? 'status-ok' : 'status-error' ?>">
                        <?= $status['directories'] ? '✓' : '✗' ?>
                    </div>
                    Permissões de Diretórios
                </h2>
                <?php foreach ($requirements['directories'] as $dir => $name): ?>
                    <?php
                    $writable = is_dir($dir) && is_writable($dir);
                    ?>
                    <div class="check-item <?= $writable ? 'ok' : 'error' ?>">
                        <div class="check-name"><?= $name ?></div>
                        <div class="check-value">
                            <?= $writable ? '✓ Escrita OK' : '✗ Sem permissão' ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Composer -->
            <div class="section">
                <h2>
                    <div class="status-icon <?= $status['composer'] ? 'status-ok' : 'status-error' ?>">
                        <?= $status['composer'] ? '✓' : '✗' ?>
                    </div>
                    Composer
                </h2>
                <div class="check-item <?= $composerAvailable ? 'ok' : 'error' ?>">
                    <div class="check-name">Composer</div>
                    <div class="check-value">
                        <?= $composerAvailable ? '✓ Disponível' : '✗ Não encontrado' ?>
                    </div>
                </div>
            </div>
            
            <!-- Resumo -->
            <div class="summary <?= $allOk ? 'all-ok' : 'has-errors' ?>">
                <h3>
                    <?= $allOk ? '🎉 Todos os requisitos estão OK!' : '⚠️ Alguns requisitos precisam ser ajustados' ?>
                </h3>
                
                <?php if ($allOk): ?>
                    <p>Seu servidor está pronto para instalar o sistema!</p>
                    <div style="margin-top: 20px;">
                        <a href="install.php" class="btn btn-success">🚀 Instalar Sistema Agora</a>
                        <a href="https://getcomposer.org/download/" class="btn btn-warning" target="_blank">📦 Baixar Composer</a>
                    </div>
                <?php else: ?>
                    <p>Por favor, ajuste os requisitos listados acima antes de prosseguir.</p>
                    
                    <?php if (!empty($missingExtensions)): ?>
                    <div class="instructions">
                        <h4>📦 Para instalar extensões faltantes:</h4>
                        <ul>
                            <li><strong>Ubuntu/Debian:</strong> <code>sudo apt install php8.4-<?= implode(' php8.4-', array_keys($missingExtensions)) ?></code></li>
                            <li><strong>CentOS/RHEL:</strong> <code>sudo yum install php8.4-<?= implode(' php8.4-', array_keys($missingExtensions)) ?></code></li>
                            <li><strong>Windows:</strong> Edite php.ini e descomente as linhas <code>extension=<?= implode('.dll', $missingExtensions) ?></code></li>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($dirProblems)): ?>
                    <div class="instructions">
                        <h4>📁 Para corrigir permissões:</h4>
                        <ul>
                            <li><strong>Linux/Mac:</strong> <code>chmod -R 755 storage bootstrap/cache</code></li>
                            <li><strong>Linux/Mac:</strong> <code>chown -R www-data:www-data storage bootstrap/cache</code></li>
                            <li><strong>Windows:</strong> Clique com botão direito nas pastas → Propriedades → Segurança → Editar permissões</li>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!$composerAvailable): ?>
                    <div class="instructions">
                        <h4>🎼 Para instalar Composer:</h4>
                        <ul>
                            <li><strong>Windows:</strong> <a href="https://getcomposer.org/Composer-Setup.exe" target="_blank">Baixe o instalador</a></li>
                            <li><strong>Linux/Mac:</strong> <code>curl -sS https://getcomposer.org/installer | php</code></li>
                            <li><strong>Global:</strong> <code>mv composer.phar /usr/local/bin/composer</code></li>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 20px;">
                        <button onclick="location.reload()" class="btn btn-warning">🔄 Verificar Novamente</button>
                        <a href="https://www.php.net/manual/pt_BR/install.php" class="btn btn-danger" target="_blank">📚 Ajuda PHP</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
