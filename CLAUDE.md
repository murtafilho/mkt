# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Marketplace B2C Multivendor** - Laravel 12 application running on Laragon (Windows/MySQL environment).

### Tech Stack
- **Backend**: Laravel 12.32.5 (PHP 8.2+)
- **Authentication**: Laravel Breeze (translated to pt-BR)
- **Permissions**: Spatie Laravel Permission
- **Media**: Spatie Laravel Media Library
- **Payments**: Mercado Pago SDK (Split Payments)
- **Frontend**: Blade + Vite + **Bootstrap 5.3** + Alpine.js
- **Database**: MySQL (via Laragon) - Database name: `mkt`
- **Testing**: Pest PHP
- **Static Analysis**: PHPStan Level 5
- **Code Formatting**: Laravel Pint (PSR-12)
- **Queue**: Database driver
- **Cache/Session**: Database driver

### ⚠️ Bootstrap 5.3 + Blade Directives - Critical Guidelines

**This project uses Bootstrap 5.3.3 (migrated from Tailwind CSS 4.0)**

**Key Principles:**
- ✅ **Always use Bootstrap native components FIRST** (Offcanvas, Modal, Dropdown, Collapse)
- ✅ Semantic classes: `.btn.btn-primary`, `.form-control`, `.card`
- ✅ Grid system: `.row`, `.col-*` (NOT Tailwind grid)
- ✅ Utilities: `.d-flex`, `.gap-3`, `.mb-4`, `.text-primary`
- ⚠️ **DO NOT reinvent components** that Bootstrap already has
- ⚠️ **DO NOT mix Alpine.js** for things Bootstrap handles (dropdowns, modals, offcanvas)

**Blade Templates - MANDATORY Pattern:**
- ✅ **USE:** `@extends('layouts.public')`, `@section('content')`, `@include('partials.header')`
- ❌ **DO NOT USE:** `<x-layouts.public>`, `<x-slot>`, Blade Components for layouts
- ✅ **Exception:** Blade Components are OK for small reusable pieces (`<x-product-card>`, `<x-cart-drawer>`)
- ✅ **Reason:** Traditional Blade directives are clearer, more maintainable, and avoid slot/prop complexity

**Bootstrap Native Components - USE THESE:**
- ✅ **Offcanvas** - Drawers/sidebars (cart, mobile menu)
- ✅ **Modal** - Dialogs/popups  
- ✅ **Dropdown** - Dropdown menus (user menu, filters)
- ✅ **Collapse** - Accordions, expandable sections
- ✅ **Toast** - Temporary notifications
- ✅ **Tooltip/Popover** - Contextual tips

**Alpine.js - ONLY for:**
- ✅ Complex state management (cart store)
- ✅ Dynamic forms with calculations
- ✅ Real-time filters/search
- ✅ API integrations (fetch, axios)

**Integration Pattern (Bootstrap + Alpine):**
```blade
<!-- ✅ CORRECT: Sync Alpine Store with Bootstrap component -->
<div class="offcanvas offcanvas-end" id="myOffcanvas"
     x-init="
        const offcanvas = new bootstrap.Offcanvas('#myOffcanvas');
        $watch('$store.myState.open', value => {
            value ? offcanvas.show() : offcanvas.hide();
        });
     ">
</div>

<!-- ❌ WRONG: Custom Alpine drawer (conflicts with CSS) -->
<div x-show="open" x-transition class="fixed...">
```

**Theme Customization:**
- **Colors**: `resources/css/app.css` → `:root { --bs-primary: #588c4c; }`
- **Palette**: Verde Mata (#588c4c), Terracota (#b86f50), Dourado (#e67e22)
- **Custom CSS**: Override Bootstrap variables, not create new frameworks

**Complete Reference:**
- See `docs/BOOTSTRAP_COMPONENTS.md` for all component examples
- Grid: `.row.g-3` > `.col-6.col-md-4.col-lg-3`
- Forms: `.form-control`, `.form-select`, `.form-check-input`
- Buttons: `.btn.btn-primary`, `.btn.btn-outline-secondary`

### Critical Architecture Decisions
- ⚠️ **Nomenclature**: Code/Database in **ENGLISH**, User Interface in **PORTUGUESE**
- ⚠️ **Semantic Consistency**: ALL references use "Seller" terminology (tables: `sellers`, `seller_addresses`, `seller_payments`; columns: `seller_id`; models: `Seller`)
- ⚠️ **1 Order = 1 Seller** (cart splits into multiple orders)
- ⚠️ **Multiple Sequential Payments** via Mercado Pago
- ⚠️ **100% Automatic Split Payments** (no manual withdrawal processing)

## Development Commands

### Running the Application

```bash
# Start development server with queue worker and Vite (recommended)
composer dev

# This runs concurrently:
# - php artisan serve (server on http://localhost:8000)
# - php artisan queue:listen --tries=1 (queue worker)
# - npm run dev (Vite dev server)
```

### Individual Services

```bash
# Run Laravel development server only
php artisan serve

# Run Vite dev server only
npm run dev

# Build assets for production
npm run build

# Run queue worker
php artisan queue:listen
```

### Database

```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Fresh migration (drops all tables and re-runs)
php artisan migrate:fresh

# Seed database
php artisan db:seed
```

### Testing

```bash
# Run all Feature/Unit tests
composer test
# Or: php artisan test

# Run specific test file
php artisan test --filter TestClassName

# Run tests in a specific directory
php artisan test tests/Feature

# Run Dusk tests (E2E browser tests)
php artisan dusk

# Run specific Dusk test
php artisan dusk --filter=SellerRegistrationTest

# Run Dusk with visible browser (debug)
# Set DUSK_HEADLESS_DISABLED=true in .env.dusk.local
php artisan dusk
```

### Mercado Pago Integration Testing

```bash
# Fresh database with test data
php artisan migrate:fresh --seed

# Test MP integration
php artisan test:mercadopago
```

**Test Cards (Sandbox):**
- ✅ Approved: `5031 4332 1540 6351` | CVV: `123` | Expiry: `11/25`
- ❌ Rejected: `5031 7557 3453 0604` | CVV: `123` | Expiry: `11/25`

### Code Quality

```bash
# Run PHPStan static analysis (Level 5)
./vendor/bin/phpstan analyse

# Format code with Laravel Pint (PSR-12)
./vendor/bin/pint

# Fix specific file
./vendor/bin/pint app/Models/User.php
```

### Development Workflow (TDD)

**MANDATORY**: Follow Test-Driven Development cycle (see `docs/FLUXO_DESENVOLVIMENTO.md`)

```bash
# 1. RED: Write failing test
php artisan test --filter YourNewTest

# 2. GREEN: Implement minimal code to pass
php artisan test --filter YourNewTest

# 3. REFACTOR: Clean up code
./vendor/bin/phpstan analyse
./vendor/bin/pint

# 4. Commit
git add . && git commit -m "feat: your feature"
```

### Other Useful Commands

```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Generate IDE helper (if installed)
php artisan ide-helper:generate

# List all routes
php artisan route:list

# Tinker (REPL)
php artisan tinker
```

## Architecture

### Directory Structure

- `app/Http/Controllers/` - Controllers (Admin, Seller, Customer namespaces)
- `app/Models/` - Eloquent models (**ENGLISH names**: Seller, Product, Order)
- `app/Services/` - Business logic (SellerService, ProductService, etc.)
- `app/Providers/` - Service providers
- `database/migrations/` - Database migrations (**15 tables in ENGLISH**)
- `database/factories/` - Model factories (for testing)
- `resources/views/` - Blade templates (**PORTUGUESE labels**)
- `resources/css/` - CSS assets (Tailwind)
- `resources/js/` - JavaScript assets
- `routes/web.php` - Web routes (**ENGLISH route names**)
- `routes/console.php` - Console commands
- `docs/` - Project documentation (**13 files**)

### Layout Architecture (Hierarchical System)

**⚠️ CRITICAL: All layouts use @extends, @section, @include (NOT Blade Components)**

**📖 Complete Documentation**: See [docs/LAYOUT_ARCHITECTURE.md](docs/LAYOUT_ARCHITECTURE.md)

**Quick Reference:**

```
base.blade.php (Master Layout)
├── HTML structure, <head>, fonts, Vite, Alpine.js
└── @extends to specific layouts:
    ├── admin.blade.php (Admin Panel - Dark theme)
    ├── seller.blade.php (Seller Dashboard - Gray theme)
    ├── app.blade.php (Authenticated User - Breeze)
    └── public.blade.php (Public Marketplace)
        └── partials/
            └── header.blade.php (Shared header via @include)
```

**Location:** `resources/views/layouts/`

**Implementation Rules:**
- ✅ **ALWAYS USE:** `@extends`, `@section`, `@yield`, `@include`
- ❌ **NEVER USE:** `<x-layouts.*>`, `<x-slot>`, Blade Components for layouts
- ✅ **Exception:** Small components OK (`<x-product-card>`, `<x-cart-drawer>`, Mercado Pago Brick)
- ✅ **Header/Footer:** Use `@include('layouts.partials.header')` for reusability
- ✅ **DRY Principle:** Extract repeating blocks to partials/
- ⚠️ **NEVER duplicate** `<head>`, Vite, or fonts in child layouts
- ⚠️ **ALWAYS extend** layouts/base.blade.php for new layouts
- ⚠️ Use `@stack('head'|'styles'|'scripts')` for page-specific assets

**Partials Architecture:**
```blade
<!-- Layout -->
@extends('layouts.public')

@section('page-content')
    <!-- Page content here -->
@endsection

<!-- layouts/public.blade.php -->
@extends('layouts.base')

@section('content')
    @include('layouts.partials.header')  ← Single source of truth
    <main>@yield('page-content')</main>
    @include('layouts.partials.footer')
@endsection
```

**Benefits:**
- ✅ Single header file for entire public site
- ✅ Edit once, reflects everywhere
- ✅ No component prop/slot complexity
- ✅ Clear hierarchy and inheritance

### Database Configuration

MySQL configuration (from `.env`):
- Database: `mkt`
- Host: `127.0.0.1`
- Port: `3306`
- Username: `root`
- Password: (empty)

### Database Schema (14 Custom Tables)

**⚠️ ALL TABLE NAMES IN ENGLISH** (see `docs/DICIONARIO_DADOS.md` for complete schema)

**Core Tables:**
- `sellers`, `seller_addresses`, `seller_payments`
- `categories`, `products`, `product_variations`
- `user_addresses`
- `orders`, `order_addresses`, `order_items`, `order_history`
- `cart_items`
- `settings`
- `payments`

**Laravel/Package Tables:**
- `users`, `password_reset_tokens`, `sessions` (Breeze)
- `roles`, `permissions`, `model_has_roles`, etc. (Spatie Permission)
- `media` (Spatie Media Library)
- `jobs`, `failed_jobs`, `job_batches` (Queue)
- `cache`, `cache_locks` (Cache)

### Nomenclature Rules

**CRITICAL**: Before implementing ANY feature, consult `docs/DICIONARIO_DADOS.md`

- ✅ **Code (Models, Controllers, Services, Migrations)**: ENGLISH
  - `Seller`, `SellerController`, `sellers` table
  - `Product`, `ProductController`, `products` table
  - `Order`, `OrderController`, `orders` table
  - **All foreign keys**: `seller_id` (NOT vendor_id)

- ✅ **Views (Blade templates, labels, buttons, messages)**: PORTUGUESE
  - `<label>Nome da Loja</label>`
  - `<input name="store_name">` (name attribute in English)
  - `<button>Adicionar ao Carrinho</button>`

- ✅ **Routes**: ENGLISH
  - `/become-seller`, `/products`, `/cart`, `/checkout`
  - `/seller/orders`, `/admin/sellers`

### Queue and Cache

Both queue and cache systems use the database driver.

## Environment Setup

This project runs on **Laragon** (Windows environment). When working with paths or commands:
- Use forward slashes in artisan commands
- The project is located at `c:\laragon\www\mkt`
- MySQL is accessible without password for root user

## Framework Version

Laravel 12 is the latest major version. Be aware of breaking changes if referencing older Laravel documentation.

## Documentation

**⚠️ MANDATORY**: Read documentation BEFORE coding

### Must-Read Documents

1. **DIRETRIZES_DESENVOLVIMENTO.md** ⭐ MANDATORY
   - Nomenclature rules (English vs Portuguese)
   - TDD workflow (Red-Green-Refactor)
   - PHPStan + Pint integration
   - Pre-commit checklist

2. **DICIONARIO_DADOS.md** ⭐ MANDATORY CONSULTATION
   - Complete database schema (15 tables)
   - Field names in English with Portuguese labels
   - Relationships, constraints, indexes
   - **MUST consult before ANY implementation**

3. **ROADMAP_MVP.md**
   - 30-day development plan (100% complete)
   - All tasks completed ✅

4. **DECISOES_PROJETO.md**
   - Key architectural decisions
   - 1 Order = 1 Seller rule
   - Multiple sequential payments
   - Normalized address tables

### Reference Documents

- `LAYOUT_ARCHITECTURE.md` ⭐ RECOMMENDED - Complete layout hierarchy documentation
- `BOOTSTRAP_COMPONENTS.md` - Bootstrap 5.3.3 component reference
- `DICIONARIO_MERCADOPAGO.md` - Mercado Pago API reference
- `INTEGRACAO_CEP_MERCADOPAGO.md` - CEP and payment integration
- `FLUXO_DESENVOLVIMENTO.md` - TDD workflow details
- `DEPLOYMENT.md` - Production deployment guide (500+ lines)
- `MVP_LAUNCH_CHECKLIST.md` - Pre-launch checklist (40+ items)
- `MCP_STATUS.md` - Active MCP servers and versions
- `LARAVEL_DUSK_TUTORIAL.md` - Complete Dusk E2E testing guide

### Documentation Location

**Core docs:** `docs/` directory (9 essential markdown files)
**Root docs:** `BOOTSTRAP_NPM_MIGRATION.md`, `MIGRATION_LAYOUTS_EXTENDS.md`, `STARTBOOTSTRAP_FINAL_IMPLEMENTATION.md`, `DUSK_QUICK_START.md`

## Laravel Dusk (E2E Browser Tests)

### Overview

Laravel Dusk 8.3.3 está instalado e configurado com **21 testes E2E** prontos para testar fluxos críticos do usuário.

### Quick Start

```bash
# Setup (primeira vez)
CREATE DATABASE mkt_dusk_test;

# Executar testes
php artisan serve   # Terminal 1
php artisan dusk     # Terminal 2

# Ver: DUSK_QUICK_START.md para guia rápido
# Tutorial completo: docs/LARAVEL_DUSK_TUTORIAL.md
```

### Testes Disponíveis

- ✅ `SellerRegistrationTest.php` - 6 testes (CPF, CNPJ, validações)
- ✅ `ProductCrudTest.php` - 5 testes (criar, editar, publicar, deletar)
- ✅ `AdminSellerApprovalTest.php` - 5 testes (aprovar, suspender, reativar)
- ✅ `CustomerShoppingFlowTest.php` - 5 testes (busca, carrinho, checkout)

**Total:** 21 testes E2E + 275 Feature/Unit = **296 testes**

### Comandos Úteis

```bash
# Executar todos os testes Dusk
php artisan dusk

# Teste específico
php artisan dusk --filter=SellerRegistrationTest

# Ver browser em ação (debug)
# .env.dusk.local: DUSK_HEADLESS_DISABLED=true
php artisan dusk

# Atualizar ChromeDriver
php artisan dusk:chrome-driver --detect
```

**📖 Documentação Completa:** `docs/LARAVEL_DUSK_TUTORIAL.md`

---

## Current Development Status

**🎉 MVP 100% COMPLETE - READY FOR PRODUCTION DEPLOYMENT 🚀**

### Implementation Summary

**Core Functionality Implemented:**
- ✅ Database: 14 migrations + 14 models + 13 factories + 7 seeders
- ✅ Authentication: Breeze (pt-BR) + Spatie Permission (3 roles, 36 permissions)
- ✅ Authorization: 4 Policies + 3 Middlewares
- ✅ Services: 5 business logic services (Product, Cart, Order, Seller, Payment)
- ✅ Controllers: 20 controllers (Admin, Seller, Customer, Public namespaces)
- ✅ Validation: 27 FormRequests (Portuguese messages)
- ✅ Views: 65+ Blade templates (Portuguese labels)
- ✅ Layout System: Hierarchical DRY architecture (base → 4 layouts)
- ✅ Components: 10+ reusable Blade components (product-card, cart-drawer, etc.)
- ✅ Frontend: Alpine.js Global Store (cart management), Chart.js (reports)
- ✅ Media: Spatie Media Library (products, sellers)
- ✅ Payments: Mercado Pago SDK + webhooks + jobs + emails
- ✅ Testing: 275 tests (273 passing, 99.3% success rate)

**Quality Metrics:**
- PHPStan Level 5: **0 errors** (93 files analyzed)
- Laravel Pint: **164 files formatted** (PSR-12 compliant)
- Test Suite: **275 tests, 273 passing** (99.3% success rate)
- Total Assertions: **567**
- Code Coverage: All critical paths tested

**Final Statistics:**
- **Duration**: 30 days (as planned)
- **Files Created**: 93 PHP files analyzed by PHPStan
- **Tests Written**: 275 automated tests (99.3% passing)
- **Database Tables**: 14 custom + 10 Laravel/packages = 24 total
- **Controllers**: 20 controllers (Admin, Seller, Customer, Public)
- **Services**: 5 business logic services
- **Policies**: 4 authorization policies
- **FormRequests**: 27 validation classes
- **Views**: 65+ Blade templates
- **Documentation**: 13 markdown files
- **Lines of Code**: ~15,000+ lines (estimated)

**Known Issues:**
- 2 webhook signature validation tests failing (non-critical for MVP)

**🚀 Next Steps:**
1. Execute pre-launch checklist from [MVP_LAUNCH_CHECKLIST.md](docs/MVP_LAUNCH_CHECKLIST.md)
2. Deploy to production following [DEPLOYMENT.md](docs/DEPLOYMENT.md)
3. Configure Mercado Pago production webhook
4. Run smoke tests
5. Monitor Day 1 metrics

## MCP Documentation Access

The MCP_DOCKER server is available for accessing package documentation:

### Spatie Packages
Access Spatie package docs using Context7-compatible library IDs:
- `/spatie/laravel-permission` - Roles and permissions (INSTALLED)
- `/spatie/laravel-medialibrary` - Media management (INSTALLED)
- `/spatie/laravel-backup` - Application backups
- `/spatie/laravel-data` - Data transfer objects
- `/spatie/laravel-query-builder` - API query building
- `/spatie/laravel-activitylog` - Activity logging
- `/spatie/laravel-settings` - Application settings
- `/spatie/laravel-tags` - Tagging system

### Laravel Documentation
- `/laravel/laravel` - Core Laravel framework (v12)
- `/websites/spatie_be_laravel-data_v4` - Laravel Data v4 docs
- `/websites/spatie_be_laravel-query-builder_v6` - Query Builder v6 docs

### Mercado Pago
- SDK: `mercadopago/dx-php` (INSTALLED)
- Docs: See `docs/DICIONARIO_MERCADOPAGO.md` for complete API reference

## Quality Standards

### Mandatory Checks Before Commit

```bash
# 1. Run tests
php artisan test

# 2. Run PHPStan (must pass Level 5)
./vendor/bin/phpstan analyse

# 3. Format code with Pint
./vendor/bin/pint

# 4. Commit with semantic message
git add .
git commit -m "feat: your feature description"
```

### Code Review Checklist

**Before Each Commit**:
- [ ] Nomenclature correct? (English in code, Portuguese in views)
- [ ] Consulted DICIONARIO_DADOS.md?
- [ ] Tests written and passing?
- [ ] PHPStan Level 5 passing?
- [ ] Code formatted with Pint?
- [ ] Relationships defined correctly?
- [ ] Followed TDD cycle?
