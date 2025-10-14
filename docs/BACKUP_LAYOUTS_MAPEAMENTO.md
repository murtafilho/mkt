# Backup e Mapeamento de Layouts - Normalização
**Data:** 2025-10-14
**Branch:** normalize-layouts

## Baseline de Compilação

**Assets compilados com sucesso:**
```
app-BJUEOs6M.css: 371.47 kB (55.44 kB gzipped)
app-C1fSKTTR.js: 421.80 kB (141.49 kB gzipped)
```

**Warnings SCSS (não críticos):**
- @import deprecated (migrar para @use no futuro - Dart Sass 3.0)
- darken() deprecated (migrar para color.scale/adjust no futuro)
- Total: 341 warnings repetitivos

## Layouts Duplicados (a remover na FASE 3)

### 1. `resources/views/components/layouts/base.blade.php`
**Uso:** Componente Blade com `{{ $slot }}` e `{{ $attributes }}`
**Conflito com:** `resources/views/layouts/base.blade.php` (layout @extends)

### 2. `resources/views/components/layouts/admin.blade.php`
**Uso:** Usa `<x-layouts.base>` + slots (`{{ $header }}`, `{{ $slot }}`)
**Conflito com:** `resources/views/layouts/admin.blade.php` (layout @extends)
**Features específicas:**
- Sidebar desktop + mobile offcanvas (Alpine.js)
- User dropdown com Bootstrap
- Role switching (Admin ↔ Seller)

### 3. `resources/views/components/layouts/app.blade.php`
**Uso:** Similar ao admin (provavelmente)
**Conflito com:** `resources/views/layouts/app.blade.php` (layout @extends)

### 4. `resources/views/components/layouts/seller.blade.php`
**Uso:** Similar ao admin (provavelmente)
**Conflito com:** `resources/views/layouts/seller.blade.php` (layout @extends)

## Views Usando `<x-layouts.*>` (a converter na FASE 2)

### 1. `admin/reports/sellers.blade.php`
**Uso atual:**
```blade
<x-layouts.admin>
    <x-slot:header>Relatório de Vendedores</x-slot>
    <x-slot:title>Relatório de Vendedores - Admin</x-slot>
    <!-- conteúdo -->
</x-layouts.admin>
```

**Conversão para:**
```blade
@extends('layouts.admin')

@section('header', 'Relatório de Vendedores')
@section('title', 'Relatório de Vendedores - Admin')

@section('page-content')
    <!-- conteúdo -->
@endsection
```

**Observações:**
- Usa classes Tailwind (grid-cols-1, space-y-6, text-sm, etc) - PRECISA MIGRAR PARA BOOTSTRAP
- Usa componentes Blade: `<x-filter-chips>`, `<x-sortable-th>`
- Paginação: `{{ $sellers->links() }}` (Bootstrap 5 por padrão)

### 2. `admin/categories/create.blade.php`
**Rota:** `/admin/categories/create`
**Layout esperado:** `layouts.admin`
**Conversão:** Similar ao item 1

### 3. `admin/categories/edit.blade.php`
**Rota:** `/admin/categories/{id}/edit`
**Layout esperado:** `layouts.admin`
**Conversão:** Similar ao item 1

## Estrutura de Layouts Corretos (Mantidos)

### Master Layout
```
resources/views/layouts/base.blade.php
├── HTML structure
├── <head>: meta, title, fonts (Google Fonts CDN - Inter)
├── Vite: @vite(['resources/sass/app.scss', 'resources/js/app.js'])
├── @stack('head'), @stack('styles')
└── @yield('content')
```

### Child Layouts (extends base)
```
1. layouts/public.blade.php
   ├── @extends('layouts.base')
   ├── @include('layouts.partials.header')
   ├── <main>@yield('page-content')</main>
   ├── <x-cart-drawer />
   └── <footer>

2. layouts/admin.blade.php
   ├── @extends('layouts.base')
   ├── Sidebar (desktop) + Offcanvas (mobile)
   ├── Top bar com user dropdown
   └── <main>@yield('page-content')</main>

3. layouts/seller.blade.php
   ├── @extends('layouts.base')
   ├── Similar ao admin (tema diferente)

4. layouts/app.blade.php
   ├── @extends('layouts.base')
   ├── Laravel Breeze navigation

5. layouts/guest.blade.php
   ├── @extends('layouts.base')
   ├── Login/Register layout
```

## Mapeamento de Seções Blade

| Layout | Seções Disponíveis | Descrição |
|--------|-------------------|-----------|
| **base** | `@yield('content')` | Conteúdo principal (child layouts) |
| | `@stack('head')` | Meta tags, links extras |
| | `@stack('styles')` | CSS inline ou links |
| | `@stack('scripts')` | JS inline ou scripts |
| | `@yield('title')` | Meta title SEO |
| **public** | `@yield('page-content')` | Conteúdo da página |
| **admin** | `@yield('page-content')` | Conteúdo da página |
| | `@yield('header')` | Título do header top bar |
| **seller** | `@yield('page-content')` | Conteúdo da página |
| | `@yield('header')` | Título do header top bar |
| **app** | `@yield('header')` | Header do Breeze |
| | `@yield('content')` | Conteúdo (direto, sem wrapper) |
| **guest** | `@yield('content')` | Conteúdo do formulário |

## Classes CSS Detectadas (Tailwind vs Bootstrap)

### ❌ Tailwind encontrado em `admin/reports/sellers.blade.php`
```
- space-y-6, space-y-4, space-x-2
- grid grid-cols-1 md:grid-cols-2 md:grid-cols-4
- gap-4, gap-6, gap-2
- text-sm, text-xs, text-3xl
- font-medium, font-bold, font-semibold
- text-primary-600, text-success-600, text-warning-600, text-danger-600
- bg-white, bg-neutral-50, bg-green-100, bg-yellow-100, bg-red-100
- shadow-sm, rounded-lg, rounded-full
- border-b, divide-y
- px-6, py-4, px-2, py-3
- hover:bg-neutral-50, hover:text-primary-600
- min-w-full
- w-full
```

### ✅ Conversão para Bootstrap 5.3
```
space-y-6         → (remover, usar .mb-4 em cada elemento)
grid-cols-1 md:grid-cols-4 → .row .g-4 > .col-12 .col-md-3
gap-4             → .g-4 (no .row)
text-sm           → .small ou font-size: 0.875rem
font-medium       → .fw-medium
bg-white          → .bg-white
shadow-sm         → .shadow-sm
rounded-lg        → .rounded
px-6 py-4         → .p-4 ou .px-4 .py-3
hover:bg-neutral-50 → Bootstrap classes nativas ou custom SCSS
min-w-full        → .w-100
```

## Próximos Passos

### FASE 2: Converter Views (1h estimada)
1. **admin/reports/sellers.blade.php**
   - [ ] Converter de `<x-layouts.admin>` para `@extends('layouts.admin')`
   - [ ] Migrar classes Tailwind para Bootstrap 5.3
   - [ ] Testar rota `/admin/reports/sellers`

2. **admin/categories/create.blade.php**
   - [ ] Converter de `<x-layouts.admin>` para `@extends('layouts.admin')`
   - [ ] Migrar classes (se houver Tailwind)
   - [ ] Testar rota `/admin/categories/create`

3. **admin/categories/edit.blade.php**
   - [ ] Converter de `<x-layouts.admin>` para `@extends('layouts.admin')`
   - [ ] Migrar classes (se houver Tailwind)
   - [ ] Testar rota `/admin/categories/{id}/edit`

### FASE 3: Remover Duplicados (30 min)
- [ ] Verificar grep zero resultados `<x-layouts.` (exceto components/)
- [ ] Deletar `resources/views/components/layouts/`
- [ ] Compilar assets: `npm run build`
- [ ] Executar subset de testes admin

### FASE 4: Normalizar SCSS (2h)
- [ ] Analisar `resources/css/app.css` vs `resources/sass/app.scss`
- [ ] Remover `resources/css/app.css` se duplicado
- [ ] Modularizar `components/_header.scss` (685 linhas)

## Notas Importantes

**Vite Config:**
```js
input: [
    'resources/sass/app.scss',  // ✅ Compilado
    'resources/js/app.js'       // ✅ Compilado
],
// resources/css/app.css NÃO está no input!
```

**Conclusão:** `resources/css/app.css` provavelmente está obsoleto e pode ser removido na FASE 4.

**Warnings Sass:** Não críticos para MVP, mas considerar migração futura:
- `@import` → `@use` (Dart Sass 3.0)
- `darken()` → `color.scale()` ou `color.adjust()`

---

**Status:** FASE 1 completa ✅
**Próximo:** FASE 2 - Converter 3 views de `<x-layouts.*>` para `@extends`
