# 📝 Configuração do Nome da Aplicação

**Data:** 12 de outubro de 2025  
**Domínio:** valedosol.org

---

## ⚙️ Como Configurar

### **1. Atualizar .env**

```env
# .env
APP_NAME="Vale do Sol"
APP_URL=https://valedosol.org
```

**⚠️ Importante:**
- Use aspas se o nome tiver espaços
- Não use caracteres especiais (ç, ã, etc) - podem causar problemas em alguns contextos
- O nome aparecerá em: logo, footer, emails, notificações, title tags

---

### **2. Limpar Cache (Após Mudar .env)**

```bash
# Limpar config cache
php artisan config:clear

# Limpar cache geral
php artisan cache:clear

# Ou fazer tudo:
php artisan optimize:clear
```

---

## 📍 Onde o APP_NAME é Usado

### **✅ Já Implementado (Dinâmico):**

#### **Layouts:**
```blade
<!-- public.blade.php -->
<a class="navbar-brand">
    <i class="bi bi-sun me-2"></i>
    {{ config('app.name') }}
</a>

<h5>{{ config('app.name') }}</h5>

&copy; {{ date('Y') }} {{ config('app.name') }} Marketplace

<!-- admin.blade.php -->
{{ config('app.name') }} Admin

<!-- seller.blade.php -->
{{ config('app.name') }} Vendedor

<!-- guest.blade.php -->
<h1>{{ config('app.name') }}</h1>
```

#### **Title Tags:**
```blade
<!-- home.blade.php -->
@section('title', config('app.name') . ' - Onde o comércio tem rosto...')

<!-- Todos os layouts usam: -->
<title>@yield('title', config('app.name'))</title>
```

---

### **📧 Emails:**

```blade
<!-- emails/orders/confirmed.blade.php -->
<h2>{{ config('app.name') }}</h2>

<p>Obrigado por comprar no {{ config('app.name') }}!</p>
```

---

### **🔔 Notificações:**

```php
// app/Notifications/*
$this->line('Bem-vindo ao ' . config('app.name'));
```

---

## 🎨 Branding Completo

### **Componentes de Marca:**

```
Logo: 🌞 + config('app.name')
Cores: Verde Mata (#6B8E23) + Terracota (#D2691E) + Dourado (#DAA520)
Tagline: "Onde o comércio tem rosto e a economia tem coração"
```

### **Aplicação:**

```blade
<!-- Padrão Visual -->
<a href="/" class="navbar-brand">
    <i class="bi bi-sun me-2"></i>  <!-- Ícone dourado -->
    {{ config('app.name') }}         <!-- Nome verde -->
</a>

<!-- Auth Pages (Guest Layout) -->
<h1 class="text-white">
    <i class="bi bi-sun me-2"></i>
    {{ config('app.name') }}
</h1>
```

---

## 🌐 Domínio: valedosol.org

### **Produção (.env.production):**

```env
APP_NAME="Vale do Sol"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://valedosol.org

# ... outras configurações
```

### **Local (.env):**

```env
APP_NAME="Vale do Sol"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

---

## 📖 Fallback

Se `APP_NAME` não estiver definido no `.env`, o sistema usa o fallback:

```php
// config/app.php
'name' => env('APP_NAME', 'Laravel'),
```

**Resultado:** Mostrará "Laravel" se APP_NAME não existir.

---

## ✅ Checklist de Configuração

### **Setup Inicial:**
- [ ] Editar `.env`
- [ ] Adicionar `APP_NAME="Vale do Sol"`
- [ ] Adicionar `APP_URL=https://valedosol.org`
- [ ] Executar `php artisan config:clear`
- [ ] Verificar `config('app.name')` retorna correto

### **Verificação Visual:**
- [ ] Abrir http://localhost:8000
- [ ] Logo mostra nome correto
- [ ] Footer mostra nome correto
- [ ] Title tag mostra nome correto
- [ ] Auth pages mostram nome correto
- [ ] Admin/Seller panels mostram nome correto

---

## 🔧 Comandos Úteis

### **Ver Configuração Atual:**
```bash
php artisan tinker
>>> config('app.name')
=> "Vale do Sol"
```

### **Testar em Todas as Views:**
```bash
# Buscar views que ainda usam hardcoded
grep -r "Vale do Sol" resources/views --exclude-dir=vendor

# Substituir se necessário
# Já fizemos isso! ✅
```

---

## 📝 Boas Práticas

### **✅ FAZER:**
```blade
<!-- Sempre usar config() -->
{{ config('app.name') }}

<!-- Com fallback customizado -->
{{ config('app.name', 'Marketplace') }}

<!-- Em strings -->
@section('title', config('app.name') . ' - Página Inicial')
```

### **❌ EVITAR:**
```blade
<!-- Nunca hardcode o nome -->
Vale do Sol  ❌

<!-- Evitar em múltiplos lugares -->
<h1>Vale do Sol</h1>  ❌
```

---

## 🎯 Resultado Final

### **Antes:**
```blade
<!-- Nome hardcoded em 19 arquivos -->
<a>Vale do Sol</a>
<h1>Vale do Sol</h1>
<title>Vale do Sol - ...</title>

Problema: Mudar nome = editar 19 arquivos
```

### **Depois:**
```blade
<!-- Nome dinâmico via config -->
<a>{{ config('app.name') }}</a>
<h1>{{ config('app.name') }}</h1>
<title>{{ config('app.name') }} - ...</title>

Solução: Mudar nome = editar 1 linha no .env ✅
```

---

## 🚀 Deployment

### **Produção (valedosol.org):**

```bash
# 1. Configurar .env de produção
APP_NAME="Vale do Sol"
APP_URL=https://valedosol.org

# 2. Limpar caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Build assets
npm run build

# 4. Deploy
# (seguir DEPLOYMENT.md)
```

---

**Nome da aplicação agora centralizado via `config('app.name')`!** ✅

**Configurar no .env:**
```env
APP_NAME="Vale do Sol"
```

**Ou qualquer outro nome desejado!** 🎨

