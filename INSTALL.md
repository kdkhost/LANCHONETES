# 🚀 Instalação do Sistema de Lanchonetes

Guia completo de instalação do Sistema de Lanchonetes Multiloja com PWA.

## 📋 Requisitos Mínimos

### Obrigatórios
- **PHP 8.4+**
- **MySQL 8.0+** ou MariaDB 10.3+
- **Composer** 2.0+
- **Servidor Web** (Apache, Nginse ou PHP Built-in)

### Extensões PHP
- PDO
- PDO MySQL
- mbstring
- curl
- zip
- gd
- bcmath
- xml
- fileinfo
- json
- session
- tokenizer

### Configurações PHP.ini
```ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 64M
post_max_size = 64M
max_input_vars = 3000
```

---

## 🔍 Verificação Automática

### Opção 1: Verificador Web (Recomendado)
1. Coloque os arquivos do projeto no seu servidor
2. Acesse: `http://seusite.com/check.php`
3. Siga as instruções na tela
4. Se tudo estiver OK, clique em "Instalar Sistema Agora"

### Opção 2: Verificador CLI
```bash
php check.php
```

---

## ⚙️ Instalação Automática

### Via Web (Recomendado)
1. Acesse: `http://seusite.com/install.php`
2. Siga o passo a passo
3. O sistema instalará tudo automaticamente

### Via Terminal
```bash
# 1. Baixar o projeto
git clone <repositorio>
cd lanchonetes

# 2. Instalar dependências
composer install

# 3. Configurar ambiente
cp .env.example .env
php artisan key:generate

# 4. Configurar banco de dados
# Edite o arquivo .env:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario
DB_PASSWORD=senha
APP_URL=http://localhost:8000

# 5. Instalar banco de dados
php artisan migrate

# 6. Linkar storage
php artisan storage:link

# 7. Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 8. Iniciar servidor
php artisan serve
```

---

## 🔧 Configurações Pós-Instalação

### 1. MercadoPago (Obrigatório para pagamentos)
Adicione ao `.env`:
```env
MERCADOPAGO_ACCESS_TOKEN=TEST_xxxxxxxxxxxxxxxxx
MERCADOPAGO_PUBLIC_KEY=TEST_xxxxxxxxxxxxxxxx
MERCADOPAGO_SANDBOX=true
```

**Como obter:**
1. Acesse: https://www.mercadopago.com.br/developers
2. Crie uma aplicação
3. Copie as chaves de acesso
4. Configure o webhook: `https://seusite.com/webhook/mercadopago/plano`

### 2. Email (Obrigatório para notificações)
Adicione ao `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seuemail@gmail.com
MAIL_PASSWORD=sua_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=seuemail@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Para Gmail:**
1. Ative "Acesso a app menos seguras"
2. Ou use "Senha de app" (recomendado)

### 3. Sistema de Arquivos
Garanta que as pastas tenham permissão de escrita:
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 🌐 Acesso ao Sistema

### URLs Importantes
- **Painel Admin:** `http://localhost:8000/admin`
- **Loja Pública:** `http://localhost:8000/{slug-loja}`
- **Verificador:** `http://localhost:8000/check.php`
- **Instalador:** `http://localhost:8000/install.php`

### Primeiro Acesso
1. Acesse: `http://localhost:8000/admin/register`
2. Crie sua conta (será Super Admin)
3. Configure sua primeira loja
4. Comece a usar!

---

## 📱 Funcionalidades Instaladas

### ✅ Sistema Completo
- **Multilojas** com URLs amigáveis
- **PWA** instalável como app
- **Pedidos online** com delivery/retirada
- **Pagamentos** via MercadoPago
- **Cozinha** com tela dedicada
- **Notificações** WhatsApp e email
- **Relatórios** completos
- **Estoque** automático
- **Avaliações** de produtos

### ✅ Sistema de Planos
- **Trial gratuito** de 14 dias
- **Planos mensal/anual**
- **Bloqueio automático** após expiração
- **Pagamentos** via MercadoPago
- **Webhook** para ativação instantânea
- **Emails** automáticos de notificação

### ✅ Novidades Recentes
- **CNPJ alfanumérico** (governo 2026)
- **Contador de visitas** completo
- **Horários automáticos** de funcionamento
- **Dashboard** de estatísticas

---

## 🛠️ Comandos Úteis

### Manutenção
```bash
# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Otimizar
php artisan optimize
php artisan config:cache

# Gerenciar planos
php artisan planos:gerenciar
php artisan lojas:gerenciar-horarios

# Verificar status
php artisan schedule:run
php artisan queue:work
```

### Desenvolvimento
```bash
# Criar usuário admin
php artisan tinker
> $user = new App\Models\User();
> $user->name = 'Admin';
> $user->email = 'admin@teste.com';
> $user->password = bcrypt('senha');
> $user->role = 'super_admin';
> $user->save();

# Testar email
php artisan tinker
> Mail::raw('Teste', fn($m) => $m->to('seuemail@teste.com')->subject('Teste'));
```

---

## 🚀 Produção

### Configurações Adicionais
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seusite.com

# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis

# Queue
QUEUE_CONNECTION=redis

# HTTPS (obrigatório para PWA)
FORCE_HTTPS=true
```

### Servidor Web

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [L]
</IfModule>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name seusite.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name seusite.com;
    root /var/www/lanchonetes/public;
    index index.php;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Cron Jobs
```bash
# Schedule Laravel
* * * * * cd /var/www/lanchonetes && php artisan schedule:run >> /dev/null 2>&1

# Queue Worker
* * * * * cd /var/www/lanchonetes && php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

---

## 🔧 Solução de Problemas

### Erros Comuns

**"Class not found"**
```bash
composer dump-autoload
php artisan optimize
```

**"Permission denied"**
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**"Database connection failed"**
- Verifique as credenciais no `.env`
- Crie o banco de dados
- Execute `php artisan migrate:fresh --seed`

**"MercadoPago error"**
- Verifique as chaves no `.env`
- Configure o webhook URL
- Teste com sandbox primeiro

### Logs
```bash
# Verificar logs
tail -f storage/logs/laravel.log

# Logs de erro
tail -f storage/logs/laravel-*.log

# Log de queue
tail -f storage/logs/worker.log
```

---

## 📞 Suporte

### Documentação
- **Laravel:** https://laravel.com/docs
- **MercadoPago:** https://www.mercadopago.com.br/developers
- **PWA:** https://web.dev/progressive-web-apps

### Comunidade
- **GitHub:** Issues do repositório
- **Discord:** Servidor da comunidade
- **Email:** suporte@seusite.com

---

## 🎉 Pronto!

Após seguir estes passos, seu sistema estará 100% funcional com:

- ✅ **Instalação automática**
- ✅ **Verificação de requisitos**
- ✅ **Sistema de planos**
- ✅ **Pagamentos online**
- ✅ **Notificações automáticas**
- ✅ **PWA instalável**
- ✅ **Dashboard completo**

Acesse `http://localhost:8000/admin` e comece a usar! 🚀
