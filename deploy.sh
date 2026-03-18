#!/bin/bash

# Script de Deploy Automático - Sistema de Lanchonetes
# Uso: ./deploy.sh [dominio] [ambiente]
# Exemplo: ./deploy.sh seudominio.com.br production

set -e  # Parar em caso de erro

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função de log
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

error() {
    echo -e "${RED}[ERRO]${NC} $1"
    exit 1
}

success() {
    echo -e "${GREEN}[SUCESSO]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[AVISO]${NC} $1"
}

# Verificar parâmetros
if [ $# -lt 1 ]; then
    error "Uso: $0 <dominio> [ambiente]"
    echo "Exemplo: $0 seudominio.com.br production"
    exit 1
fi

DOMAIN=$1
ENVIRONMENT=${2:-production}
PROJECT_PATH="/var/www/$DOMAIN"
BACKUP_PATH="/var/backups/$DOMAIN"

log "Iniciando deploy do Sistema de Lanchonetes"
log "Domínio: $DOMAIN"
log "Ambiente: $ENVIRONMENT"

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then
    error "Este script precisa ser executado como root (sudo)"
fi

# Verificar sistema operacional
if [ -f /etc/debian_version ]; then
    OS="debian"
    log "Sistema operacional: Debian/Ubuntu detectado"
elif [ -f /etc/redhat-release ]; then
    OS="redhat"
    log "Sistema operacional: RedHat/CentOS detectado"
else
    error "Sistema operacional não suportado"
fi

# Criar diretórios
log "Criando estrutura de diretórios..."
mkdir -p $PROJECT_PATH
mkdir -p $BACKUP_PATH
mkdir -p /var/log/$DOMAIN

# Instalar dependências do sistema
log "Instalando dependências do sistema..."

if [ "$OS" = "debian" ]; then
    apt update
    apt install -y apache2 mysql-server php8.4 php8.4-cli php8.4-fpm \
        php8.4-mysql php8.4-xml php8.4-mbstring php8.4-curl php8.4-zip \
        php8.4-gd php8.4-bcmath php8.4-fileinfo php8.4-tokenizer \
        unzip curl git certbot python3-certbot-apache \
        composer nodejs npm
elif [ "$OS" = "redhat" ]; then
    yum update -y
    yum install -y httpd mariadb-server php php-cli php-fpm \
        php-mysql php-xml php-mbstring php-curl php-zip \
        php-gd php-bcmath php-fileinfo php-tokenizer \
        unzip curl git certbot python3-certbot-apache \
        composer nodejs npm
fi

# Configurar Apache/Nginx
log "Configurando servidor web..."

if [ "$OS" = "debian" ]; then
    # Apache
    a2enmod rewrite ssl headers
    a2ensite default-ssl
    
    # Criar virtual host
    cat > /etc/apache2/sites-available/$DOMAIN.conf << EOF
<VirtualHost *:80>
    ServerName $DOMAIN
    DocumentRoot $PROJECT_PATH
    
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    ServerName $DOMAIN
    DocumentRoot $PROJECT_PATH/public
    
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/ssl-cert-snakeoil.pem
    SSLCertificateKeyFile /etc/ssl/private/ssl-cert-snakeoil.key
    
    <Directory $PROJECT_PATH>
        AllowOverride All
        Require all granted
    </Directory>
    
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Content-Type-Options "nosniff"
</VirtualHost>
EOF
    
    a2ensite $DOMAIN
    systemctl reload apache2
fi

# Configurar MySQL/MariaDB
log "Configurando banco de dados..."

if [ "$OS" = "debian" ]; then
    if ! systemctl is-active --quiet mysql; then
        systemctl start mysql
        systemctl enable mysql
    fi
    
    # Criar banco e usuário
    DB_NAME="lanchonete_$(echo $DOMAIN | tr '.' '_')"
    DB_USER="lanchonete_user"
    DB_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
    
    mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';"
    mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
    mysql -e "FLUSH PRIVILEGES;"
fi

# Obter certificado SSL
log "Configurando SSL..."

if command -v certbot &> /dev/null; then
    certbot --apache -d $DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN --redirect || warning "Certbot falhou, usando certificado auto-assinado"
else
    warning "Certbot não encontrado, usando certificado auto-assinado"
fi

# Copiar arquivos do projeto
log "Copiando arquivos do projeto..."

# Backup se já existir
if [ -d "$PROJECT_PATH/public" ]; then
    log "Fazendo backup do projeto existente..."
    tar -czf "$BACKUP_PATH/backup_$(date +%Y%m%d_%H%M%S).tar.gz" -C "$PROJECT_PATH" .
fi

# Copiar arquivos (assumindo que o script está na raiz do projeto)
cp -r * $PROJECT_PATH/
cp -r .* $PROJECT_PATH/ 2>/dev/null || true

# Configurar permissões
log "Configurando permissões..."

chown -R www-data:www-data $PROJECT_PATH
chmod -R 755 $PROJECT_PATH
chmod -R 777 $PROJECT_PATH/storage
chmod -R 777 $PROJECT_PATH/bootstrap/cache
chmod 600 $PROJECT_PATH/.env

# Instalar dependências PHP
log "Instalando dependências PHP..."

cd $PROJECT_PATH
composer install --no-dev --optimize-autoloader

# Configurar ambiente
log "Configurando ambiente..."

if [ ! -f "$PROJECT_PATH/.env" ]; then
    cp $PROJECT_PATH/.env.example $PROJECT_PATH/.env
fi

# Atualizar .env
sed -i "s|APP_URL=.*|APP_URL=https://$DOMAIN|g" $PROJECT_PATH/.env
sed -i "s|APP_ENV=.*|APP_ENV=$ENVIRONMENT|g" $PROJECT_PATH/.env
sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|g" $PROJECT_PATH/.env

if [ "$OS" = "debian" ]; then
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=$DB_NAME|g" $PROJECT_PATH/.env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=$DB_USER|g" $PROJECT_PATH/.env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|g" $PROJECT_PATH/.env
fi

# Gerar chave da aplicação
log "Gerando chave da aplicação..."
php artisan key:generate --force

# Executar migrations
log "Executando migrations..."
php artisan migrate --force

# Linkar storage
log "Linkando storage..."
php artisan storage:link

# Otimizar Laravel
log "Otimizando Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Configurar cron job
log "Configurando cron job..."

# Adicionar ao crontab do www-data
(crontab -u www-data -l 2>/dev/null; echo "* * * * * cd $PROJECT_PATH && php artisan schedule:run >> /dev/null 2>&1") | crontab -u www-data -

# Criar usuário admin
log "Criando usuário administrador..."

ADMIN_EMAIL="admin@$DOMAIN"
ADMIN_PASSWORD=$(openssl rand -base64 12 | tr -d "=+/" | cut -c1-10)

php artisan tinker << EOF
\$user = new App\Models\User();
\$user->name = 'Administrador';
\$user->email = '$ADMIN_EMAIL';
\$user->password = bcrypt('$ADMIN_PASSWORD');
\$user->role = 'super_admin';
\$user->save();
EOF

# Configurar logrotate
log "Configurando rotação de logs..."

cat > /etc/logrotate.d/$DOMAIN << EOF
$PROJECT_PATH/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload apache2
    endscript
}
EOF

# Testar configuração
log "Testando configuração..."

if curl -f -s "https://$DOMAIN" > /dev/null; then
    success "Site está acessível!"
else
    error "Site não está acessível. Verifique logs."
fi

# Salvar informações de instalação
INSTALL_INFO="$PROJECT_PATH/install_info.txt"

cat > $INSTALL_INFO << EOF
=== INFORMAÇÕES DE INSTALAÇÃO ===
Data: $(date)
Domínio: $DOMAIN
Ambiente: $ENVIRONMENT

=== ACESSO ===
URL: https://$DOMAIN
Admin: https://$DOMAIN/admin
Instalador: https://$DOMAIN/install.php
Verificador: https://$DOMAIN/check.php

=== BANCO DE DADOS ===
Banco: $DB_NAME
Usuário: $DB_USER
Senha: $DB_PASSWORD

=== USUÁRIO ADMIN ===
Email: $ADMIN_EMAIL
Senha: $ADMIN_PASSWORD

=== CAMINHOS ===
Projeto: $PROJECT_PATH
Logs: /var/log/$DOMAIN
Backups: $BACKUP_PATH

=== COMANDOS ÚTEIS ===
Verificar logs: tail -f $PROJECT_PATH/storage/logs/laravel.log
Reiniciar Apache: systemctl reload apache2
Otimizar: cd $PROJECT_PATH && php artisan optimize
Backup: tar -czf backup_\$(date +%Y%m%d).tar.gz .

=== PRÓXIMOS PASSOS ===
1. Configure o MercadoPago em .env
2. Configure o email em .env
3. Acesse o admin e configure sua loja
4. Teste o sistema completo

EOF

chmod 600 $INSTALL_INFO

# Finalização
success "Deploy concluído com sucesso!"

echo ""
echo "🎉 Sistema instalado em: https://$DOMAIN"
echo "📧 Acesso admin: $ADMIN_EMAIL / $ADMIN_PASSWORD"
echo "📋 Informações salvas em: $INSTALL_INFO"
echo ""
echo "📋 Próximos passos:"
echo "1. Acesse https://$DOMAIN/admin"
echo "2. Configure o MercadoPago no .env"
echo "3. Configure o email no .env"
echo "4. Cadastre sua primeira loja"
echo "5. Comece a usar! 🚀"
echo ""
echo "⚠️  Importante:"
echo "- Mantenha o arquivo $INSTALL_INFO seguro"
echo "- Configure backups regulares"
echo "- Monitore os logs do sistema"
echo "- Mantenha o sistema atualizado"

# Criar script de backup rápido
cat > $PROJECT_PATH/backup.sh << 'EOF'
#!/bin/bash
PROJECT_PATH="$(dirname "$0")"
BACKUP_PATH="/var/backups/$(basename $(dirname $PROJECT_PATH))"
DATE=$(date +%Y%m%d_%H%M%S)

echo "Fazendo backup..."
tar -czf "$BACKUP_PATH/backup_$DATE.tar.gz" -C "$PROJECT_PATH" .
echo "Backup concluído: $BACKUP_PATH/backup_$DATE.tar.gz"
EOF

chmod +x $PROJECT_PATH/backup.sh

success "Script de backup criado: $PROJECT_PATH/backup.sh"
