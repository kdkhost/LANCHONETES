# 🚀 Configuração para Produção

Guia completo para configurar o Sistema de Lanchonetes em ambiente de produção.

## 📋 Pré-requisitos

- **Servidor Web**: Apache 2.4+ ou Nginx
- **PHP**: 8.4+ com extensões necessárias
- **Banco de Dados**: MySQL 8.0+ ou MariaDB 10.3+
- **SSL**: Certificado HTTPS (obrigatório para PWA)
- **Domain**: Domínio configurado apontando para o servidor

---

## 🔧 Configuração do Servidor

### Apache (Recomendado)

1. **Habilitar mod_rewrite:**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

2. **Configurar Virtual Host:**
```apache
<VirtualHost *:80>
    ServerName seudominio.com.br
    DocumentRoot /var/www/lanchonetes
    
    # Redirecionar para HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    ServerName seudominio.com.br
    DocumentRoot /var/www/lanchonetes/public
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/seudominio.com.br/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/seudominio.com.br/privkey.pem
    
    # Security Headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Content-Type-Options "nosniff"
    
    # AllowOverride para .htaccess
    <Directory /var/www/lanchonetes>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

3. **Habilitar site:**
```bash
sudo a2ensite seudominio.com.br
sudo systemctl reload apache2
```

### Nginx

1. **Copiar configuração:**
```bash
sudo cp nginx.conf.example /etc/nginx/sites-available/seudominio.com.br
sudo ln -s /etc/nginx/sites-available/seudominio.com.br /etc/nginx/sites-enabled/
```

2. **Editar configuração:**
```bash
sudo nano /etc/nginx/sites-available/seudominio.com.br
# Alterar seudominio.com.br para seu domínio real
# Verificar caminhos dos certificados SSL
```

3. **Testar e ativar:**
```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## 🔒 Configuração SSL (HTTPS)

### Let's Encrypt (Gratuito)

1. **Instalar Certbot:**
```bash
sudo apt update
sudo apt install certbot python3-certbot-apache
# ou para nginx: sudo apt install certbot python3-certbot-nginx
```

2. **Obter certificado:**
```bash
# Apache
sudo certbot --apache -d seudominio.com.br -d www.seudominio.com.br

# Nginx
sudo certbot --nginx -d seudominio.com.br -d www.seudominio.com.br
```

3. **Auto-renovação:**
```bash
sudo crontab -e
# Adicionar:
0 12 * * * /usr/bin/certbot renew --quiet
```

---

## 📁 Estrutura de Arquivos

```
/var/www/lanchonetes/
├── .env                    # Configurações do ambiente
├── .htaccess              # Configurações Apache (raiz)
├── index.php              # Entry point com verificação
├── install.php            # Instalador automático
├── check.php              # Verificador de requisitos
├── public/                # Arquivos públicos
│   ├── .htaccess          # Configurações Apache (public)
│   ├── index.php          # Laravel public/index.php
│   ├── assets/            # CSS, JS, imagens
│   └── storage/           # Uploads (linkado)
├── storage/               # Armazenamento
├── vendor/                # Dependências
└── bootstrap/cache/       # Cache
```

---

## ⚙️ Configuração do Ambiente

1. **Copiar .env:**
```bash
cp .env.example .env
```

2. **Editar .env:**
```env
APP_NAME="Sua Lanchonete"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seudominio.com.br

# Banco de Dados
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario
DB_PASSWORD=senha_segura

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seuemail@dominio.com
MAIL_PASSWORD=senha_app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=seuemail@dominio.com
MAIL_FROM_NAME="${APP_NAME}"

# MercadoPago
MERCADOPAGO_ACCESS_TOKEN=PROD_xxxxxxxxxxxxxxxxx
MERCADOPAGO_PUBLIC_KEY=PROD_xxxxxxxxxxxxxxxx
MERCADOPAGO_SANDBOX=false

# Queue (opcional, recomendado)
QUEUE_CONNECTION=database
```

3. **Gerar chave:**
```bash
php artisan key:generate
```

---

## 🗄️ Configuração do Banco de Dados

1. **Criar banco:**
```sql
CREATE DATABASE nome_do_banco CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'usuario'@'localhost' IDENTIFIED BY 'senha_segura';
GRANT ALL PRIVILEGES ON nome_do_banco.* TO 'usuario'@'localhost';
FLUSH PRIVILEGES;
```

2. **Executar migrations:**
```bash
php artisan migrate --force
```

3. **Criar usuário admin:**
```bash
php artisan tinker
> $user = new App\Models\User();
> $user->name = 'Administrador';
> $user->email = 'admin@seudominio.com';
> $user->password = bcrypt('senha_segura');
> $user->role = 'super_admin';
> $user->save();
> exit
```

---

## 🔗 Linkar Storage

```bash
php artisan storage:link
```

---

## 🚀 Otimização de Performance

1. **Otimizar Laravel:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

2. **Configurar Queue (recomendado):**
```bash
# Editar .env
QUEUE_CONNECTION=database

# Criar tabela de jobs
php artisan queue:table
php artisan migrate

# Iniciar worker
php artisan queue:work --daemon
```

3. **Configurar Cron:**
```bash
sudo crontab -e
# Adicionar:
* * * * * cd /var/www/lanchonetes && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔐 Segurança

1. **Permissões de arquivos:**
```bash
# Pastas
sudo chown -R www-data:www-data /var/www/lanchonetes
sudo chmod -R 755 /var/www/lanchonetes
sudo chmod -R 777 /var/www/lanchonetes/storage
sudo chmod -R 777 /var/www/lanchonetes/bootstrap/cache

# Arquivos
sudo chmod 644 /var/www/lanchonetes/.env
sudo chmod 600 /var/www/lanchonetes/.env
```

2. **Proteger pastas sensíveis:**
```bash
# Já configurado no .htaccess, mas verifique:
# - .env
# - vendor/
# - storage/
# - bootstrap/cache/
# - composer.json
# - package.json
```

3. **Firewall:**
```bash
# Permitir apenas portas necessárias
sudo ufw allow 22    # SSH
sudo ufw allow 80    # HTTP
sudo ufw allow 443   # HTTPS
sudo ufw enable
```

---

## 📊 Monitoramento

1. **Logs:**
```bash
# Verificar logs
tail -f /var/www/lanchonetes/storage/logs/laravel.log

# Logs do servidor
sudo tail -f /var/log/apache2/error.log
# ou
sudo tail -f /var/log/nginx/error.log
```

2. **Health Check:**
```bash
# Criar endpoint de health
# Adicionar em routes/web.php:
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});
```

---

## 🔄 Manutenção

1. **Backup do banco:**
```bash
# Criar script de backup
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u usuario -p nome_do_banco > /backup/db_backup_$DATE.sql
find /backup -name "db_backup_*.sql" -mtime +7 -delete
```

2. **Limpeza de cache:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

3. **Atualizar dependências:**
```bash
composer update --no-dev --optimize-autoloader
php artisan optimize
```

---

## 🌐 URLs Finais

Após configuração:

- **Sistema**: `https://seudominio.com.br`
- **Admin**: `https://seudominio.com.br/admin`
- **Instalador**: `https://seudominio.com.br/install.php`
- **Verificador**: `https://seudominio.com.br/check.php`
- **API**: `https://seudominio.com.br/api`
- **Webhooks**: `https://seudominio.com.br/webhook`

---

## ⚠️ Importante

1. **HTTPS é OBRIGATÓRIO** para PWA funcionar
2. **Remova `/public`** da URL (configuração automática)
3. **Configure SSL** antes de ir para produção
4. **Faça backup** regularmente
5. **Monitore logs** de erro
6. **Mantenha dependências** atualizadas

---

## 🆘 Suporte

Se encontrar problemas:

1. **Verifique logs** do Laravel e do servidor
2. **Teste instalador**: `https://seudominio.com.br/check.php`
3. **Verifique permissões** das pastas
4. **Confirme configurações** SSL
5. **Teste em ambiente local** antes de produção

---

## 🎉 Pronto!

Seu sistema está configurado para produção com:
- ✅ URL limpa (sem `/public`)
- ✅ HTTPS obrigatório
- ✅ Instalação automática
- ✅ Segurança reforçada
- ✅ Performance otimizada
- ✅ Monitoramento ativo

Acesse `https://seudominio.com.br` e comece a usar! 🚀
