<?php
/**
 * 🚀 Instalador Profissional - Sistema de Lanchonetes
 * Interface moderna e robusta com verificação completa de requisitos
 */

// Configurações
define('MIN_PHP_VERSION', '8.4.0');
define('REQUIRED_EXTENSIONS', ['pdo', 'pdo_mysql', 'mbstring', 'curl', 'zip', 'gd', 'bcmath', 'xml', 'fileinfo']);
define('REQUIRED_PHP_INI', [
    'memory_limit' => '256M',
    'max_execution_time' => '300',
    'upload_max_filesize' => '64M',
    'post_max_size' => '64M'
]);

// Detectar se é requisição AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

// Se for AJAX, retornar JSON
if ($isAjax) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'check_requirements':
            echo json_encode([
                'success' => true,
                'data' => checkAllRequirements()
            ]);
            exit;
            
        case 'install':
            echo json_encode(installSystem());
            exit;
            
        case 'create_admin':
            echo json_encode(createAdminUser());
            exit;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
            exit;
    }
}

// Verificar se está rodando via CLI ou web
$isCli = php_sapi_name() === 'cli';

function exibir($mensagem, $tipo = 'info') {
    global $isCli;
    if ($isCli) {
        $cores = [
            'info' => "\033[0m",
            'success' => "\033[32m",
            'warning' => "\033[33m", 
            'error' => "\033[31m",
            'title' => "\033[1;34m"
        ];
        echo $cores[$tipo] . $mensagem . "\033[0m\n";
    } else {
        $cores = [
            'info' => '#007bff',
            'success' => '#28a745',
            'warning' => '#ffc107',
            'error' => '#dc3545',
            'title' => '#6f42c1'
        ];
        echo "<div style='color: {$cores[$tipo]}; margin: 10px 0; padding: 10px; border-left: 4px solid {$cores[$tipo]}; background: #f8f9fa;'>{$mensagem}</div>";
    }
}

function titulo($texto) {
    global $isCli;
    if ($isCli) {
        exibir(str_repeat("=", 60), 'title');
        exibir(strtoupper($texto), 'title');
        exibir(str_repeat("=", 60), 'title');
    } else {
        echo "<h1 style='color: #6f42c1; text-align: center; padding: 20px;'>{$texto}</h1>";
    }
}

function verificarPHP() {
    titulo("VERIFICANDO PHP");
    
    $versaoAtual = PHP_VERSION;
    $versaoMinima = MIN_PHP_VERSION;
    
    if (version_compare($versaoAtual, $versaoMinima, '>=')) {
        exibir("✅ Versão PHP: {$versaoAtual} (OK)", 'success');
        return true;
    } else {
        exibir("❌ Versão PHP: {$versaoAtual} (Mínima: {$versaoMinima})", 'error');
        exibir("Por favor, atualize seu PHP para a versão {$versaoMinima} ou superior", 'warning');
        return false;
    }
}

function verificarExtensões() {
    titulo("VERIFICANDO EXTENSÕES PHP");
    
    $todasOk = true;
    $faltantes = [];
    
    foreach (REQUIRED_EXTENSIONS as $ext) {
        if (extension_loaded($ext)) {
            exibir("✅ Extensão {$ext}: OK", 'success');
        } else {
            exibir("❌ Extensão {$ext}: FALTANDO", 'error');
            $faltantes[] = $ext;
            $todasOk = false;
        }
    }
    
    if (!empty($faltantes)) {
        exibir("Extensões faltantes: " . implode(', ', $faltantes), 'warning');
        exibir("Instale as extensões e execute o instalador novamente", 'warning');
    }
    
    return $todasOk;
}

function verificarConfigurações() {
    titulo("VERIFICANDO CONFIGURAÇÕES PHP.INI");
    
    $problemas = [];
    
    foreach (REQUIRED_PHP_INI as $config => $valorEsperado) {
        $valorAtual = ini_get($config);
        
        // Comparação especial para valores de memória/tempo
        if (in_array($config, ['memory_limit', 'max_execution_time', 'upload_max_filesize', 'post_max_size'])) {
            $valorAtualBytes = return_bytes($valorAtual);
            $valorEsperadoBytes = return_bytes($valorEsperado);
            
            if ($valorAtualBytes >= $valorEsperadoBytes) {
                exibir("✅ {$config}: {$valorAtual} (OK)", 'success');
            } else {
                exibir("⚠️  {$config}: {$valorAtual} (Recomendado: {$valorEsperado})", 'warning');
                $problemas[] = "{$config} deveria ser {$valorEsperado}";
            }
        } else {
            if ($valorAtual == $valorEsperado) {
                exibir("✅ {$config}: {$valorAtual} (OK)", 'success');
            } else {
                exibir("⚠️  {$config}: {$valorAtual} (Esperado: {$valorEsperado})", 'warning');
                $problemas[] = "{$config} deveria ser {$valorEsperado}";
            }
        }
    }
    
    if (!empty($problemas)) {
        exibir("Ajustes recomendados no php.ini:", 'warning');
        foreach ($problemas as $problema) {
            exibir("  - {$problema}", 'warning');
        }
    }
    
    return true; // Não é crítico, apenas recomendações
}

function return_bytes($val) {
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

function verificarComposer() {
    titulo("VERIFICANDO COMPOSER");
    
    // Verificar se composer está disponível
    $composerPath = which('composer') ?: shell_exec('where composer 2>nul');
    $composerPath = trim($composerPath);
    
    if (!empty($composerPath)) {
        exibir("✅ Composer encontrado em: {$composerPath}", 'success');
        
        // Verificar versão
        $version = shell_exec('composer --version 2>nul');
        if (!empty($version)) {
            exibir("✅ Versão: " . trim($version), 'success');
        }
        return true;
    } else {
        exibir("❌ Composer não encontrado", 'error');
        exibir("Por favor, instale o Composer:", 'warning');
        exibir("1. Baixe em: https://getcomposer.org/download/", 'info');
        exibir("2. Ou via Chocolatey: choco install composer", 'info');
        exibir("3. Ou via Composer Setup: https://getcomposer.org/Composer-Setup.exe", 'info');
        return false;
    }
}

function verificarBanco() {
    titulo("VERIFICANDO BANCO DE DADOS");
    
    // Verificar se PDO MySQL está disponível
    if (extension_loaded('pdo_mysql')) {
        exibir("✅ PDO MySQL disponível", 'success');
        return true;
    } else {
        exibir("❌ PDO MySQL não disponível", 'error');
        return false;
    }
}

function verificarPermissões() {
    titulo("VERIFICANDO PERMISSÕES DE PASTAS");
    
    $pastas = [
        'storage' => 'Pasta Storage',
        'storage/logs' => 'Logs',
        'storage/framework' => 'Framework Cache',
        'bootstrap/cache' => 'Bootstrap Cache',
        'public' => 'Public Uploads'
    ];
    
    $problemas = [];
    
    foreach ($pastas as $pasta => $descricao) {
        if (is_dir($pasta)) {
            if (is_writable($pasta)) {
                exibir("✅ {$descricao}: Escrita OK", 'success');
            } else {
                exibir("❌ {$descricao}: Sem permissão de escrita", 'error');
                $problemas[] = $pasta;
            }
        } else {
            exibir("❌ {$descricao}: Pasta não existe", 'error');
            $problemas[] = $pasta;
        }
    }
    
    if (!empty($problemas)) {
        exibir("Pastas com problemas de permissão:", 'warning');
        foreach ($problemas as $pasta) {
            exibir("  - {$pasta}", 'warning');
        }
        exibir("Execute: chmod -R 755 storage bootstrap/cache", 'info');
        exiliar("Execute: chown -R www-data:www-data storage bootstrap/cache", 'info');
    }
    
    return empty($problemas);
}

function executarInstalação() {
    titulo("EXECUTANDO INSTALAÇÃO");
    
    $passos = [
        'composer install' => 'Instalando dependências...',
        'php artisan key:generate' => 'Gerando chave da aplicação...',
        'php artisan storage:link' => 'Linkando pasta storage...',
        'php artisan migrate' => 'Executando migrations...',
        'php artisan cache:clear' => 'Limpando cache...',
        'php artisan config:clear' => 'Limpando configurações...',
        'php artisan route:clear' => 'Limpando rotas...',
        'php artisan view:clear' => 'Limpando views...'
    ];
    
    foreach ($passos as $comando => $descricao) {
        exibir($descricao, 'info');
        
        $output = shell_exec($comando . ' 2>&1');
        
        if (strpos($output, 'Error') !== false || strpos($output, 'Exception') !== false) {
            exibir("❌ Erro ao executar: {$comando}", 'error');
            exibir("Detalhes: " . substr($output, 0, 200) . "...", 'error');
            return false;
        } else {
            exibir("✅ {$descricao} - OK", 'success');
        }
    }
    
    return true;
}

function criarAmbiente() {
    titulo("CONFIGURANDO AMBIENTE");
    
    if (!file_exists('.env')) {
        if (file_exists('.env.example')) {
            if (copy('.env.example', '.env')) {
                exibir("✅ Arquivo .env criado", 'success');
                exibir("⚠️  Configure o banco de dados no arquivo .env", 'warning');
                exibir("DB_CONNECTION=mysql", 'info');
                exibir("DB_HOST=127.0.0.1", 'info');
                exibir("DB_PORT=3306", 'info');
                exibir("DB_DATABASE=nome_do_banco", 'info');
                exibir("DB_USERNAME=usuario", 'info');
                exibir("DB_PASSWORD=senha", 'info');
                exibir("APP_URL=http://localhost:8000", 'info');
                return true;
            } else {
                exibir("❌ Erro ao criar .env", 'error');
                return false;
            }
        } else {
            exibir("❌ Arquivo .env.example não encontrado", 'error');
            return false;
        }
    } else {
        exibir("✅ Arquivo .env já existe", 'success');
        return true;
    }
}

function exibirResumo($sucesso) {
    titulo($sucesso ? "INSTALAÇÃO CONCLUÍDA COM SUCESSO!" : "INSTALAÇÃO FALHOU");
    
    if ($sucesso) {
        exibir("🎉 Sistema instalado com sucesso!", 'success');
        exibir("📋 Próximos passos:", 'info');
        exibir("1. Configure o banco de dados no .env (se ainda não configurou)", 'info');
        exibir("2. Execute: php artisan serve", 'info');
        exibir("3. Acesse: http://localhost:8000", 'info');
        exibir("4. Crie sua conta de administrador", 'info');
        exibir("5. Configure o MercadoPago em .env", 'info');
        exibir("", 'info');
        exibir("📚 Documentação adicional:", 'info');
        exibir("• Configurar MercadoPago: https://www.mercadopago.com.br/developers", 'info');
        exibir("• Configurar email: edite MAIL_* no .env", 'info');
    } else {
        exibir("❌ Falha na instalação. Verifique os erros acima.", 'error');
        exibir("📞 Para suporte:", 'info');
        exibir("• Verifique os requisitos novamente", 'info');
        exibir("• Instale as dependências faltantes", 'info');
        exibir("• Corrija as permissões das pastas", 'info');
    }
}

// Função principal de instalação
function main() {
    global $isCli;
    
    if (!$isCli) {
        echo "<!DOCTYPE html><html><head>";
        echo "<title>Instalador - Sistema de Lanchonetes</title>";
        echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:0 auto;padding:20px;}</style>";
        echo "</head><body>";
    }
    
    titulo("INSTALADOR AUTOMÁTICO - SISTEMA DE LANCHONETES");
    exibir("Bem-vindo ao instalador automático! Vamos verificar os requisitos do sistema.", 'info');
    
    // Verificações
    $checks = [
        'php' => verificarPHP(),
        'extensoes' => verificarExtensões(),
        'configuracoes' => verificarConfigurações(),
        'composer' => verificarComposer(),
        'banco' => verificarBanco(),
        'permissoes' => verificarPermissões()
    ];
    
    $todosOk = array_reduce($checks, function($carry, $item) {
        return $carry && $item;
    }, true);
    
    if (!$todosOk) {
        exibirResumo(false);
        return;
    }
    
    // Perguntar se deseja continuar
    if ($isCli) {
        exibir("Todos os requisitos estão OK. Deseja continuar com a instalação? (y/n)", 'info');
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) !== 'y') {
            exibir("Instalação cancelada pelo usuário.", 'warning');
            return;
        }
    } else {
        echo "<form method='post'>";
        echo "<input type='hidden' name='install' value='1'>";
        echo "<button type='submit' style='background: #28a745; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;'>INSTALAR AGORA</button>";
        echo "</form>";
        
        if (!isset($_POST['install'])) {
            return;
        }
    }
    
    // Executar instalação
    if (criarAmbiente()) {
        $sucesso = executarInstalação();
        exibirResumo($sucesso);
    } else {
        exibirResumo(false);
    }
    
    if (!$isCli) {
        echo "</body></html>";
    }
}

// Executar
main();
?>
