<?php
/**
 * Script para criar estrutura de diretórios e arquivos essenciais do Laravel
 */

echo "🚀 Criando estrutura de diretórios essenciais...\n";

// Criar diretórios essenciais
$directories = [
    'storage/app/public',
    'storage/framework/cache',
    'storage/framework/sessions', 
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache',
    'public/uploads',
    'public/storage',
    'public/images',
    'public/css',
    'public/js',
    'resources/views',
    'resources/views/admin',
    'resources/views/layouts',
    'resources/views/cliente',
    'resources/views/errors',
    'resources/lang',
    'resources/lang/pt_BR',
    'app/Http/Controllers',
    'app/Http/Controllers/Admin',
    'app/Http/Controllers/Api',
    'app/Http/Middleware',
    'app/Models',
    'app/Services',
    'app/Console/Commands',
    'database/migrations',
    'database/seeders',
    'database/factories',
    'routes',
    'tests',
    'tests/Unit',
    'tests/Feature'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✅ Criado diretório: $dir\n";
    } else {
        echo "ℹ️  Diretório já existe: $dir\n";
    }
}

// Criar arquivos essenciais
$files = [
    'storage/framework/.gitignore' => "*\n!.gitignore",
    'bootstrap/cache/.gitignore' => "*\n!.gitignore",
    'public/.htaccess' => '<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>',
    
    'resources/lang/pt_BR/validation.php' => '<?php return [
    \'accepted\' => \':attribute deve ser aceito.\',
    \'active_url\' => \':attribute não é uma URL válida.\',
    \'after\' => \':attribute deve ser uma data posterior a :date.\',
    \'alpha\' => \':attribute deve conter apenas letras.\',
    \'alpha_dash\' => \':attribute deve conter apenas letras, números e traços.\',
    \'alpha_num\' => \':attribute deve conter apenas letras e números.\',
    \'array\' => \':attribute deve ser um array.\',
    \'before\' => \':attribute deve ser uma data anterior a :date.\',
    \'between\' => [
        \'array\' => \':attribute deve ter entre :min e :max itens.\',
        \'file\' => \':attribute deve ter entre :min e :max kilobytes.\',
        \'numeric\' => \':attribute deve estar entre :min e :max.\',
        \'string\' => \':attribute deve ter entre :min e :max caracteres.\',
    ],
    \'boolean\' => \':attribute deve ser verdadeiro ou falso.\',
    \'confirmed\' => \':attribute de confirmação não confere.\',
    \'date\' => \':attribute não é uma data válida.\',
    \'date_format\' => \':attribute não corresponde ao formato :format.\',
    \'different\' => \':attribute e :other devem ser diferentes.\',
    \'digits\' => \':attribute deve ter :digits dígitos.\',
    \'digits_between\' => \':attribute deve ter entre :min e :max dígitos.\',
    \'dimensions\' => \':attribute tem dimensões de imagem inválidas.\',
    \'distinct\' => \':attribute já foi selecionado.\',
    \'email\' => \':attribute não é um email válido.\',
    \'exists\' => \':attribute selecionado é inválido.\',
    \'file\' => \':attribute deve ser um arquivo.\',
    \'filled\' => \':attribute deve ter um valor.\',
    \'gt\' => [
        \'array\' => \':attribute deve ter mais de :value itens.\',
        \'file\' => \':attribute deve ter mais de :value kilobytes.\',
        \'numeric\' => \':attribute deve ser maior que :value.\',
        \'string\' => \':attribute deve ter mais de :value caracteres.\',
    ],
    \'image\' => \':attribute deve ser uma imagem.\',
    \'in\' => \':attribute selecionado é inválido.\',
    \'in_array\' => \':attribute não existe em :other.\',
    \'integer\' => \':attribute deve ser um número inteiro.\',
    \'ip\' => \':attribute deve ser um endereço IP válido.\',
    \'json\' => \':attribute deve ser uma string JSON válida.\',
    \'max\' => [
        \'array\' => \':attribute não pode ter mais de :max itens.\',
        \'file\' => \':attribute não pode ter mais de :max kilobytes.\',
        \'numeric\' => \':attribute não pode ser maior que :max.\',
        \'string\' => \':attribute não pode ter mais de :max caracteres.\',
    ],
    \'mimes\' => \':attribute deve ser um arquivo do tipo: :values.\',
    \'mimetypes\' => \':attribute deve ser um arquivo do tipo: :values.\',
    \'min\' => [
        \'array\' => \':attribute deve ter no mínimo :min itens.\',
        \'file\' => \':attribute deve ter no mínimo :min kilobytes.\',
        \'numeric\' => \':attribute deve ser no mínimo :min.\',
        \'string\' => \':attribute deve ter no mínimo :min caracteres.\',
    ],
    \'not_in\' => \':attribute selecionado é inválido.\',
    \'numeric\' => \':attribute deve ser um número.\',
    \'present\' => \':attribute deve estar presente.\',
    \'regex\' => \':attribute tem um formato inválido.\',
    \'required\' => \':attribute é obrigatório.\',
    \'required_if\' => \':attribute é obrigatório quando :other é :value.\',
    \'required_unless\' => \':attribute é obrigatório exceto quando :other é em :values.\',
    \'required_with\' => \':attribute é obrigatório quando :values está presente.\',
    \'required_with_all\' => \':attribute é obrigatório quando :values estão presentes.\',
    \'required_without\' => \':attribute é obrigatório quando :values não está presente.\',
    \'required_without_all\' => \':attribute é obrigatório quando nenhum dos :values está presente.\',
    \'same\' => \':attribute e :other devem ser iguais.\',
    \'size\' => [
        \'array\' => \':attribute deve ter :size itens.\',
        \'file\' => \':attribute deve ter :size kilobytes.\',
        \'numeric\' => \':attribute deve ser :size.\',
        \'string\' => \':attribute deve ter :size caracteres.\',
    ],
    \'string\' => \':attribute deve ser uma string.\',
    \'timezone\' => \':attribute deve ser uma zona válida.\',
    \'unique\' => \':attribute já está sendo usado.\',
    \'uploaded\' => \':attribute falhou no upload.\',
    \'url\' => \':attribute não é uma URL válida.\',
];',
];

foreach ($files as $file => $content) {
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    if (!file_exists($file)) {
        file_put_contents($file, $content);
        echo "✅ Criado arquivo: $file\n";
    } else {
        echo "ℹ️  Arquivo já existe: $file\n";
    }
}

// Criar link simbólico para storage (se não existir)
if (!file_exists('public/storage')) {
    if (symlink('../storage/app/public', 'public/storage')) {
        echo "✅ Criado link simbólico: public/storage\n";
    } else {
        echo "⚠️  Não foi possível criar link simbólico (pode ser criado manualmente)\n";
    }
} else {
    echo "ℹ️  Link simbólico já existe: public/storage\n";
}

echo "\n🎉 Estrutura criada com sucesso!\n";
echo "\n📋 Próximos passos:\n";
echo "1. Execute 'composer install' para instalar dependências\n";
echo "2. Copie .env.example para .env\n";
echo "3. Execute 'php artisan key:generate'\n";
echo "4. Configure o banco de dados no .env\n";
echo "5. Execute 'php artisan migrate'\n";
echo "6. Execute 'php artisan storage:link'\n";
echo "7. Execute 'php artisan serve' para testar\n";
?>
