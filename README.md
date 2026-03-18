# 🍔 Sistema Lanchonete — Laravel 12 + PWA

Sistema completo de lanchonete/delivery com múltiplas lojas, pagamento MercadoPago (PIX + Cartão), notificações WhatsApp via Evolution API, rastreamento em tempo real e PWA estilo Android.

---

## ✅ Requisitos

| Componente | Versão mínima |
|---|---|
| PHP | 8.4+ |
| MySQL / MariaDB | 10.4+ |
| Composer | 2.x |
| Node.js | 20+ (apenas para build) |
| Extensões PHP | pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, bcmath, fileinfo, gd ou imagick |

---

## 🚀 Instalação

### 1. Clonar / Enviar arquivos

```bash
# Via Git
git clone https://github.com/seu-usuario/lanchonete.git
cd lanchonete

# Ou via cPanel FileManager: envie os arquivos e descompacte
```

### 2. Instalar dependências PHP

```bash
composer install --optimize-autoloader --no-dev
```

### 3. Configurar variáveis de ambiente

```bash
cp .env.example .env
php artisan key:generate
```

Edite o arquivo `.env` com suas credenciais:

```env
APP_NAME="Nome da sua Lanchonete"
APP_URL=https://seudominio.com.br

DB_HOST=localhost
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario_banco
DB_PASSWORD=senha_banco

MERCADOPAGO_PUBLIC_KEY=APP_USR-xxxxx
MERCADOPAGO_ACCESS_TOKEN=APP_USR-xxxxx
MERCADOPAGO_SANDBOX=false

EVOLUTION_API_URL=https://sua-evolution-api.com
EVOLUTION_API_KEY=sua-chave-api
EVOLUTION_INSTANCE=nome-instancia
```

### 4. Criar banco de dados e executar migrations

```bash
php artisan migrate --force
```

### 5. Popular dados iniciais (Seeders)

```bash
php artisan db:seed --force
```

Isso cria:
- **Loja demo**: Lanchonete do Zé
- **Admin**: admin@lanchonete.com / admin123
- **Gerente**: gerente@lanchonete.com / gerente123
- **Entregador**: entregador@lanchonete.com / entregador123
- **Cliente**: cliente@lanchonete.com / cliente123
- Categorias e produtos de exemplo

### 6. Criar link simbólico do storage

```bash
php artisan storage:link
```

### 7. Otimizar para produção

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## 🌐 Configuração no cPanel (Hospedagem Compartilhada)

### Apontar o domínio para `/public`

No cPanel → **Domínios** → **Subdomínios** (ou domínio raiz):
- Defina o **Document Root** como: `public_html/lanchonete/public`

Ou crie um arquivo `.htaccess` na raiz do cPanel:

```apache
RewriteEngine On
RewriteRule ^(.*)$ public/$1 [L]
```

### Configurar o `.htaccess` do `/public`

O Laravel já inclui o `.htaccess` correto em `/public/.htaccess`.

### Permissões de pastas

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 775 storage/app/public
```

### Banco de dados

1. Crie um banco MySQL no cPanel → **MySQL Databases**
2. Crie um usuário e associe ao banco com **ALL PRIVILEGES**
3. Atualize o `.env` com as credenciais

### PHP Version no cPanel

- Vá em **MultiPHP Manager** ou **PHP Selector (CloudLinux)**
- Selecione **PHP 8.4**
- Ative as extensões: `pdo_mysql`, `mbstring`, `xml`, `gd`, `fileinfo`, `zip`, `bcmath`

### Cron Job (Tarefas Agendadas)

No cPanel → **Cron Jobs**, adicione:
```
* * * * * /usr/local/bin/php /home/usuario/public_html/lanchonete/artisan schedule:run >> /dev/null 2>&1
```

---

## 🔧 Configurações do Sistema

### MercadoPago

1. Acesse [MercadoPago Developers](https://www.mercadopago.com.br/developers)
2. Crie um aplicativo
3. Copie a **Public Key** e o **Access Token**
4. No painel admin: **Lojas → Configurar → Credenciais MP**
5. Configure o Webhook: `https://seudominio.com.br/webhook/mercadopago`

### Evolution API (WhatsApp)

1. Instale a [Evolution API](https://github.com/EvolutionAPI/evolution-api)
2. Crie uma instância e conecte seu WhatsApp
3. Configure no `.env`:
   ```env
   EVOLUTION_API_URL=https://sua-api.com
   EVOLUTION_API_KEY=sua-chave
   EVOLUTION_INSTANCE=lanchonete
   ```
4. No painel admin: **Lojas → Configurar → Ativar WhatsApp**

### Entrega por Bairros

No painel admin: **Lojas → Configurar → Bairros de Entrega**

Configure:
- Nome do bairro
- Cidade e estado
- Taxa de entrega
- Tempo estimado

### Taxa por KM

Configure no painel admin:
- Tipo de taxa: **Por KM**
- Valor por KM
- Raio máximo de entrega (km)

### Horários de Funcionamento

No painel admin: **Lojas → Configurar → Horários**

---

## 📱 PWA (Progressive Web App)

O sistema é um PWA completo. Para instalar no celular:

1. Acesse o site pelo **Chrome no Android** ou **Safari no iOS**
2. Um banner aparecerá: **"Adicionar à tela inicial"**
3. Ou toque no menu do navegador → **"Adicionar à tela inicial"**

### Ícones PWA

Coloque os ícones em `/public/img/icones/`:
- `icon-72x72.png`
- `icon-96x96.png`
- `icon-128x128.png`
- `icon-144x144.png`
- `icon-152x152.png`
- `icon-192x192.png`
- `icon-384x384.png`
- `icon-512x512.png`

Use o [PWA Asset Generator](https://www.pwabuilder.com/imageGenerator) para gerar todos os tamanhos.

---

## 🏪 Sistema Multi-Loja

Cada loja tem seu próprio:
- URL: `seudominio.com.br/{slug-da-loja}`
- Cardápio, categorias e produtos
- Funcionários e entregadores
- Configurações de entrega
- Credenciais do MercadoPago
- Instância Evolution API

Para criar uma nova loja: Painel Admin → **Lojas → Nova Loja**

---

## 👥 Usuários e Permissões

| Role | Acesso |
|---|---|
| `super_admin` | Acesso total a todas as lojas |
| `admin` | Acesso total à sua loja |
| `gerente` | Gerencia pedidos, produtos, funcionários |
| `atendente` | Visualiza e gerencia pedidos |
| `entregador` | Painel do entregador (aceitar/confirmar entregas) |
| `cliente` | Área do cliente (pedidos, perfil, endereços) |

---

## 💳 Pagamentos Suportados

| Método | Quando disponível |
|---|---|
| PIX | Sempre (online) |
| Cartão de Crédito | Sempre (checkout transparente MP) |
| Cartão de Débito | Sempre (checkout transparente MP) |
| Pagamento na Entrega | Somente com entregador próprio cadastrado |

---

## 🛵 Tipos de Taxa de Entrega

| Tipo | Configuração |
|---|---|
| **Taxa Fixa** | Valor único para qualquer entrega |
| **Por Bairro** | Valor diferente por bairro |
| **Por KM** | Calculado pela distância via ViaCEP + Haversine |
| **Grátis** | Sem cobrança de entrega |

---

## 📡 Rastreamento em Tempo Real

1. Entregador aceita a entrega no app
2. Localização é enviada automaticamente via GPS (watchPosition)
3. Cliente recebe link de rastreamento via WhatsApp
4. Link público: `seudominio.com.br/rastreamento/{token}`
5. Mapa OpenStreetMap mostra posição ao vivo

---

## 🔄 Estrutura de Arquivos Principais

```
lanchonete/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Dashboard, Pedido, Produto, Loja, Categoria...
│   │   │   ├── Api/            # CepApi, CupomApi, ProdutoApi, PedidoApi
│   │   │   ├── Auth/           # AuthController
│   │   │   ├── Cliente/        # Home, Checkout, PedidoCliente
│   │   │   ├── Entregador/     # EntregadorController
│   │   │   ├── Perfil/         # PerfilController
│   │   │   └── WebhookController, RastreamentoController
│   │   └── Middleware/
│   │       ├── CheckRole.php
│   │       ├── IdentificarLoja.php
│   │       └── LojaAtiva.php
│   ├── Models/                 # Todos os Models Eloquent
│   ├── Services/               # MercadoPago, EvolutionApi, Entrega, Cep, Upload, Pedido
│   ├── Events/                 # PedidoStatusAtualizado, LocalizacaoEntregadorAtualizada
│   └── Providers/              # AppServiceProvider
├── database/
│   ├── migrations/             # Todas as migrations
│   └── seeders/                # Dados iniciais
├── resources/views/
│   ├── layouts/                # pwa.blade.php, admin.blade.php
│   ├── auth/                   # login, registro, esqueceu-senha
│   ├── cliente/                # home, checkout, pedidos, lojas
│   ├── admin/                  # dashboard, pedidos, produtos, categorias, relatorios
│   ├── entregador/             # dashboard
│   ├── perfil/                 # index
│   ├── rastreamento/           # publico
│   └── errors/                 # 503
├── public/
│   ├── css/                    # app.css (PWA), admin.css
│   ├── js/                     # app.js, admin.js
│   ├── img/                    # pix.svg, icones/
│   ├── manifest.json           # PWA manifest
│   └── sw.js                   # Service Worker
├── routes/
│   ├── web.php
│   ├── api.php
│   ├── channels.php
│   └── console.php
└── config/
    ├── lanchonete.php          # Configurações centrais do sistema
    └── auth.php
```

---

## 🐛 Solução de Problemas

### Erro 500 após deploy
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
chmod -R 775 storage bootstrap/cache
```

### Migrations falhando
- Verifique se o usuário MySQL tem permissão `CREATE TABLE`
- Verifique se o charset do banco é `utf8mb4`

### Upload de imagens não funciona
```bash
php artisan storage:link
chmod -R 775 storage/app/public
```

### WhatsApp não envia
- Verifique se a instância Evolution API está conectada
- Confirme o número do WhatsApp no formato internacional (55119xxxxx)
- Ative a opção no painel: Lojas → Configurar → Notificações WhatsApp

### PIX não aparece
- Confirme `MERCADOPAGO_SANDBOX=false` em produção
- Verifique se o `MERCADOPAGO_ACCESS_TOKEN` é de produção
- Confirme CPF do cliente no checkout

---

## 📞 Suporte

Para dúvidas e suporte técnico, consulte a documentação:
- [Laravel 12 Docs](https://laravel.com/docs/12.x)
- [MercadoPago Developers](https://www.mercadopago.com.br/developers)
- [Evolution API Docs](https://doc.evolution-api.com)

---

## 📄 Licença

Este sistema é proprietário. Todos os direitos reservados.
