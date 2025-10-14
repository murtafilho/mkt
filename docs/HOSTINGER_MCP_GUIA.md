# Guia: Hostinger + MCP (Model Context Protocol)

**Data:** 2025-01-14
**Contexto:** Análise de hospedagem Hostinger para Laravel + MCP Server

---

## 📋 Sumário Executivo

**Conclusão:** Para rodar Laravel + MCP Server na Hostinger, você **PRECISA de VPS**, hospedagem compartilhada **NÃO é compatível** com MCP.

**Recomendação:** Hostinger VPS Plan 2 ou superior (mínimo 4GB RAM, 2 vCPU)

---

## 1. Hostinger - Características e Planos (2025)

### 🌐 Hospedagem Compartilhada (Shared Hosting)

**Características:**
- ✅ Ideal para sites estáticos, WordPress, Laravel simples
- ✅ Preço baixo: R$ 9,99/mês - R$ 29,99/mês
- ✅ Painel hPanel intuitivo
- ✅ PHP 8.2+, MySQL, acesso SSH limitado
- ❌ **Recursos compartilhados** (CPU, RAM)
- ❌ **Sem Node.js nativo** (limitação crítica)
- ❌ **Sem controle de processos** (PM2, systemd)
- ❌ **Sem Docker/containers**

**Veredito Shared Hosting:**
❌ **NÃO COMPATÍVEL** com MCP Server (requer Node.js, processos persistentes)

---

### 🚀 VPS Hosting (Virtual Private Server)

**Características:**
- ✅ Recursos dedicados (RAM, CPU, storage)
- ✅ Full root access (controle total)
- ✅ Suporte a Node.js, Python, Docker
- ✅ Gerenciamento de processos (PM2, systemd)
- ✅ AMD EPYC CPUs + NVMe SSD
- ✅ 1 IP dedicado + firewall configurável
- ✅ Snapshots e backups semanais
- ⚠️ **Não gerenciado** (você configura tudo)
- ⚠️ Renovação 3x mais cara que promo inicial

**Planos VPS Hostinger (2025):**

| Plano | RAM | vCPU | Storage | Preço Inicial | Renovação |
|-------|-----|------|---------|---------------|-----------|
| VPS 1 | 1 GB | 1 | 20 GB NVMe | $4.99/mês | ~$14.99/mês |
| VPS 2 | 2 GB | 1 | 40 GB NVMe | $6.99/mês | ~$19.99/mês |
| VPS 3 | 4 GB | 2 | 80 GB NVMe | $9.99/mês | ~$29.99/mês |
| VPS 4 | 8 GB | 4 | 160 GB NVMe | $15.99/mês | ~$45.99/mês |
| VPS 8 | 16 GB | 8 | 320 GB NVMe | $34.99/mês | ~$99.99/mês |

**Data Center no Brasil:** ✅ Disponível (baixa latência)

---

## 2. MCP (Model Context Protocol) - Requisitos

### 🖥️ Requisitos de Hardware

**Mínimos (MCP básico):**
- CPU: 2 vCPUs
- RAM: 4 GB
- Storage: 40 GB NVMe SSD
- Network: 1 Gbps

**Recomendados (Produção):**
- CPU: 4-8 vCPUs (multi-core AMD EPYC)
- RAM: 8-16 GB
- Storage: 80-160 GB NVMe SSD
- Network: 1 Gbps + baixa latência

### 📦 Requisitos de Software

**Obrigatório:**
- ✅ Node.js 18+ (TypeScript/JavaScript MCP SDK)
- ✅ Python 3.9+ (Python MCP SDK 1.2.0+)
- ✅ Gerenciador de processos (PM2 ou systemd)
- ✅ Reverse proxy (Nginx/Traefik) para SSL
- ✅ Docker (opcional, mas recomendado)

**Protocolos de Transporte:**
- Legacy HTTP+SSE (conexões persistentes)
- **Streamable HTTP** (2025-03-26 spec) - mais eficiente

**Limitação Importante:**
⚠️ HTTP+SSE **NÃO funciona** em serverless (Google Cloud Run, Vercel, Netlify) - requer VPS/dedicado

### 🔐 Segurança e Autenticação

- SSL/TLS obrigatório (Let's Encrypt via Nginx)
- Access tokens para autenticação
- Firewall configurável
- DDoS protection (incluído no VPS Hostinger)

---

## 3. Compatibilidade: Hostinger + MCP

### ❌ Shared Hosting + MCP = **INCOMPATÍVEL**

**Razões:**
1. Sem suporte nativo a Node.js
2. Sem controle de processos (PM2 bloqueado)
3. Sem Docker
4. Recursos compartilhados (instabilidade)
5. SSH limitado (sem root access)

### ✅ VPS Hostinger + MCP = **COMPATÍVEL**

**Recomendação de Plano:**

| Cenário | Plano Mínimo | Ideal |
|---------|--------------|-------|
| Desenvolvimento/Teste | VPS 2 (2GB RAM, 1 vCPU) | VPS 3 (4GB, 2 vCPU) |
| **Produção (Projeto Atual)** | **VPS 3 (4GB, 2 vCPU)** | **VPS 4 (8GB, 4 vCPU)** |
| Alta carga (100+ req/s) | VPS 4 (8GB, 4 vCPU) | VPS 8 (16GB, 8 vCPU) |

**Para o Marketplace B2C Multivendor atual:**
- Laravel 12 + MySQL + Queue + MCP Server
- **Recomendado:** VPS 3 ou VPS 4
- **Justificativa:** Laravel + MySQL (2GB) + MCP Server (1-2GB) + margem de segurança

---

## 4. Arquitetura de Deploy (Hostinger VPS)

### 🏗️ Stack Completo

```
┌─────────────────────────────────────┐
│  Cloudflare (CDN + DDoS Protection) │ Opcional
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│  Hostinger VPS (Ubuntu 22.04)       │
│  ┌─────────────────────────────────┐│
│  │  Nginx (Reverse Proxy + SSL)    ││
│  │  • Laravel: port 8000 → /       ││
│  │  • MCP Server: port 3000 → /mcp ││
│  └─────────────────────────────────┘│
│                                      │
│  ┌──────────────┬──────────────────┐│
│  │ Laravel 12   │  MCP Server      ││
│  │ (PHP-FPM)    │  (Node.js 18)    ││
│  │ Port: 8000   │  Port: 3000      ││
│  │              │  (PM2 managed)   ││
│  └──────────────┴──────────────────┘│
│                                      │
│  ┌─────────────────────────────────┐│
│  │  MySQL 8.0                       ││
│  │  Database: mkt                   ││
│  └─────────────────────────────────┘│
│                                      │
│  ┌─────────────────────────────────┐│
│  │  Redis (Cache + Queue)           ││ Opcional
│  └─────────────────────────────────┘│
└──────────────────────────────────────┘
```

### 📂 Estrutura de Diretórios

```bash
/var/www/
├── mkt-laravel/              # Laravel App
│   ├── public/               # Document root (Nginx)
│   ├── .env                  # Laravel config
│   └── storage/              # Logs, cache
│
└── mcp-server/               # MCP Server
    ├── src/
    ├── package.json
    ├── ecosystem.config.js   # PM2 config
    └── .env                  # MCP config
```

---

## 5. Guia de Configuração Passo a Passo

### 🔧 Passo 1: Provisionar VPS Hostinger

1. **Comprar VPS Plan 3 ou 4**
   - Escolher data center: **Brazil** (baixa latência)
   - Sistema operacional: **Ubuntu 22.04 LTS**
   - Ativar backups automáticos

2. **Acessar via SSH**
   ```bash
   ssh root@seu-ip-vps
   ```

### 🔧 Passo 2: Configurar Servidor (Ubuntu 22.04)

#### 2.1 Atualizar Sistema
```bash
apt update && apt upgrade -y
```

#### 2.2 Instalar PHP 8.2 + Extensões
```bash
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring \
  php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip php8.2-gd \
  php8.2-intl php8.2-redis php8.2-imagick
```

#### 2.3 Instalar MySQL 8.0
```bash
apt install -y mysql-server
mysql_secure_installation
```

Criar database:
```sql
mysql -u root -p
CREATE DATABASE mkt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mkt_user'@'localhost' IDENTIFIED BY 'senha_segura';
GRANT ALL PRIVILEGES ON mkt.* TO 'mkt_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 2.4 Instalar Nginx
```bash
apt install -y nginx
```

#### 2.5 Instalar Node.js 18 LTS
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs
```

Verificar:
```bash
node -v  # v18.x.x
npm -v   # 9.x.x
```

#### 2.6 Instalar PM2
```bash
npm install -g pm2
pm2 startup systemd  # Configurar autostart
```

#### 2.7 Instalar Composer
```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

### 🔧 Passo 3: Deploy Laravel

#### 3.1 Clonar Repositório
```bash
cd /var/www/
git clone https://github.com/seu-usuario/mkt.git mkt-laravel
cd mkt-laravel
```

#### 3.2 Instalar Dependências
```bash
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

#### 3.3 Configurar .env
```bash
cp .env.example .env
nano .env
```

Ajustar:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seudominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mkt
DB_USERNAME=mkt_user
DB_PASSWORD=senha_segura

QUEUE_CONNECTION=database
CACHE_DRIVER=file
SESSION_DRIVER=database
```

#### 3.4 Gerar Key + Migrate
```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 3.5 Permissões
```bash
chown -R www-data:www-data /var/www/mkt-laravel
chmod -R 775 /var/www/mkt-laravel/storage
chmod -R 775 /var/www/mkt-laravel/bootstrap/cache
```

### 🔧 Passo 4: Deploy MCP Server

#### 4.1 Criar Projeto MCP
```bash
cd /var/www/
mkdir mcp-server
cd mcp-server
npm init -y
npm install @modelcontextprotocol/sdk
```

#### 4.2 Criar Server Básico (src/index.js)
```javascript
import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';

const server = new Server({
  name: 'mkt-mcp-server',
  version: '1.0.0',
}, {
  capabilities: {
    tools: {},
  },
});

// Adicionar tools personalizados aqui

const transport = new StdioServerTransport();
await server.connect(transport);
```

#### 4.3 Configurar PM2 (ecosystem.config.js)
```javascript
module.exports = {
  apps: [{
    name: 'mcp-server',
    script: './src/index.js',
    instances: 2,  // Cluster mode
    exec_mode: 'cluster',
    env: {
      NODE_ENV: 'production',
      PORT: 3000
    },
    error_file: './logs/err.log',
    out_file: './logs/out.log',
    log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
  }]
};
```

#### 4.4 Iniciar MCP Server
```bash
pm2 start ecosystem.config.js
pm2 save
pm2 list
```

### 🔧 Passo 5: Configurar Nginx

#### 5.1 Criar Config Laravel
```bash
nano /etc/nginx/sites-available/mkt-laravel
```

```nginx
server {
    listen 80;
    server_name seudominio.com www.seudominio.com;
    root /var/www/mkt-laravel/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### 5.2 Ativar Site
```bash
ln -s /etc/nginx/sites-available/mkt-laravel /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

#### 5.3 Configurar SSL (Let's Encrypt)
```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d seudominio.com -d www.seudominio.com
```

### 🔧 Passo 6: Configurar Queue Worker

```bash
nano /etc/systemd/system/laravel-worker.service
```

```ini
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/mkt-laravel/artisan queue:work --sleep=3 --tries=3

[Install]
WantedBy=multi-user.target
```

Iniciar:
```bash
systemctl enable laravel-worker
systemctl start laravel-worker
systemctl status laravel-worker
```

---

## 6. Custos Estimados

### 💰 Plano VPS 3 (Recomendado)

**Primeiro Ano:**
- Promo inicial: $9.99/mês × 12 = **$119.88/ano**
- Domínio .com: ~$12/ano
- SSL: Grátis (Let's Encrypt)
- **Total Ano 1:** ~$132/ano (~R$ 660/ano)

**Renovação (Ano 2+):**
- VPS 3: $29.99/mês × 12 = **$359.88/ano**
- Domínio: $12/ano
- **Total Ano 2+:** ~$372/ano (~R$ 1.860/ano)

### 💰 Plano VPS 4 (Alta Performance)

**Primeiro Ano:**
- Promo inicial: $15.99/mês × 12 = **$191.88/ano**
- **Total Ano 1:** ~$204/ano (~R$ 1.020/ano)

**Renovação (Ano 2+):**
- VPS 4: $45.99/mês × 12 = **$551.88/ano**
- **Total Ano 2+:** ~$564/ano (~R$ 2.820/ano)

---

## 7. Alternativas à Hostinger

Se o custo de renovação da Hostinger for muito alto, considere:

### 🌐 VPS Alternativas (Brasil)

| Provider | RAM | vCPU | Storage | Preço/mês |
|----------|-----|------|---------|-----------|
| **Contabo** | 4 GB | 2 | 100 GB SSD | €4.99 (~R$ 27) |
| **Hetzner** | 4 GB | 2 | 40 GB SSD | €4.51 (~R$ 24) |
| **DigitalOcean** | 4 GB | 2 | 80 GB SSD | $24/mês (~R$ 120) |
| **Vultr** | 4 GB | 2 | 80 GB SSD | $18/mês (~R$ 90) |
| **Linode (Akamai)** | 4 GB | 2 | 80 GB SSD | $24/mês (~R$ 120) |

**Vantagens Alternativas:**
- Preço estável (sem promos enganosas)
- Melhor documentação
- Suporte a Terraform/Ansible

**Desvantagens:**
- Sem data center no Brasil (latência +50ms)
- Interface em inglês
- Suporte técnico mais limitado

---

## 8. Checklist de Deploy

### ✅ Pré-Deploy
- [ ] Comprar VPS Hostinger (Plan 3 ou 4)
- [ ] Registrar domínio (.com.br ou .com)
- [ ] Configurar DNS (apontar A record para IP VPS)
- [ ] Acessar VPS via SSH

### ✅ Configuração Servidor
- [ ] Atualizar Ubuntu (`apt update && upgrade`)
- [ ] Instalar PHP 8.2 + extensões
- [ ] Instalar MySQL 8.0
- [ ] Instalar Nginx
- [ ] Instalar Node.js 18 + PM2
- [ ] Instalar Composer
- [ ] Configurar firewall (UFW: portas 80, 443, 22)

### ✅ Deploy Laravel
- [ ] Clonar repositório Git
- [ ] `composer install --no-dev`
- [ ] `npm install && npm run build`
- [ ] Configurar `.env` (production)
- [ ] `php artisan migrate --force`
- [ ] Configurar Nginx virtual host
- [ ] Configurar SSL (Let's Encrypt)
- [ ] Configurar queue worker (systemd)

### ✅ Deploy MCP Server
- [ ] Criar diretório `/var/www/mcp-server`
- [ ] Instalar dependências MCP SDK
- [ ] Configurar `ecosystem.config.js` (PM2)
- [ ] `pm2 start ecosystem.config.js`
- [ ] `pm2 save && pm2 startup`

### ✅ Testes
- [ ] Acessar site: `https://seudominio.com`
- [ ] Testar login admin/seller
- [ ] Testar cadastro de produto
- [ ] Testar carrinho + checkout
- [ ] Verificar queue worker: `systemctl status laravel-worker`
- [ ] Verificar MCP server: `pm2 logs mcp-server`
- [ ] Testar webhooks Mercado Pago

### ✅ Monitoramento
- [ ] Configurar backup automático (Hostinger snapshots)
- [ ] Configurar logs (`/var/log/nginx/`, `storage/logs/`)
- [ ] Configurar alertas (Uptime Robot, StatusCake)
- [ ] Documentar credenciais (1Password, Bitwarden)

---

## 9. Troubleshooting Comum

### ❌ Erro: "502 Bad Gateway"
**Causa:** PHP-FPM não está rodando
**Solução:**
```bash
systemctl status php8.2-fpm
systemctl restart php8.2-fpm
```

### ❌ Erro: "Permission denied" no storage/
**Causa:** Permissões incorretas
**Solução:**
```bash
chown -R www-data:www-data /var/www/mkt-laravel/storage
chmod -R 775 /var/www/mkt-laravel/storage
```

### ❌ MCP Server não inicia
**Causa:** Porta 3000 ocupada ou Node.js desatualizado
**Solução:**
```bash
lsof -i :3000  # Verificar porta
pm2 logs mcp-server  # Ver logs
node -v  # Verificar versão (precisa 18+)
```

### ❌ Queue worker não processa jobs
**Causa:** Service não está ativo
**Solução:**
```bash
systemctl status laravel-worker
systemctl restart laravel-worker
php artisan queue:work --once  # Testar manualmente
```

---

## 10. Recursos Adicionais

### 📚 Documentação Oficial

- **Hostinger VPS:** https://www.hostinger.com/tutorials/how-to-deploy-laravel
- **MCP Protocol:** https://modelcontextprotocol.io/quickstart/server
- **Laravel Deployment:** https://laravel.com/docs/12.x/deployment
- **PM2 Docs:** https://pm2.keymetrics.io/docs/usage/quick-start/

### 🛠️ Ferramentas Úteis

- **Laravel Forge:** Gerenciamento automatizado de VPS ($12/mês) - alternativa ao setup manual
- **Ploi.io:** Similar ao Forge ($10/mês)
- **Cloudflare:** CDN + DDoS protection (plano gratuito)
- **Uptime Robot:** Monitoramento de uptime (plano gratuito 50 sites)

---

## 📝 Conclusão

**Para o Marketplace B2C Multivendor atual:**

✅ **Recomendação Final:** Hostinger VPS 3 (4GB RAM, 2 vCPU, 80GB NVMe)

**Justificativa:**
- Laravel 12 + MySQL (~2GB RAM)
- MCP Server Node.js (~1-2GB RAM)
- Margem para picos de tráfego
- Preço acessível no primeiro ano ($120)
- Data center no Brasil (baixa latência)
- Full root access (controle total)

**Próximos Passos:**
1. Comprar VPS Hostinger Plan 3 (promo $9.99/mês)
2. Seguir checklist de deploy (seção 8)
3. Testar em produção
4. Monitorar performance (considerar upgrade para VPS 4 se necessário)

**Lembrete:** Renovação será 3x mais cara ($30/mês) - considerar migração para Contabo/Hetzner no futuro.

---

**Documentação criada por:** Claude Code
**Última atualização:** 2025-01-14
