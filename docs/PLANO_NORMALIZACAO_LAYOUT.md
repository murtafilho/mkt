# Plano de Normalização de Layout e Estilos
**Marketplace Vale do Sol - Bootstrap 5.3 + SCSS + Blade @extends**

## Objetivo

Normalizar sistematicamente todas as views para usar:
1. **Blade @extends** (ao invés de componentes `<x-layouts.*>`)
2. **Bootstrap 5.3** nativo (componentes oficiais)
3. **SCSS centralizado** (`resources/sass/app.scss` + variáveis customizadas)

## Estado Atual (Auditoria Completa)

### ✅ O que está BEM

**Estilos SCSS:**
- ✅ Bootstrap 5.3.3 instalado via npm
- ✅ SCSS bem estruturado em `resources/sass/`
- ✅ Variáveis customizadas centralizadas (`_variables.scss`)
- ✅ Paleta Vale do Sol definida (Verde Mata #588c4c, Terracota #D2691E, Dourado #DAA520)
- ✅ 7 arquivos parciais de componentes (`components/`)
- ✅ Vite configurado para compilar `resources/sass/app.scss`

**Layouts:**
- ✅ `layouts/base.blade.php` - Master layout (HTML, head, Vite, Alpine.js)
- ✅ `layouts/public.blade.php` - Layout público (extends base)
- ✅ `layouts/admin.blade.php` - Painel admin (extends base)
- ✅ `layouts/seller.blade.php` - Dashboard vendedor (extends base)
- ✅ `layouts/app.blade.php` - Usuário autenticado (extends base)
- ✅ `layouts/guest.blade.php` - Login/Registro (extends base)
- ✅ `layouts/partials/header.blade.php` - Header via @include

**Views:**
- ✅ 38 views já usam `@extends('layouts.*)`
- ✅ Maioria das views públicas, admin, seller, customer, checkout

### ⚠️ O que precisa SER NORMALIZADO

**1. Duplicação de Layouts (6 arquivos a remover):**
```
❌ resources/views/components/layouts/base.blade.php
❌ resources/views/components/layouts/app.blade.php
❌ resources/views/components/layouts/admin.blade.php
❌ resources/views/components/layouts/seller.blade.php
```

**2. Views usando `<x-layouts.*>` (3 arquivos a converter):**
```
❌ admin/reports/sellers.blade.php
❌ admin/categories/create.blade.php
❌ admin/categories/edit.blade.php
```

**3. Duplicação de CSS:**
```
⚠️ resources/css/app.css (404 linhas - CSS puro com variáveis :root)
⚠️ resources/sass/app.scss (485 linhas - SCSS com imports)
```
**Problema:** Vite compila apenas `resources/sass/app.scss`, então `resources/css/app.css` pode estar obsoleto ou causando confusão.

**4. Componentes SCSS a revisar:**
- `components/_header.scss` (685 linhas - muito extenso, pode ser modularizado)
- `components/_product-card.scss` (225 linhas - OK)
- `components/_hero.scss`
- `components/_categories.scss`
- `components/_search.scss`
- `components/_auth.scss`
- `components/_modern-forms.scss`

## Plano de Normalização (5 Fases)

---

### 📋 FASE 1: Análise e Backup
**Objetivo:** Garantir segurança antes das mudanças

**Tarefas:**
- [ ] **1.1** Criar branch de desenvolvimento: `git checkout -b normalize-layouts`
- [ ] **1.2** Executar testes atuais: `php artisan test` (baseline)
- [ ] **1.3** Compilar assets atuais: `npm run build` (baseline)
- [ ] **1.4** Fazer backup de `resources/views/components/layouts/` (para comparação)
- [ ] **1.5** Documentar quais views usam quais layouts (mapeamento completo)

**Comandos:**
```bash
# Criar branch
git checkout -b normalize-layouts

# Baseline de testes
php artisan test > tests_baseline.txt

# Baseline de assets
npm run build
```

---

### 🔄 FASE 2: Converter Views de `<x-layouts>` para `@extends`
**Objetivo:** Eliminar uso de componentes Blade para layouts

#### 2.1 Converter `admin/categories/create.blade.php`

**Antes:**
```blade
<x-layouts.admin>
    <x-slot name="header">Criar Categoria</x-slot>
    <x-slot name="content">
        <!-- conteúdo -->
    </x-slot>
</x-layouts.admin>
```

**Depois:**
```blade
@extends('layouts.admin')

@section('header', 'Criar Categoria')

@section('page-content')
    <!-- conteúdo -->
@endsection
```

**Tarefas:**
- [ ] **2.1.1** Converter `admin/categories/create.blade.php`
- [ ] **2.1.2** Converter `admin/categories/edit.blade.php`
- [ ] **2.1.3** Converter `admin/reports/sellers.blade.php`
- [ ] **2.1.4** Testar rotas admin: `/admin/categories/create`, `/admin/categories/{id}/edit`, `/admin/reports/sellers`
- [ ] **2.1.5** Validar visualmente (npm run dev + php artisan serve)

**Verificação:**
```bash
# Buscar uso restante de <x-layouts
grep -r "<x-layouts\." resources/views --exclude-dir=components

# Deve retornar ZERO resultados (exceto components/layouts/)
```

---

### 🗑️ FASE 3: Remover Layouts Duplicados
**Objetivo:** Eliminar `resources/views/components/layouts/`

**Tarefas:**
- [ ] **3.1** Verificar que NENHUMA view usa mais `<x-layouts.*>` (grep)
- [ ] **3.2** Remover diretório completo:
  ```bash
  rm -rf resources/views/components/layouts/
  ```
- [ ] **3.3** Executar testes: `php artisan test` (garantir zero quebras)
- [ ] **3.4** Compilar assets: `npm run build` (garantir zero erros)

**Verificação:**
```bash
# Confirmar que diretório não existe mais
ls resources/views/components/layouts/
# Deve retornar: "No such file or directory"
```

---

### 🎨 FASE 4: Normalizar Estilos SCSS
**Objetivo:** Centralizar 100% dos estilos em `resources/sass/app.scss`

#### 4.1 Resolver Duplicação CSS vs SCSS

**Análise:**
- `resources/css/app.css` - 404 linhas, variáveis CSS `:root`
- `resources/sass/app.scss` - 485 linhas, imports Bootstrap + componentes
- **Vite compila:** Apenas `resources/sass/app.scss` (verificado em `vite.config.js`)

**Decisão:**
- ✅ **MANTER:** `resources/sass/app.scss` (fonte única de verdade)
- ❌ **REMOVER:** `resources/css/app.css` (obsoleto, não compilado)

**Tarefas:**
- [ ] **4.1.1** Verificar se `resources/css/app.css` contém algo que NÃO está em SCSS
- [ ] **4.1.2** Migrar qualquer customização única para `resources/sass/app.scss`
- [ ] **4.1.3** Remover `resources/css/app.css`
- [ ] **4.1.4** Atualizar `.gitignore` se necessário

**Verificação:**
```bash
# Compilar e verificar se nada quebrou
npm run build

# Verificar que arquivo foi removido
ls resources/css/app.css
# Deve retornar: "No such file or directory"
```

#### 4.2 Reorganizar Componentes SCSS

**Objetivo:** Modularizar `components/_header.scss` (685 linhas - muito extenso)

**Plano de Modularização:**
```
resources/sass/
├── _variables.scss (✅ OK - 153 linhas)
├── app.scss (principal)
├── components/
│   ├── _header.scss → DIVIDIR EM:
│   │   ├── _navbar.scss (navbar + navegação)
│   │   ├── _buttons.scss (botões customizados)
│   │   ├── _footer.scss (footer)
│   ├── _hero.scss (✅ manter)
│   ├── _categories.scss (✅ manter)
│   ├── _search.scss (✅ manter)
│   ├── _product-card.scss (✅ manter)
│   ├── _auth.scss (✅ manter)
│   ├── _modern-forms.scss (✅ manter)
```

**Tarefas:**
- [ ] **4.2.1** Criar `components/_navbar.scss` (extrair linhas 1-169, 433-477 de _header.scss)
- [ ] **4.2.2** Criar `components/_buttons.scss` (extrair linhas 190-431 de _header.scss)
- [ ] **4.2.3** Criar `components/_footer.scss` (extrair linhas 433-477 de _header.scss)
- [ ] **4.2.4** Atualizar `app.scss` com novos imports:
  ```scss
  @import 'components/navbar';
  @import 'components/buttons';
  @import 'components/footer';
  ```
- [ ] **4.2.5** Remover/reduzir `components/_header.scss` (manter apenas top-bar e ícones específicos)
- [ ] **4.2.6** Compilar e testar: `npm run dev`

**Verificação:**
```bash
# Compilar sem erros
npm run build

# Verificar output (deve ser idêntico ao anterior)
# Comparar public/build/assets/app-*.css antes e depois
```

#### 4.3 Centralizar Variáveis Bootstrap

**Objetivo:** Garantir que TODAS as customizações Bootstrap estão em `_variables.scss`

**Tarefas:**
- [ ] **4.3.1** Revisar `resources/sass/_variables.scss` (153 linhas)
- [ ] **4.3.2** Garantir que cores, fontes, espaçamentos estão centralizados
- [ ] **4.3.3** Remover qualquer variável duplicada em `app.scss` ou componentes
- [ ] **4.3.4** Documentar todas as variáveis customizadas (comentários SCSS)

**Exemplo de Documentação:**
```scss
// ==============================================================================
// PALETA DE CORES - VALE DO SOL
// ==============================================================================
// Verde Mata (#588c4c) - Cor principal, natureza, sustentabilidade
// Terracota (#D2691E) - Cor secundária, artesanato, tradição
// Dourado (#DAA520) - Destaque, promoções, calls-to-action

$verde-mata: #588c4c;
$terracota: #D2691E;
$dourado: #DAA520;

// Bootstrap Override
$primary: $verde-mata;
$secondary: $terracota;
$warning: $dourado;
```

---

### 📐 FASE 5: Normalizar Uso de Bootstrap Nativo
**Objetivo:** Garantir que todas as views usam componentes Bootstrap 5.3 oficiais

#### 5.1 Auditar Componentes Usados nas Views

**Componentes Bootstrap a verificar:**
- [x] **Offcanvas** - Cart Drawer (`cart-drawer.blade.php`)
- [ ] **Modal** - Dialogs/popups
- [ ] **Dropdown** - Menus (user, categorias)
- [ ] **Collapse** - Accordions
- [ ] **Navbar** - Header principal
- [ ] **Card** - Product cards
- [ ] **Form Controls** - Inputs, selects, checkboxes
- [ ] **Buttons** - Botões (primary, outline, etc)
- [ ] **Badge** - Tags, contadores
- [ ] **Pagination** - Listagens

**Tarefas:**
- [ ] **5.1.1** Revisar `resources/views/components/cart-drawer.blade.php`
  - Verificar se usa Alpine.js ou Bootstrap Offcanvas nativo
  - Se Alpine: avaliar migração para Bootstrap + integração Alpine
- [ ] **5.1.2** Revisar `resources/views/layouts/partials/header.blade.php`
  - Verificar dropdowns (usar Bootstrap Dropdown)
  - Verificar mobile menu (usar Bootstrap Collapse/Offcanvas)
- [ ] **5.1.3** Revisar `resources/views/components/product-card.blade.php`
  - Verificar se usa classes Bootstrap nativas
  - Verificar badges, botões, imagens
- [ ] **5.1.4** Criar checklist de componentes Bootstrap vs customizações

#### 5.2 Eliminar Redundâncias (Alpine.js vs Bootstrap)

**Princípios:**
- ✅ **Bootstrap nativo PRIMEIRO** para UI (offcanvas, modals, dropdowns)
- ✅ **Alpine.js APENAS** para lógica de estado (cart store, API calls)
- ❌ **NUNCA duplicar** funcionalidade (ex: Alpine drawer vs Bootstrap Offcanvas)

**Tarefas:**
- [ ] **5.2.1** Revisar cart drawer:
  ```blade
  <!-- ✅ IDEAL: Bootstrap Offcanvas + Alpine Store -->
  <div class="offcanvas offcanvas-end" id="cartDrawer"
       x-init="$watch('$store.cart.open', open => {
           const drawer = bootstrap.Offcanvas.getOrCreateInstance('#cartDrawer');
           open ? drawer.show() : drawer.hide();
       })">
  ```
- [ ] **5.2.2** Revisar dropdowns do header (usar `data-bs-toggle="dropdown"`)
- [ ] **5.2.3** Revisar modals (se houver) - usar Bootstrap Modal nativo

#### 5.3 Documentar Padrões de Uso

**Tarefas:**
- [ ] **5.3.1** Criar `docs/BOOTSTRAP_PATTERNS.md` com exemplos:
  - ✅ Como usar Offcanvas + Alpine
  - ✅ Como usar Modals
  - ✅ Como usar Dropdowns
  - ✅ Como usar Forms Bootstrap
  - ✅ Como integrar Chart.js (relatórios)
- [ ] **5.3.2** Atualizar `CLAUDE.md` com referência ao guia de padrões

---

## 🧪 Testes e Validação (Cada Fase)

### Testes Automatizados
```bash
# SEMPRE executar após cada fase
php artisan test

# Verificar zero regressões
# Baseline: 275 testes, 273 passing (99.3%)
```

### Testes Visuais (Manual)

**Checklist de Páginas:**
- [ ] **Públicas:**
  - [ ] Homepage (`/`)
  - [ ] Listagem produtos (`/products`)
  - [ ] Produto individual (`/products/{id}`)
  - [ ] Carrinho (`/cart`)
  - [ ] Checkout (`/checkout`)
  - [ ] Vendedor público (`/sellers/{id}`)

- [ ] **Admin:**
  - [ ] Dashboard (`/admin/dashboard`)
  - [ ] Vendedores (`/admin/sellers`)
  - [ ] Categorias (`/admin/categories`) ← FASE 2
  - [ ] Pedidos (`/admin/orders`)
  - [ ] Relatórios (`/admin/reports`)
  - [ ] Configurações (`/admin/settings`)

- [ ] **Seller:**
  - [ ] Dashboard (`/seller/dashboard`)
  - [ ] Produtos (`/seller/products`)
  - [ ] Pedidos (`/seller/orders`)
  - [ ] Perfil (`/seller/profile/edit`)

- [ ] **Customer:**
  - [ ] Meus Pedidos (`/customer/my-orders`)
  - [ ] Perfil (`/profile`)

- [ ] **Auth:**
  - [ ] Login (`/login`)
  - [ ] Registro (`/register`)
  - [ ] Registro Vendedor (`/seller/register`)

### Testes de Responsividade

**Breakpoints a testar:**
- [ ] Mobile (375px - iPhone SE)
- [ ] Tablet (768px - iPad)
- [ ] Desktop (1024px)
- [ ] Desktop Large (1920px)

**Ferramentas:**
```bash
# Servidor de desenvolvimento
npm run dev
php artisan serve

# Browser DevTools: Toggle device toolbar (Ctrl+Shift+M)
```

### Testes de Performance

**Antes e Depois:**
```bash
# Compilar assets
npm run build

# Verificar tamanho dos arquivos
ls -lh public/build/assets/

# Comparar:
# - app-*.css (tamanho deve ser similar ou menor)
# - app-*.js (tamanho deve ser idêntico)
```

**Métricas:**
- **app.css:** ~150-200KB (compilado, antes de minificação)
- **app.js:** ~50-100KB (Alpine.js + Bootstrap JS + custom)
- **Lighthouse Score:** Manter > 90 em Performance

---

## 📊 Cronograma e Estimativas

| Fase | Descrição | Estimativa | Risco |
|------|-----------|------------|-------|
| **1** | Análise e Backup | 30 min | 🟢 Baixo |
| **2** | Converter Views @extends | 1h | 🟡 Médio |
| **3** | Remover Duplicados | 30 min | 🟢 Baixo |
| **4** | Normalizar SCSS | 2h | 🟡 Médio |
| **5** | Normalizar Bootstrap | 1.5h | 🟡 Médio |
| **Total** | - | **5.5h** | - |

**Riscos:**
- 🟡 **Médio:** Conversão de layouts pode quebrar props/slots complexos
- 🟡 **Médio:** Modularização SCSS pode introduzir bugs de import
- 🟢 **Baixo:** Remoção de arquivos duplicados (após confirmação de não uso)

---

## ✅ Critérios de Sucesso

### Obrigatórios (Must Have)
- [x] **ZERO** views usando `<x-layouts.*>` (exceto pequenos components)
- [x] **ZERO** arquivos duplicados em `components/layouts/`
- [x] **ZERO** CSS não compilado (`resources/css/app.css` removido)
- [x] **100%** dos estilos centralizados em `resources/sass/`
- [x] **273/275** testes passando (manter baseline)
- [x] **ZERO** erros de compilação Vite
- [x] **Todas** as páginas renderizam corretamente (visual QA)

### Desejáveis (Should Have)
- [ ] SCSS modularizado (header dividido em navbar/buttons/footer)
- [ ] Documentação de padrões Bootstrap (`BOOTSTRAP_PATTERNS.md`)
- [ ] Variáveis SCSS documentadas (comentários explicativos)
- [ ] Performance mantida ou melhorada (tamanho CSS)

### Opcionais (Nice to Have)
- [ ] Dark mode support (variáveis CSS para tema)
- [ ] Storybook para componentes (visualização isolada)
- [ ] Sass-lint configurado (linting de SCSS)

---

## 🔍 Referências

### Documentação do Projeto
- `docs/LAYOUT_ARCHITECTURE.md` - Arquitetura de layouts atual
- `docs/BOOTSTRAP_COMPONENTS.md` - Referência de componentes Bootstrap 5.3
- `CLAUDE.md` - Diretrizes gerais do projeto
- `docs/DIRETRIZES_DESENVOLVIMENTO.md` - Padrões de código

### Documentação Externa
- [Bootstrap 5.3 Docs](https://getbootstrap.com/docs/5.3/)
- [Laravel Blade Templates](https://laravel.com/docs/12.x/blade)
- [Alpine.js](https://alpinejs.dev/)
- [Vite Laravel Plugin](https://laravel.com/docs/12.x/vite)

---

## 📝 Notas de Implementação

### Convenções de Nomenclatura

**Arquivos SCSS:**
- Partials começam com `_` (ex: `_variables.scss`, `_navbar.scss`)
- Arquivos de entrada SEM `_` (ex: `app.scss`)

**Seções Blade:**
- `@section('page-content')` - Conteúdo principal da página
- `@section('header')` - Título do header (admin/seller dashboards)
- `@section('title')` - Meta title (SEO)
- `@stack('head')` - Scripts/styles adicionais no `<head>`
- `@stack('scripts')` - Scripts adicionais antes de `</body>`

**Classes Bootstrap:**
- Usar classes utilitárias quando possível (`.d-flex`, `.gap-3`, `.mb-4`)
- Evitar criar classes customizadas para coisas que Bootstrap já faz
- Customizações apenas via SCSS (sobrescrevendo variáveis Bootstrap)

### Boas Práticas

**Blade:**
```blade
<!-- ✅ BOM: Hierarquia clara -->
@extends('layouts.public')

@section('page-content')
    <div class="container">
        <!-- conteúdo -->
    </div>
@endsection

<!-- ❌ MAU: Componente de layout -->
<x-layouts.public>
    <x-slot name="content">
        <!-- dificulta manutenção -->
    </x-slot>
</x-layouts.public>
```

**SCSS:**
```scss
// ✅ BOM: Usar variáveis Bootstrap
.my-component {
    background-color: $primary;
    padding: $spacer * 2;
    border-radius: $border-radius;
}

// ❌ MAU: Valores hardcoded
.my-component {
    background-color: #588c4c;
    padding: 32px;
    border-radius: 8px;
}
```

---

## 🚀 Próximos Passos (Pós-Normalização)

Após concluir todas as fases, considerar:

1. **Otimização de Performance:**
   - PurgeCSS para remover CSS não usado
   - Image optimization (WebP, lazy loading)
   - Critical CSS inline

2. **Acessibilidade:**
   - Auditoria WCAG 2.2 Level AA
   - Testes com screen readers
   - Contraste de cores (WCAG AA: 4.5:1 para texto)

3. **Manutenibilidade:**
   - Criar guia de estilo visual (Styleguide)
   - Documentar todos os componentes
   - Setup de Storybook (opcional)

---

**Última atualização:** 2025-10-14
**Responsável:** Claude Code
**Status:** 📋 Planejamento Completo - Pronto para Execução
