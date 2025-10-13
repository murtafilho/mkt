# Guia de Desenvolvimento - Layout Marketplace Vale do Sol
## Bootstrap 5.3 + npm + Vite + Spatie Media Library + Laravel

---

## 📋 Índice

1. [Configuração Técnica Inicial](#1-configuração-técnica-inicial)
2. [Estrutura do Header](#2-estrutura-do-header)
3. [Sistema de Busca](#3-sistema-de-busca)
4. [Hero Section](#4-hero-section)
5. [Categorias e Subcategorias](#5-categorias-e-subcategorias)
6. [Shopping Cart](#6-shopping-cart)
7. [Sistema de Login](#7-sistema-de-login)
8. [Integração Spatie Media Library](#8-integração-spatie-media-library)
9. [Considerações Especiais para Vale do Sol](#9-considerações-especiais-para-vale-do-sol)
10. [Performance e Otimização](#10-performance-e-otimização)

---

## 1. Configuração Técnica Inicial

### 1.1 Instalação Bootstrap 5.3 + Vite + Laravel

```bash
# Instalar dependências do Bootstrap e Popper
npm install --save bootstrap@5.3 @popperjs/core

# Instalar Sass para compilação
npm install --save-dev sass
```

### 1.2 Configuração do vite.config.js

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import * as path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss', 
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            "~bootstrap": path.resolve(__dirname, 'node_modules/bootstrap')
        }
    }
});
```

### 1.3 Estrutura de Arquivos

```
resources/
├── sass/
│   ├── app.scss (arquivo principal)
│   ├── _variables.scss (cores do Vale do Sol)
│   ├── _header.scss
│   ├── _hero.scss
│   ├── _categories.scss
│   └── _cart.scss
├── js/
│   ├── app.js
│   └── components/
│       ├── search.js
│       ├── cart.js
│       └── categories.js
└── views/
    ├── layouts/
    │   └── app.blade.php
    └── components/
```

### 1.4 Configuração do app.scss

```scss
// Paleta de Cores Vale do Sol
$primary: #6B8E23;        // Verde mata
$secondary: #D2691E;      // Terracota
$accent: #DAA520;         // Dourado quente
$light: #FFFFFF;          // Branco fresco
$dark: #2C3E50;

// Importar variáveis customizadas
@import 'variables';

// Importar Bootstrap
@import '~bootstrap/scss/bootstrap.scss';

// Componentes customizados
@import 'header';
@import 'hero';
@import 'categories';
@import 'cart';
```

### 1.5 Configuração do app.js

```javascript
import * as bootstrap from 'bootstrap';

// Importar componentes customizados
import './components/search';
import './components/cart';
import './components/categories';

// Expor Bootstrap globalmente
window.bootstrap = bootstrap;
```

---

## 2. Estrutura do Header

### 2.1 Anatomia do Header

**Elementos Essenciais:**
1. **Logo** (canto superior esquerdo)
2. **Barra de Busca** (centralizada ou expandida)
3. **Ícones de Navegação** (conta, carrinho, favoritos)
4. **Navegação Principal** (categorias)
5. **Informações de Localização** (Vale do Sol/Jardim Canadá)

### 2.2 Estrutura HTML Recomendada

```html
<!-- Header Sticky com Bootstrap 5.3 -->
<header class="sticky-top bg-white shadow-sm">
    <!-- Top Bar (opcional) -->
    <div class="top-bar bg-light py-2 d-none d-md-block">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small class="text-muted">
                        <i class="bi bi-geo-alt"></i> 
                        Atendemos: Vale do Sol, Pasárgada, Jardim Canadá
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <small>
                        <a href="/ajuda" class="text-decoration-none">Ajuda</a>
                        <span class="mx-2">|</span>
                        <a href="/vendedor" class="text-decoration-none">Vender</a>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <div class="main-header py-3">
        <div class="container">
            <div class="row align-items-center">
                <!-- Logo (25%) -->
                <div class="col-6 col-md-3">
                    <a href="/" class="d-block">
                        <img src="/images/logo-valedosol.svg" 
                             alt="Vale do Sol" 
                             class="img-fluid"
                             style="max-height: 50px;">
                    </a>
                </div>

                <!-- Search Bar (50%) - Desktop -->
                <div class="col-md-6 d-none d-md-block">
                    <!-- Ver seção 3 para detalhes -->
                    @include('components.search-bar')
                </div>

                <!-- User Actions (25%) -->
                <div class="col-6 col-md-3">
                    <div class="d-flex justify-content-end align-items-center gap-3">
                        <!-- Login/Account -->
                        <a href="/conta" class="text-decoration-none text-dark">
                            <i class="bi bi-person fs-4"></i>
                            <small class="d-none d-lg-inline ms-1">Conta</small>
                        </a>

                        <!-- Favoritos -->
                        <a href="/favoritos" class="position-relative text-decoration-none text-dark">
                            <i class="bi bi-heart fs-4"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
                                3
                            </span>
                        </a>

                        <!-- Cart -->
                        <a href="/carrinho" class="position-relative text-decoration-none text-dark">
                            <i class="bi bi-cart3 fs-4"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                2
                            </span>
                        </a>
                    </div>
                </div>

                <!-- Mobile Search (100%) -->
                <div class="col-12 d-md-none mt-3">
                    @include('components.search-bar')
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light border-top">
        <div class="container">
            <!-- Mobile Toggle -->
            <button class="navbar-toggler" type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Nav Items -->
            <div class="collapse navbar-collapse" id="mainNav">
                @include('components.main-navigation')
            </div>
        </div>
    </nav>
</header>
```

### 2.3 Best Practices para Header

**✅ FAZER:**
- Usar `position: sticky` para manter header visível
- Implementar contraste claro entre áreas clicáveis e não-clicáveis
- Colocar logo linkável para homepage (top-left)
- Mostrar número de itens no carrinho sempre visível
- Usar ícones universais (lupa, carrinho, pessoa)

**❌ EVITAR:**
- Headers muito altos (max: 150px total)
- Animações excessivas no scroll
- Esconder elementos essenciais em mobile
- Menu hamburguer para categorias principais em desktop

---

## 3. Sistema de Busca

### 3.1 Componente de Busca Principal

```html
<!-- components/search-bar.blade.php -->
<form action="{{ route('busca') }}" method="GET" class="search-form">
    <div class="input-group input-group-lg">
        <!-- Dropdown de Categorias (opcional) -->
        <button class="btn btn-outline-secondary dropdown-toggle d-none d-lg-flex" 
                type="button" 
                data-bs-toggle="dropdown">
            Todas
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Todas as Categorias</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#">Meio Ambiente</a></li>
            <li><a class="dropdown-item" href="#">Casa e Construção</a></li>
            <li><a class="dropdown-item" href="#">Gastronomia</a></li>
            <!-- Mais categorias -->
        </ul>

        <!-- Campo de Busca -->
        <input type="search" 
               class="form-control" 
               name="q"
               placeholder="O que você procura? Ex: jardineiro, marmita caseira..." 
               autocomplete="off"
               id="searchInput"
               aria-label="Campo de busca">

        <!-- Botão de Busca -->
        <button class="btn btn-primary" type="submit">
            <i class="bi bi-search"></i>
            <span class="d-none d-md-inline ms-2">Buscar</span>
        </button>
    </div>

    <!-- Container para Autocomplete -->
    <div id="searchSuggestions" class="search-suggestions position-absolute w-100 d-none">
        <!-- Preenchido via JavaScript -->
    </div>
</form>
```

### 3.2 JavaScript para Autocomplete

```javascript
// resources/js/components/search.js
import debounce from 'lodash/debounce';

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const suggestionsContainer = document.getElementById('searchSuggestions');

    if (!searchInput) return;

    // Função de busca com debounce
    const performSearch = debounce(async function(query) {
        if (query.length < 2) {
            suggestionsContainer.classList.add('d-none');
            return;
        }

        try {
            const response = await fetch(`/api/search/suggestions?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            renderSuggestions(data);
        } catch (error) {
            console.error('Erro na busca:', error);
        }
    }, 300);

    // Event listener
    searchInput.addEventListener('input', (e) => {
        performSearch(e.target.value);
    });

    // Renderizar sugestões
    function renderSuggestions(data) {
        if (!data.suggestions || data.suggestions.length === 0) {
            suggestionsContainer.classList.add('d-none');
            return;
        }

        let html = '<div class="list-group shadow-lg">';
        
        // Produtos
        if (data.products && data.products.length > 0) {
            html += '<div class="list-group-item bg-light"><small class="text-muted">PRODUTOS</small></div>';
            data.products.forEach(product => {
                html += `
                    <a href="/produto/${product.slug}" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <img src="${product.thumbnail}" class="me-3" style="width: 50px; height: 50px; object-fit: cover;">
                            <div>
                                <div>${product.name}</div>
                                <small class="text-muted">${product.category}</small>
                            </div>
                        </div>
                    </a>
                `;
            });
        }

        // Vendedores
        if (data.sellers && data.sellers.length > 0) {
            html += '<div class="list-group-item bg-light"><small class="text-muted">VENDEDORES</small></div>';
            data.sellers.forEach(seller => {
                html += `
                    <a href="/vendedor/${seller.slug}" class="list-group-item list-group-item-action">
                        <i class="bi bi-shop me-2"></i> ${seller.name}
                        <small class="text-muted ms-2">${seller.location}</small>
                    </a>
                `;
            });
        }

        html += '</div>';
        suggestionsContainer.innerHTML = html;
        suggestionsContainer.classList.remove('d-none');
    }

    // Fechar sugestões ao clicar fora
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            suggestionsContainer.classList.add('d-none');
        }
    });
});
```

### 3.3 CSS para Busca

```scss
// resources/sass/_search.scss
.search-form {
    position: relative;

    .input-group-lg {
        .form-control {
            border-radius: 25px 0 0 25px;
            border-right: none;
            
            &:focus {
                box-shadow: none;
                border-color: $primary;
            }
        }

        .btn-primary {
            border-radius: 0 25px 25px 0;
        }
    }

    .search-suggestions {
        top: 100%;
        left: 0;
        z-index: 1050;
        margin-top: 0.5rem;
        max-height: 500px;
        overflow-y: auto;
        background: white;
        border-radius: 0.5rem;

        .list-group-item {
            transition: background-color 0.2s;

            &:hover {
                background-color: rgba($primary, 0.1);
            }

            img {
                border-radius: 0.25rem;
            }
        }
    }
}

// Mobile
@media (max-width: 767.98px) {
    .search-form {
        .input-group-lg .form-control {
            font-size: 1rem;
        }
    }
}
```

### 3.4 Best Practices para Busca

**✅ IMPLEMENTAR:**
- Autocomplete após 2 caracteres digitados
- Debounce de 300ms para evitar requisições excessivas
- Mostrar thumbnails de produtos nas sugestões
- Separar resultados por tipo (produtos, serviços, vendedores)
- Suporte a busca por voz em mobile (futuro)
- Filtros rápidos (chips) para refinar busca

**❌ EVITAR:**
- Autocomplete que demora mais de 500ms
- Muitos resultados (máx. 8-10 sugestões)
- Sugestões sem imagens (baixa taxa de clique)
- Zero resultados sem alternativas

---

## 4. Hero Section

### 4.1 Decisão: Carousel vs Static Hero

**RECOMENDAÇÃO PARA VALE DO SOL: HERO ESTÁTICO**

**Motivos:**
- Usuários geralmente ignoram carrosséis automáticos, com engajamento caindo drasticamente após o primeiro slide (de 40% para 18%)
- Carrosséis prejudicam SEO devido ao peso da página e confusão do usuário
- Para marketplace local, mensagem única e focada é mais eficaz

**QUANDO usar carousel:**
- Múltiplos stakeholders exigindo visibilidade
- Campanhas sazonais frequentes (Natal, Dia das Mães)
- Máximo 3-5 slides com controle manual

### 4.2 Hero Estático Recomendado

```html
<!-- Hero Section Vale do Sol -->
<section class="hero-section bg-light">
    <div class="container">
        <div class="row align-items-center py-5">
            <!-- Conteúdo (50%) -->
            <div class="col-md-6 order-2 order-md-1">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold mb-3">
                        Onde o comércio tem rosto e a economia tem coração
                    </h1>
                    <p class="lead text-muted mb-4">
                        Descubra produtos e serviços dos seus vizinhos — 
                        do Jardim Canadá a Pasárgada, somos todos uma comunidade.
                    </p>
                    
                    <!-- CTAs -->
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="/explorar" class="btn btn-primary btn-lg">
                            <i class="bi bi-compass me-2"></i>
                            Explorar Ofertas
                        </a>
                        <a href="/vendedor/cadastro" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-shop me-2"></i>
                            Quero Vender
                        </a>
                    </div>

                    <!-- Trust Badges -->
                    <div class="trust-badges mt-4">
                        <div class="d-flex gap-4 flex-wrap">
                            <div class="badge-item">
                                <i class="bi bi-shield-check text-success fs-5"></i>
                                <small class="ms-2">Vendedores Verificados</small>
                            </div>
                            <div class="badge-item">
                                <i class="bi bi-geo-alt text-primary fs-5"></i>
                                <small class="ms-2">100% Local</small>
                            </div>
                            <div class="badge-item">
                                <i class="bi bi-people text-info fs-5"></i>
                                <small class="ms-2">Comunidade Ativa</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Imagem (50%) -->
            <div class="col-md-6 order-1 order-md-2 mb-4 mb-md-0">
                <div class="hero-image">
                    <img src="/images/hero-community.jpg" 
                         alt="Comunidade Vale do Sol" 
                         class="img-fluid rounded-3 shadow-lg"
                         loading="eager">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Bar (opcional) -->
<section class="stats-bar bg-white border-top border-bottom py-4">
    <div class="container">
        <div class="row text-center">
            <div class="col-6 col-md-3 mb-3 mb-md-0">
                <div class="stat-item">
                    <h3 class="display-6 text-primary mb-0">150+</h3>
                    <small class="text-muted">Vendedores Ativos</small>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3 mb-md-0">
                <div class="stat-item">
                    <h3 class="display-6 text-primary mb-0">500+</h3>
                    <small class="text-muted">Produtos e Serviços</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <h3 class="display-6 text-primary mb-0">4.8★</h3>
                    <small class="text-muted">Avaliação Média</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <h3 class="display-6 text-primary mb-0">5km</h3>
                    <small class="text-muted">Raio de Entrega</small>
                </div>
            </div>
        </div>
    </div>
</section>
```

### 4.3 Hero Alternativo (com carousel se necessário)

```html
<!-- Hero com Carousel - Máximo 3 slides -->
<section class="hero-carousel">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="false">
        <!-- Indicadores -->
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
        </div>

        <!-- Slides -->
        <div class="carousel-inner">
            <!-- Slide 1 -->
            <div class="carousel-item active">
                <div class="container">
                    <div class="row align-items-center py-5">
                        <div class="col-md-7">
                            <h2 class="display-5 fw-bold">
                                Produtos Orgânicos Frescos
                            </h2>
                            <p class="lead">Direto dos produtores locais para sua casa</p>
                            <a href="/categoria/organicos" class="btn btn-primary btn-lg">Ver Produtos</a>
                        </div>
                        <div class="col-md-5">
                            <img src="/images/hero-organicos.jpg" class="img-fluid" alt="Produtos Orgânicos">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="carousel-item">
                <div class="container">
                    <div class="row align-items-center py-5">
                        <div class="col-md-7">
                            <h2 class="display-5 fw-bold">
                                Reforma e Construção
                            </h2>
                            <p class="lead">Profissionais qualificados da sua região</p>
                            <a href="/categoria/construcao" class="btn btn-primary btn-lg">Encontrar Profissional</a>
                        </div>
                        <div class="col-md-5">
                            <img src="/images/hero-construcao.jpg" class="img-fluid" alt="Construção">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="carousel-item">
                <div class="container">
                    <div class="row align-items-center py-5">
                        <div class="col-md-7">
                            <h2 class="display-5 fw-bold">
                                Gastronomia Caseira
                            </h2>
                            <p class="lead">Do food truck ao jantar sofisticado</p>
                            <a href="/categoria/gastronomia" class="btn btn-primary btn-lg">Ver Cardápios</a>
                        </div>
                        <div class="col-md-5">
                            <img src="/images/hero-gastronomia.jpg" class="img-fluid" alt="Gastronomia">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Controles -->
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
            <span class="visually-hidden">Próximo</span>
        </button>
    </div>
</section>
```

### 4.4 Best Practices para Hero

**✅ FAZER:**
- Headline clara e concisa (máx. 10 palavras)
- CTA proeminente acima da dobra
- Imagem de alta qualidade (WebP, otimizada)
- Loading="eager" para imagem hero
- Texto legível com bom contraste
- Mobile-first (testar em 360px width)

**❌ EVITAR:**
- Carousel com auto-rotate rápido (<5s)
- Mais de 3 slides
- Texto sobre imagem sem overlay
- Imagens pesadas (>200KB)
- Múltiplos CTAs concorrentes

---

## 5. Categorias e Subcategorias

### 5.1 Estrutura de Dados

```php
// app/Models/Category.php
class Category extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'parent_id', 'order'];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('order');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Scope para categorias principais
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id')->orderBy('order');
    }
}
```

### 5.2 Grid de Categorias Principais

```html
<!-- Seção de Categorias na Homepage -->
<section class="categories-section py-5">
    <div class="container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="fw-bold">Explore por Categoria</h2>
                <p class="text-muted">Do premium ao popular, tudo em um lugar</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="/categorias" class="btn btn-outline-primary">
                    Ver Todas <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
        </div>

        <!-- Grid de Categorias -->
        <div class="row g-4">
            @foreach($mainCategories as $category)
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('category.show', $category->slug) }}" 
                   class="category-card text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm hover-lift">
                        <!-- Ícone/Imagem -->
                        <div class="card-body text-center">
                            <div class="category-icon mb-3">
                                {!! $category->icon !!}
                                <!-- ou -->
                                <img src="{{ $category->getFirstMediaUrl('icon') }}" 
                                     alt="{{ $category->name }}"
                                     class="img-fluid"
                                     style="max-height: 80px;">
                            </div>
                            <h5 class="card-title mb-2">{{ $category->name }}</h5>
                            <small class="text-muted">
                                {{ $category->products_count }} itens
                            </small>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
```

### 5.3 Navegação com Dropdown (Mega Menu)

```html
<!-- components/main-navigation.blade.php -->
<ul class="navbar-nav me-auto mb-2 mb-lg-0">
    @foreach($mainCategories->take(6) as $category)
    <li class="nav-item dropdown mega-menu">
        <a class="nav-link dropdown-toggle" 
           href="{{ route('category.show', $category->slug) }}"
           data-bs-toggle="dropdown">
            {!! $category->icon_small !!}
            {{ $category->name }}
        </a>

        <!-- Mega Menu Dropdown -->
        @if($category->children->count() > 0)
        <div class="dropdown-menu mega-dropdown shadow-lg p-4">
            <div class="container-fluid">
                <div class="row">
                    <!-- Subcategorias -->
                    @foreach($category->children->chunk(ceil($category->children->count() / 3)) as $chunk)
                    <div class="col-md-4 mb-3">
                        @foreach($chunk as $subcategory)
                        <div class="mb-3">
                            <h6 class="dropdown-header">
                                <a href="{{ route('category.show', $subcategory->slug) }}"
                                   class="text-dark text-decoration-none">
                                    {{ $subcategory->name }}
                                </a>
                            </h6>
                            <!-- Sub-subcategorias (se existirem) -->
                            @if($subcategory->children->count() > 0)
                            <ul class="list-unstyled ms-3">
                                @foreach($subcategory->children as $subsubcategory)
                                <li>
                                    <a href="{{ route('category.show', $subsubcategory->slug) }}"
                                       class="dropdown-item">
                                        {{ $subsubcategory->name }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endforeach

                    <!-- Produtos em Destaque (opcional) -->
                    <div class="col-md-12 border-top pt-3 mt-3">
                        <div class="row">
                            @foreach($category->featuredProducts->take(4) as $product)
                            <div class="col-md-3">
                                <a href="{{ route('product.show', $product->slug) }}"
                                   class="text-decoration-none">
                                    <img src="{{ $product->getFirstMediaUrl('images', 'thumb') }}"
                                         class="img-fluid rounded mb-2"
                                         alt="{{ $product->name }}">
                                    <small class="text-dark d-block">{{ $product->name }}</small>
                                    <small class="text-primary fw-bold">
                                        R$ {{ number_format($product->price, 2, ',', '.') }}
                                    </small>
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </li>
    @endforeach

    <!-- Link "Ver Todas" -->
    <li class="nav-item">
        <a class="nav-link" href="/categorias">
            <i class="bi bi-grid-3x3"></i> Ver Todas
        </a>
    </li>
</ul>
```

### 5.4 Página de Categoria com Filtros

```html
<!-- views/category/show.blade.php -->
<div class="container py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Início</a></li>
            @if($category->parent)
            <li class="breadcrumb-item">
                <a href="{{ route('category.show', $category->parent->slug) }}">
                    {{ $category->parent->name }}
                </a>
            </li>
            @endif
            <li class="breadcrumb-item active">{{ $category->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Filtros Sidebar (Desktop) -->
        <div class="col-lg-3 d-none d-lg-block">
            @include('components.filters-sidebar')
        </div>

        <!-- Produtos/Serviços -->
        <div class="col-lg-9">
            <!-- Header da Categoria -->
            <div class="category-header mb-4">
                <h1>{{ $category->name }}</h1>
                <p class="text-muted">{{ $products->total() }} resultados</p>

                <!-- Subcategorias (se houver) -->
                @if($category->children->count() > 0)
                <div class="subcategories-chips mb-3">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($category->children as $subcategory)
                        <a href="{{ route('category.show', $subcategory->slug) }}"
                           class="btn btn-sm btn-outline-secondary">
                            {{ $subcategory->name }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Filtros Mobile -->
            <div class="d-lg-none mb-3">
                <button class="btn btn-outline-primary w-100" 
                        data-bs-toggle="offcanvas" 
                        data-bs-target="#mobileFilters">
                    <i class="bi bi-funnel me-2"></i> Filtros
                </button>
            </div>

            <!-- Barra de Ordenação -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="applied-filters">
                    <!-- Chips de filtros aplicados -->
                </div>
                <div class="sort-options">
                    <select class="form-select" id="sortBy">
                        <option value="relevance">Mais Relevantes</option>
                        <option value="price_asc">Menor Preço</option>
                        <option value="price_desc">Maior Preço</option>
                        <option value="newest">Mais Recentes</option>
                        <option value="rating">Melhor Avaliação</option>
                    </select>
                </div>
            </div>

            <!-- Grid de Produtos -->
            <div class="row g-4">
                @foreach($products as $product)
                <div class="col-6 col-md-4">
                    @include('components.product-card', ['product' => $product])
                </div>
                @endforeach
            </div>

            <!-- Paginação -->
            <div class="mt-5">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas para Filtros Mobile -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileFilters">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Filtros</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        @include('components.filters-sidebar')
    </div>
</div>
```

### 5.5 Componente de Filtros

```html
<!-- components/filters-sidebar.blade.php -->
<div class="filters-sidebar">
    <h5 class="mb-3">Filtrar por:</h5>

    <!-- Faixa de Preço -->
    <div class="filter-group mb-4">
        <h6 class="filter-title">
            <button class="btn btn-link text-dark text-decoration-none w-100 text-start p-0"
                    data-bs-toggle="collapse"
                    data-bs-target="#priceFilter">
                Preço
                <i class="bi bi-chevron-down float-end"></i>
            </button>
        </h6>
        <div class="collapse show" id="priceFilter">
            <div class="mt-3">
                <div class="row g-2">
                    <div class="col-6">
                        <input type="number" class="form-control" placeholder="Mín" name="price_min">
                    </div>
                    <div class="col-6">
                        <input type="number" class="form-control" placeholder="Máx" name="price_max">
                    </div>
                </div>
                <button class="btn btn-sm btn-primary w-100 mt-2">Aplicar</button>
            </div>
        </div>
    </div>

    <!-- Localização -->
    <div class="filter-group mb-4">
        <h6 class="filter-title">
            <button class="btn btn-link text-dark text-decoration-none w-100 text-start p-0"
                    data-bs-toggle="collapse"
                    data-bs-target="#locationFilter">
                Localização
                <i class="bi bi-chevron-down float-end"></i>
            </button>
        </h6>
        <div class="collapse show" id="locationFilter">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="vale-do-sol" id="loc1">
                <label class="form-check-label" for="loc1">
                    Vale do Sol
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="pasargada" id="loc2">
                <label class="form-check-label" for="loc2">
                    Pasárgada
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="jardim-canada" id="loc3">
                <label class="form-check-label" for="loc3">
                    Jardim Canadá
                </label>
            </div>
        </div>
    </div>

    <!-- Avaliação -->
    <div class="filter-group mb-4">
        <h6 class="filter-title">Avaliação</h6>
        <div class="rating-filters">
            @for($i = 5; $i >= 1; $i--)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="{{ $i }}" id="rating{{ $i }}">
                <label class="form-check-label" for="rating{{ $i }}">
                    @for($j = 0; $j < $i; $j++)
                        <i class="bi bi-star-fill text-warning"></i>
                    @endfor
                    @for($j = $i; $j < 5; $j++)
                        <i class="bi bi-star text-muted"></i>
                    @endfor
                    <span class="ms-2">e acima</span>
                </label>
            </div>
            @endfor
        </div>
    </div>

    <!-- Botão Limpar -->
    <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
        Limpar Filtros
    </button>
</div>
```

### 5.6 Best Practices para Categorias

**✅ FAZER:**
- Manter subcategorias em chunks gerenciáveis (máx 10 itens por grupo)
- Tornar headers de categorias pai clicáveis, não apenas labels
- Mostrar contagem de produtos em cada categoria
- Usar breadcrumbs para navegação hierárquica
- Destacar categoria atual na navegação
- Adicionar produtos em múltiplas categorias quando relevante (cross-listing)

**❌ EVITAR:**
- Categorias "Outros" ou "Diversos"
- Mais de 3 níveis de profundidade
- Menus dropdown que desaparecem ao tirar o mouse
- Esconder subcategorias importantes em dropdowns fechados
- Filtros que retornam zero resultados

---

## 6. Shopping Cart

### 6.1 Tipos de Cart

Para Vale do Sol, recomenda-se **Mini Cart (Dropdown)** + **Página de Carrinho Completa**

### 6.2 Mini Cart (Offcanvas)

```html
<!-- Botão no Header -->
<button class="btn position-relative" 
        type="button" 
        data-bs-toggle="offcanvas" 
        data-bs-target="#cartOffcanvas">
    <i class="bi bi-cart3 fs-4"></i>
    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary" 
          id="cartCount">
        2
    </span>
</button>

<!-- Offcanvas Cart -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title">
            Seu Carrinho <span class="text-muted">(<span id="cartItemCount">2</span> itens)</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body p-0 d-flex flex-column">
        <!-- Lista de Itens (com scroll) -->
        <div class="cart-items flex-grow-1" style="overflow-y: auto;">
            @foreach($cartItems as $item)
            <div class="cart-item border-bottom p-3">
                <div class="row g-3">
                    <!-- Imagem -->
                    <div class="col-3">
                        <img src="{{ $item->product->thumbnail }}" 
                             class="img-fluid rounded"
                             alt="{{ $item->product->name }}">
                    </div>

                    <!-- Detalhes -->
                    <div class="col-9">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-1">{{ $item->product->name }}</h6>
                            <button class="btn btn-sm btn-link text-danger p-0" 
                                    onclick="removeFromCart({{ $item->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                        <small class="text-muted d-block mb-2">
                            {{ $item->product->seller->name }}
                        </small>

                        <!-- Quantidade e Preço -->
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Controle de Quantidade -->
                            <div class="quantity-control input-group input-group-sm" style="width: 100px;">
                                <button class="btn btn-outline-secondary" 
                                        onclick="updateQuantity({{ $item->id }}, -1)">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" 
                                       class="form-control text-center" 
                                       value="{{ $item->quantity }}"
                                       min="1"
                                       readonly>
                                <button class="btn btn-outline-secondary" 
                                        onclick="updateQuantity({{ $item->id }}, 1)">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>

                            <!-- Preço -->
                            <div class="text-end">
                                <div class="fw-bold">
                                    R$ {{ number_format($item->total, 2, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Footer Fixo -->
        <div class="cart-footer border-top p-3 bg-light">
            <!-- Subtotal -->
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Subtotal:</span>
                <span class="fw-bold fs-5" id="cartSubtotal">
                    R$ {{ number_format($cartSubtotal, 2, ',', '.') }}
                </span>
            </div>

            <!-- Aviso de Frete -->
            <small class="text-muted d-block mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Frete calculado no checkout
            </small>

            <!-- Botões de Ação -->
            <div class="d-grid gap-2">
                <a href="{{ route('checkout') }}" class="btn btn-primary btn-lg">
                    Finalizar Pedido
                </a>
                <button class="btn btn-outline-secondary" 
                        data-bs-dismiss="offcanvas">
                    Continuar Comprando
                </button>
            </div>

            <!-- Trust Badges -->
            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="bi bi-shield-check text-success me-1"></i>
                    Compra segura
                </small>
            </div>
        </div>
    </div>
</div>
```

### 6.3 Página Completa do Carrinho

```html
<!-- views/cart/index.blade.php -->
<div class="container py-5">
    <h1 class="mb-4">Carrinho de Compras</h1>

    @if($cartItems->count() > 0)
    <div class="row">
        <!-- Lista de Produtos -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <!-- Header da Tabela (Desktop) -->
                    <div class="row d-none d-md-flex border-bottom pb-2 mb-3">
                        <div class="col-md-5"><strong>Produto</strong></div>
                        <div class="col-md-2 text-center"><strong>Preço</strong></div>
                        <div class="col-md-2 text-center"><strong>Quantidade</strong></div>
                        <div class="col-md-2 text-center"><strong>Total</strong></div>
                        <div class="col-md-1"></div>
                    </div>

                    <!-- Itens do Carrinho -->
                    @foreach($cartItems as $item)
                    <div class="cart-item row align-items-center py-3 border-bottom">
                        <!-- Produto -->
                        <div class="col-md-5">
                            <div class="d-flex align-items-center">
                                <img src="{{ $item->product->thumbnail }}" 
                                     class="img-thumbnail me-3" 
                                     style="width: 80px; height: 80px; object-fit: cover;"
                                     alt="{{ $item->product->name }}">
                                <div>
                                    <h6 class="mb-1">
                                        <a href="{{ route('product.show', $item->product->slug) }}"
                                           class="text-decoration-none text-dark">
                                            {{ $item->product->name }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="bi bi-shop me-1"></i>
                                        {{ $item->product->seller->name }}
                                    </small>
                                    @if($item->product->location)
                                    <small class="text-muted d-block">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        {{ $item->product->location }}
                                    </small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Preço -->
                        <div class="col-6 col-md-2 text-center">
                            <span class="d-md-none fw-bold">Preço: </span>
                            R$ {{ number_format($item->product->price, 2, ',', '.') }}
                        </div>

                        <!-- Quantidade -->
                        <div class="col-6 col-md-2">
                            <div class="input-group input-group-sm justify-content-center">
                                <button class="btn btn-outline-secondary" 
                                        onclick="updateCartQuantity({{ $item->id }}, {{ $item->quantity - 1 }})">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" 
                                       class="form-control text-center" 
                                       value="{{ $item->quantity }}"
                                       min="1"
                                       max="99"
                                       style="width: 60px;"
                                       onchange="updateCartQuantity({{ $item->id }}, this.value)">
                                <button class="btn btn-outline-secondary" 
                                        onclick="updateCartQuantity({{ $item->id }}, {{ $item->quantity + 1 }})">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="col-6 col-md-2 text-center fw-bold">
                            <span class="d-md-none">Total: </span>
                            R$ {{ number_format($item->total, 2, ',', '.') }}
                        </div>

                        <!-- Remover -->
                        <div class="col-6 col-md-1 text-center">
                            <button class="btn btn-sm btn-link text-danger" 
                                    onclick="removeFromCart({{ $item->id }})"
                                    title="Remover item">
                                <i class="bi bi-trash fs-5"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach

                    <!-- Botão Limpar Carrinho -->
                    <div class="text-end mt-3">
                        <button class="btn btn-link text-danger" onclick="clearCart()">
                            <i class="bi bi-trash me-1"></i> Limpar Carrinho
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cupom de Desconto -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">Tem um cupom de desconto?</h6>
                    <div class="row g-2">
                        <div class="col-8">
                            <input type="text" 
                                   class="form-control" 
                                   placeholder="Digite o código do cupom"
                                   id="couponCode">
                        </div>
                        <div class="col-4">
                            <button class="btn btn-secondary w-100" onclick="applyCoupon()">
                                Aplicar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumo do Pedido -->
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 100px;">
                <div class="card-body">
                    <h5 class="card-title mb-4">Resumo do Pedido</h5>

                    <!-- Detalhamento -->
                    <div class="summary-details">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal ({{ $cartItems->count() }} itens)</span>
                            <span>R$ {{ number_format($subtotal, 2, ',', '.') }}</span>
                        </div>

                        @if($discount > 0)
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Desconto</span>
                            <span>- R$ {{ number_format($discount, 2, ',', '.') }}</span>
                        </div>
                        @endif

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Frete</span>
                            <span>Calculado no checkout</span>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-4">
                            <strong class="fs-5">Total</strong>
                            <strong class="fs-5 text-primary">
                                R$ {{ number_format($total, 2, ',', '.') }}
                            </strong>
                        </div>
                    </div>

                    <!-- Botão Finalizar -->
                    <div class="d-grid gap-2">
                        <a href="{{ route('checkout') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle me-2"></i>
                            Finalizar Pedido
                        </a>
                        <a href="/" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>
                            Continuar Comprando
                        </a>
                    </div>

                    <!-- Trust Badges -->
                    <div class="trust-section mt-4 pt-3 border-top">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-shield-check text-success fs-4 me-2"></i>
                            <small>Compra 100% segura</small>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-truck text-primary fs-4 me-2"></i>
                            <small>Entrega local rápida</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-arrow-repeat text-info fs-4 me-2"></i>
                            <small>Fácil devolução</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @else
    <!-- Carrinho Vazio -->
    <div class="text-center py-5">
        <i class="bi bi-cart-x display-1 text-muted"></i>
        <h3 class="mt-3">Seu carrinho está vazio</h3>
        <p class="text-muted">Que tal explorar nossos produtos?</p>
        <a href="/" class="btn btn-primary btn-lg mt-3">
            Começar a Comprar
        </a>
    </div>
    @endif
</div>
```

### 6.4 JavaScript do Carrinho

```javascript
// resources/js/components/cart.js

class ShoppingCart {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.updateCartUI();
    }

    bindEvents() {
        // Adicionar ao carrinho
        document.querySelectorAll('[data-add-to-cart]').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = button.dataset.productId;
                this.addToCart(productId);
            });
        });
    }

    async addToCart(productId, quantity = 1) {
        try {
            const response = await fetch('/api/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ product_id: productId, quantity })
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification('Produto adicionado ao carrinho!', 'success');
                this.updateCartUI(data.cart);
                
                // Abrir mini cart
                const cartOffcanvas = new bootstrap.Offcanvas(document.getElementById('cartOffcanvas'));
                cartOffcanvas.show();
            }
        } catch (error) {
            this.showNotification('Erro ao adicionar produto', 'error');
            console.error(error);
        }
    }

    async updateQuantity(itemId, quantity) {
        if (quantity < 1) {
            this.removeItem(itemId);
            return;
        }

        try {
            const response = await fetch(`/api/cart/update/${itemId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ quantity })
            });

            const data = await response.json();

            if (data.success) {
                this.updateCartUI(data.cart);
            }
        } catch (error) {
            console.error(error);
        }
    }

    async removeItem(itemId) {
        if (!confirm('Deseja remover este item do carrinho?')) return;

        try {
            const response = await fetch(`/api/cart/remove/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification('Item removido', 'success');
                this.updateCartUI(data.cart);
            }
        } catch (error) {
            console.error(error);
        }
    }

    updateCartUI(cart) {
        // Atualizar contador
        const cartCount = cart ? cart.items_count : 0;
        document.querySelectorAll('[id^="cartCount"]').forEach(el => {
            el.textContent = cartCount;
            el.classList.toggle('d-none', cartCount === 0);
        });

        // Atualizar subtotal
        if (cart) {
            const subtotalElements = document.querySelectorAll('[id^="cartSubtotal"]');
            subtotalElements.forEach(el => {
                el.textContent = `R$ ${cart.subtotal.toFixed(2).replace('.', ',')}`;
            });
        }
    }

    showNotification(message, type = 'info') {
        // Implementar com Toast do Bootstrap
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        const container = document.querySelector('.toast-container') || this.createToastContainer();
        container.insertAdjacentHTML('beforeend', toastHtml);
        
        const toast = new bootstrap.Toast(container.lastElementChild);
        toast.show();
    }

    createToastContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }
}

// Inicializar
document.addEventListener('DOMContentLoaded', () => {
    window.cart = new ShoppingCart();
});

// Funções globais para callbacks inline
function updateCartQuantity(itemId, quantity) {
    window.cart.updateQuantity(itemId, quantity);
}

function removeFromCart(itemId) {
    window.cart.removeItem(itemId);
}

function clearCart() {
    if (confirm('Deseja limpar todo o carrinho?')) {
        // Implementar
    }
}
```

### 6.5 Best Practices para Cart

**✅ FAZER:**
- Mostrar barra de progresso do checkout
- CTA "Finalizar" deve ser visualmente dominante
- Mostrar atributos do produto (tamanho, cor) no carrinho
- Botão "Continuar Comprando" menos proeminente
- Permitir editar quantidade facilmente
- Mostrar preço total em tempo real
- Incluir thumbails dos produtos
- Mobile: botões grandes (min 44px)

**❌ EVITAR:**
- Custos escondidos (revelar frete antecipadamente)
- Anúncios excessivos na página do carrinho
- Checkout obrigatório com conta (oferecer guest)
- Processo de checkout com mais de 3 etapas
- Remover item sem confirmação

---

## 7. Sistema de Login

### 7.1 Modal de Login/Cadastro

```html
<!-- Modal de Login -->
<div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Tabs -->
            <div class="modal-header border-0">
                <ul class="nav nav-tabs w-100 border-0" role="tablist">
                    <li class="nav-item w-50">
                        <button class="nav-link active w-100" 
                                data-bs-toggle="tab" 
                                data-bs-target="#loginTab">
                            Entrar
                        </button>
                    </li>
                    <li class="nav-item w-50">
                        <button class="nav-link w-100" 
                                data-bs-toggle="tab" 
                                data-bs-target="#registerTab">
                            Cadastrar
                        </button>
                    </li>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="tab-content">
                    <!-- Tab Login -->
                    <div class="tab-pane fade show active" id="loginTab">
                        <form action="{{ route('login') }}" method="POST">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="loginEmail" class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control form-control-lg" 
                                       id="loginEmail"
                                       name="email"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="loginPassword" class="form-label">Senha</label>
                                <input type="password" 
                                       class="form-control form-control-lg" 
                                       id="loginPassword"
                                       name="password"
                                       required>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="rememberMe"
                                           name="remember">
                                    <label class="form-check-label" for="rememberMe">
                                        Lembrar-me
                                    </label>
                                </div>
                                <a href="{{ route('password.request') }}" 
                                   class="text-decoration-none small">
                                    Esqueceu a senha?
                                </a>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                Entrar
                            </button>

                            <!-- Login Social -->
                            <div class="text-center mb-3">
                                <small class="text-muted">ou entre com</small>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-dark">
                                    <i class="bi bi-google me-2"></i> Google
                                </button>
                                <button type="button" class="btn btn-outline-primary">
                                    <i class="bi bi-facebook me-2"></i> Facebook
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab Cadastro -->
                    <div class="tab-pane fade" id="registerTab">
                        <form action="{{ route('register') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="registerName" class="form-label">Nome Completo</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="registerName"
                                       name="name"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="registerEmail" class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="registerEmail"
                                       name="email"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="registerPhone" class="form-label">WhatsApp</label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="registerPhone"
                                       name="phone"
                                       placeholder="(31) 99999-9999">
                            </div>

                            <div class="mb-3">
                                <label for="registerLocation" class="form-label">Localização</label>
                                <select class="form-select" id="registerLocation" name="location">
                                    <option value="">Selecione...</option>
                                    <option value="vale-do-sol">Vale do Sol</option>
                                    <option value="pasargada">Pasárgada</option>
                                    <option value="morro-chapeu">Morro do Chapéu</option>
                                    <option value="jardim-canada">Jardim Canadá</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="registerPassword" class="form-label">Senha</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="registerPassword"
                                       name="password"
                                       required>
                                <div class="form-text">Mínimo 8 caracteres</div>
                            </div>

                            <div class="mb-3">
                                <label for="registerPasswordConfirm" class="form-label">
                                    Confirmar Senha
                                </label>
                                <input type="password" 
                                       class="form-control" 
                                       id="registerPasswordConfirm"
                                       name="password_confirmation"
                                       required>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="acceptTerms"
                                       required>
                                <label class="form-check-label small" for="acceptTerms">
                                    Aceito os 
                                    <a href="/termos" target="_blank">Termos de Uso</a> 
                                    e 
                                    <a href="/privacidade" target="_blank">Política de Privacidade</a>
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                Criar Conta
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

### 7.2 Página de Conta do Usuário

```html
<!-- views/account/dashboard.blade.php -->
<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Foto do Usuário -->
                    <div class="text-center mb-3">
                        <img src="{{ auth()->user()->avatar ?? '/images/default-avatar.png' }}" 
                             class="rounded-circle mb-2"
                             style="width: 80px; height: 80px; object-fit: cover;"
                             alt="{{ auth()->user()->name }}">
                        <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                        <small class="text-muted">{{ auth()->user()->location }}</small>
                    </div>

                    <hr>

                    <!-- Menu -->
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="/conta">
                            <i class="bi bi-grid me-2"></i> Dashboard
                        </a>
                        <a class="nav-link" href="/conta/pedidos">
                            <i class="bi bi-bag me-2"></i> Meus Pedidos
                        </a>
                        <a class="nav-link" href="/conta/favoritos">
                            <i class="bi bi-heart me-2"></i> Favoritos
                        </a>
                        <a class="nav-link" href="/conta/enderecos">
                            <i class="bi bi-geo-alt me-2"></i> Endereços
                        </a>
                        <a class="nav-link" href="/conta/perfil">
                            <i class="bi bi-person me-2"></i> Meu Perfil
                        </a>
                        
                        <hr>

                        <a class="nav-link text-primary" href="/vendedor/dashboard">
                            <i class="bi bi-shop me-2"></i> Área do Vendedor
                        </a>

                        <hr>

                        <a class="nav-link text-danger" href="/logout">
                            <i class="bi bi-box-arrow-right me-2"></i> Sair
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="col-lg-9">
            <!-- Cards de Overview -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block">Pedidos</small>
                                    <h3 class="mb-0">{{ $ordersCount }}</h3>
                                </div>
                                <i class="bi bi-bag fs-1 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block">Favoritos</small>
                                    <h3 class="mb-0">{{ $favoritesCount }}</h3>
                                </div>
                                <i class="bi bi-heart fs-1 text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block">Avaliações</small>
                                    <h3 class="mb-0">{{ $reviewsCount }}</h3>
                                </div>
                                <i class="bi bi-star fs-1 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pedidos Recentes -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Pedidos Recentes</h5>
                </div>
                <div class="card-body">
                    @foreach($recentOrders as $order)
                    <div class="order-item border-bottom pb-3 mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $order->firstProduct->thumbnail }}" 
                                         class="img-thumbnail me-3"
                                         style="width: 60px; height: 60px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-1">Pedido #{{ $order->id }}</h6>
                                        <small class="text-muted">
                                            {{ $order->created_at->format('d/m/Y') }} • 
                                            {{ $order->items_count }} itens
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <span class="badge bg-{{ $order->status_color }} mb-2">
                                    {{ $order->status_label }}
                                </span>
                                <div class="fw-bold">
                                    R$ {{ number_format($order->total, 2, ',', '.') }}
                                </div>
                                <a href="/conta/pedidos/{{ $order->id }}" 
                                   class="btn btn-sm btn-outline-primary mt-2">
                                    Ver Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <a href="/conta/pedidos" class="btn btn-outline-secondary w-100 mt-2">
                        Ver Todos os Pedidos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
```

### 7.3 Best Practices para Login

**✅ FAZER:**
- Login social (Google, Facebook)
- "Esqueci minha senha" visível
- Opção "Lembrar-me"
- Guest checkout (compra sem conta)
- Validação em tempo real
- Progress indicator no cadastro

**❌ EVITAR:**
- Cadastro obrigatório para comprar
- Campos desnecessários
- CAPTCHA excessivo
- Senha fraca aceita
- Processo longo (máx 1 página)

---

## 8. Integração Spatie Media Library

### 8.1 Instalação e Configuração

```bash
# Instalar pacote
composer require "spatie/laravel-medialibrary:^11.0"

# Publicar configuração e migrations
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-config"

# Rodar migrations
php artisan migrate
```

### 8.2 Configuração de Discos

```php
// config/filesystems.php
'disks' => [
    // ... outros disks

    'media' => [
        'driver' => 'local',
        'root' => public_path('media'),
        'url' => env('APP_URL').'/media',
        'visibility' => 'public',
        'throw' => false,
    ],
],
```

```php
// config/media-library.php
return [
    'disk_name' => 'media',
    
    'max_file_size' => 1024 * 1024 * 10, // 10MB

    // Conversões de imagem
    'image_driver' => env('IMAGE_DRIVER', 'gd'),

    // Performance
    'queue_name' => 'media',
];
```

### 8.3 Model com Media Library

```php
// app/Models/Product.php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * Registrar Media Collections
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->maxFilesize(5 * 1024 * 1024) // 5MB
            ->maxNumberOfFiles(10);

        $this
            ->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf'])
            ->singleFile();
    }

    /**
     * Conversões de Imagem
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->nonQueued(); // Gerar imediatamente

        $this
            ->addMediaConversion('medium')
            ->width(800)
            ->height(800)
            ->optimize()
            ->performOnCollections('images');

        $this
            ->addMediaConversion('large')
            ->width(1600)
            ->height(1600)
            ->optimize()
            ->performOnCollections('images');
    }

    /**
     * Accessors para fácil acesso
     */
    public function getThumbnailAttribute()
    {
        return $this->getFirstMediaUrl('images', 'thumb') 
            ?: '/images/placeholder-product.jpg';
    }

    public function getImagesAttribute()
    {
        return $this->getMedia('images')->map(function($media) {
            return [
                'id' => $media->id,
                'thumb' => $media->getUrl('thumb'),
                'medium' => $media->getUrl('medium'),
                'large' => $media->getUrl('large'),
                'original' => $media->getUrl(),
            ];
        });
    }
}
```

### 8.4 Controller para Upload

```php
// app/Http/Controllers/ProductImageController.php
use App\Models\Product;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;

class ProductImageController extends Controller
{
    /**
     * Upload de imagem
     */
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'images' => 'required',
            'images.*' => 'image|mimes:jpeg,png,webp|max:5120' // 5MB
        ]);

        $uploadedImages = [];

        foreach ($request->file('images') as $image) {
            try {
                $media = $product
                    ->addMedia($image)
                    ->usingName(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME))
                    ->withCustomProperties(['uploaded_by' => auth()->id()])
                    ->toMediaCollection('images');

                $uploadedImages[] = [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'thumb' => $media->getUrl('thumb'),
                ];
            } catch (FileDoesNotExist $e) {
                return response()->json([
                    'error' => 'Falha no upload da imagem'
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'images' => $uploadedImages
        ]);
    }

    /**
     * Remover imagem
     */
    public function destroy(Product $product, $mediaId)
    {
        $media = $product->getMedia('images')->where('id', $mediaId)->first();

        if (!$media) {
            return response()->json(['error' => 'Imagem não encontrada'], 404);
        }

        $media->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Reordenar imagens
     */
    public function reorder(Request $request, Product $product)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:media,id'
        ]);

        $media = $product->getMedia('images');

        foreach ($request->order as $index => $mediaId) {
            $media->where('id', $mediaId)->first()->update([
                'order_column' => $index
            ]);
        }

        return response()->json(['success' => true]);
    }
}
```

### 8.5 Blade Component para Upload

```html
<!-- components/image-uploader.blade.php -->
<div class="image-uploader">
    <!-- Preview Area -->
    <div class="images-preview mb-3">
        <div class="row g-3" id="imagesPreview">
            @foreach($product->getMedia('images') as $media)
            <div class="col-6 col-md-3 image-preview-item" data-media-id="{{ $media->id }}">
                <div class="card position-relative">
                    <img src="{{ $media->getUrl('thumb') }}" 
                         class="card-img-top" 
                         alt="Product Image">
                    <div class="card-body p-2">
                        <button type="button" 
                                class="btn btn-sm btn-danger w-100"
                                onclick="deleteImage({{ $media->id }})">
                            <i class="bi bi-trash"></i> Remover
                        </button>
                    </div>
                    <!-- Drag Handle -->
                    <div class="position-absolute top-0 end-0 p-2 cursor-move">
                        <i class="bi bi-arrows-move text-white bg-dark rounded p-1"></i>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Upload Area -->
    <div class="upload-area border-2 border-dashed rounded p-4 text-center"
         id="uploadArea">
        <input type="file" 
               id="imageInput" 
               name="images[]" 
               multiple 
               accept="image/jpeg,image/png,image/webp"
               class="d-none">
        <i class="bi bi-cloud-upload fs-1 text-muted d-block mb-2"></i>
        <p class="mb-2">Arraste imagens ou clique para selecionar</p>
        <small class="text-muted">PNG, JPG, WEBP até 5MB cada</small>
        <div class="mt-3">
            <button type="button" 
                    class="btn btn-outline-primary"
                    onclick="document.getElementById('imageInput').click()">
                Selecionar Imagens
            </button>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="progress mt-3 d-none" id="uploadProgress">
        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
    </div>
</div>

<script>
// Upload e Preview de Imagens
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const imageInput = document.getElementById('imageInput');
    const imagesPreview = document.getElementById('imagesPreview');
    const uploadProgress = document.getElementById('uploadProgress');

    // Drag & Drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('border-primary');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('border-primary');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('border-primary');
        const files = e.dataTransfer.files;
        handleFiles(files);
    });

    // File Input Change
    imageInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });

    // Handle Files Upload
    async function handleFiles(files) {
        const formData = new FormData();
        
        for (let file of files) {
            if (file.type.startsWith('image/')) {
                formData.append('images[]', file);
            }
        }

        if (formData.has('images[]')) {
            uploadProgress.classList.remove('d-none');
            
            try {
                const response = await fetch('{{ route("product.images.store", $product) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Adicionar imagens ao preview
                    data.images.forEach(image => {
                        addImagePreview(image);
                    });
                    imageInput.value = ''; // Reset input
                }
            } catch (error) {
                console.error('Upload error:', error);
                alert('Erro no upload das imagens');
            } finally {
                uploadProgress.classList.add('d-none');
            }
        }
    }

    // Add Image to Preview
    function addImagePreview(image) {
        const html = `
            <div class="col-6 col-md-3 image-preview-item" data-media-id="${image.id}">
                <div class="card position-relative">
                    <img src="${image.thumb}" class="card-img-top" alt="Product Image">
                    <div class="card-body p-2">
                        <button type="button" 
                                class="btn btn-sm btn-danger w-100"
                                onclick="deleteImage(${image.id})">
                            <i class="bi bi-trash"></i> Remover
                        </button>
                    </div>
                    <div class="position-absolute top-0 end-0 p-2 cursor-move">
                        <i class="bi bi-arrows-move text-white bg-dark rounded p-1"></i>
                    </div>
                </div>
            </div>
        `;
        imagesPreview.insertAdjacentHTML('beforeend', html);
    }
});

// Delete Image
async function deleteImage(mediaId) {
    if (!confirm('Remover esta imagem?')) return;

    try {
        const response = await fetch(`/produtos/{{ $product->id }}/images/${mediaId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        if (response.ok) {
            document.querySelector(`[data-media-id="${mediaId}"]`).remove();
        }
    } catch (error) {
        console.error('Delete error:', error);
    }
}

// Sortable (usando SortableJS)
// npm install sortablejs
import Sortable from 'sortablejs';

const imagesPreview = document.getElementById('imagesPreview');
Sortable.create(imagesPreview, {
    animation: 150,
    handle: '.cursor-move',
    onEnd: async function(evt) {
        const order = Array.from(imagesPreview.children).map(el => 
            el.dataset.mediaId
        );

        await fetch('{{ route("product.images.reorder", $product) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ order })
        });
    }
});
</script>
```

### 8.6 Best Practices Spatie Media Library

**✅ FAZER:**
- Gerar múltiplas conversões (thumb, medium, large)
- Usar queues para processamento de imagens grandes
- Otimizar imagens automaticamente
- Validar tipos e tamanhos de arquivo
- Usar WebP quando possível
- Lazy load de conversões não essenciais

**❌ EVITAR:**
- Gerar conversões síncronas para uploads grandes
- Armazenar originais sem otimização
- Múltiplos uploads simultâneos sem controle
- Ignorar erros de upload

---

## 9. Considerações Especiais para Vale do Sol

### 9.1 Identidade Visual

```scss
// resources/sass/_variables.scss - Paleta Vale do Sol

// Cores Principais
$verde-mata: #6B8E23;
$terracota: #D2691E;
$dourado: #DAA520;

// Override Bootstrap
$primary: $verde-mata;
$secondary: $terracota;
$info: $dourado;

// Tipografia
$font-family-base: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
$headings-font-family: 'Poppins', $font-family-base;

// Arredondamento
$border-radius: 0.5rem;
$border-radius-lg: 1rem;

// Sombras
$box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
$box-shadow-lg: 0 1rem 3rem rgba(0, 0, 0, 0.175);
```

### 9.2 Componentes Específicos

```html
<!-- Badge de Localização -->
<component>
<span class="badge bg-light text-dark border">
    <i class="bi bi-geo-alt text-primary"></i>
    {{ $location }}
</span>
</component>

<!-- Card de Vendedor -->
<component>
<div class="seller-card">
    <div class="d-flex align-items-center">
        <img src="{{ $seller->avatar }}" class="rounded-circle me-3" width="50" height="50">
        <div>
            <h6 class="mb-0">{{ $seller->name }}</h6>
            <small class="text-muted">
                <i class="bi bi-geo-alt"></i> {{ $seller->location }}
            </small>
            <div class="mt-1">
                <span class="badge bg-success">Verificado</span>
                <span class="text-warning">
                    ★ {{ $seller->rating }}
                </span>
            </div>
        </div>
    </div>
</div>
</component>

<!-- Indicador de Diversidade Social -->
<component>
<div class="diversity-badge">
    <span class="badge rounded-pill" 
          style="background: linear-gradient(90deg, #6B8E23, #DAA520);">
        <i class="bi bi-stars me-1"></i>
        Comércio Inclusivo
    </span>
</div>
</component>
```

### 9.3 Filtros Específicos do Marketplace

```html
<!-- Filtro por Faixa de Preço (Inclusivo) -->
<div class="filter-group">
    <h6>Faixa de Preço</h6>
    <div class="btn-group-vertical w-100" role="group">
        <input type="radio" class="btn-check" name="priceRange" id="price1" value="0-50">
        <label class="btn btn-outline-secondary text-start" for="price1">
            Até R$ 50 <small class="text-muted float-end">Popular</small>
        </label>

        <input type="radio" class="btn-check" name="priceRange" id="price2" value="50-150">
        <label class="btn btn-outline-secondary text-start" for="price2">
            R$ 50 - R$ 150
        </label>

        <input type="radio" class="btn-check" name="priceRange" id="price3" value="150-500">
        <label class="btn btn-outline-secondary text-start" for="price3">
            R$ 150 - R$ 500
        </label>

        <input type="radio" class="btn-check" name="priceRange" id="price4" value="500+">
        <label class="btn btn-outline-secondary text-start" for="price4">
            Acima de R$ 500 <small class="text-muted float-end">Premium</small>
        </label>
    </div>
</div>

<!-- Filtro por Tipo de Vendedor -->
<div class="filter-group">
    <h6>Tipo de Vendedor</h6>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="individual">
        <label class="form-check-label" for="individual">
            Vendedores Individuais
        </label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="small-business">
        <label class="form-check-label" for="small-business">
            Pequenos Negócios Locais
        </label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="verified">
        <label class="form-check-label" for="verified">
            <i class="bi bi-patch-check-fill text-success"></i>
            Verificados
        </label>
    </div>
</div>
```

### 9.4 Seção "Histórias da Comunidade"

```html
<!-- Seção na Homepage -->
<section class="community-stories py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4">Histórias da Nossa Comunidade</h2>
        <p class="text-center text-muted mb-5">
            Conheça quem faz a diferença no Vale do Sol
        </p>

        <div class="row g-4">
            @foreach($featuredStories as $story)
            <div class="col-md-4">
                <div class="card h-100 shadow-sm story-card">
                    <img src="{{ $story->image }}" 
                         class="card-img-top" 
                         alt="{{ $story->seller_name }}"
                         style="height: 250px; object-fit: cover;">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ $story->seller_avatar }}" 
                                 class="rounded-circle me-2"
                                 width="40"
                                 height="40">
                            <div>
                                <h6 class="mb-0">{{ $story->seller_name }}</h6>
                                <small class="text-muted">{{ $story->category }}</small>
                            </div>
                        </div>
                        <p class="card-text">{{ $story->excerpt }}</p>
                        <a href="/historias/{{ $story->slug }}" 
                           class="btn btn-outline-primary btn-sm">
                            Ler História Completa
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
```

---

## 10. Performance e Otimização

### 10.1 Otimização de Imagens

```php
// config/media-library.php
'image_optimizers' => [
    Spatie\ImageOptimizer\Optimizers\Jpegoptim::class => [
        '-m85', // Qualidade máxima 85%
        '--strip-all',
        '--all-progressive',
    ],
    Spatie\ImageOptimizer\Optimizers\Pngquant::class => [
        '--force',
    ],
    Spatie\ImageOptimizer\Optimizers\Optipng::class => [
        '-i0',
        '-o2',
        '-quiet',
    ],
    Spatie\ImageOptimizer\Optimizers\Svgo::class => [
        '--disable=cleanupIDs',
    ],
],
```

### 10.2 Lazy Loading

```html
<!-- Imagens -->
<img src="placeholder.jpg" 
     data-src="real-image.jpg" 
     class="lazy"
     loading="lazy"
     alt="Product">

<!-- Script -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const lazyImages = document.querySelectorAll('img.lazy');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });

    lazyImages.forEach(img => imageObserver.observe(img));
});
</script>
```

### 10.3 Cache de Queries

```php
// app/Services/CategoryService.php
use Illuminate\Support\Facades\Cache;

class CategoryService
{
    public function getMainCategories()
    {
        return Cache::remember('main_categories', 3600, function() {
            return Category::parents()
                ->with(['children' => function($query) {
                    $query->orderBy('order')->limit(8);
                }])
                ->withCount('products')
                ->get();
        });
    }

    public function clearCache()
    {
        Cache::forget('main_categories');
    }
}
```

### 10.4 Asset Bundling com Vite

```javascript
// vite.config.js
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor': ['bootstrap', '@popperjs/core'],
                    'utils': ['lodash', 'axios']
                }
            }
        }
    }
});
```

### 10.5 Database Indexes

```php
// database/migrations/xxxx_add_indexes_to_products_table.php
Schema::table('products', function (Blueprint $table) {
    $table->index('category_id');
    $table->index('seller_id');
    $table->index('status');
    $table->index(['price', 'status']);
    $table->fullText(['name', 'description']);
});
```

### 10.6 Checklist de Performance

**✅ Implementar:**
- Lazy loading de imagens
- WebP com fallback para JPEG
- Minificação de CSS/JS via Vite
- Cache de queries frequentes (categorias, featured products)
- CDN para assets estáticos
- Compressão GZIP no servidor
- Database indexes em foreign keys
- Eager loading de relationships
- Pagination (máx 48 itens por página)

**❌ Evitar:**
- N+1 queries
- Imagens não otimizadas (>200KB)
- Múltiplas requisições de fontes
- JavaScript bloqueante
- CSS inline excessivo

---

## 📚 Recursos Adicionais

### Documentação Oficial
- [Bootstrap 5.3](https://getbootstrap.com/docs/5.3/)
- [Laravel Vite](https://laravel.com/docs/vite)
- [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary)

### Ferramentas Recomendadas
- **Figma**: Design de protótipos
- **TinyPNG**: Otimização de imagens
- **PageSpeed Insights**: Análise de performance
- **BrowserStack**: Testes cross-browser

### Bibliotecas Úteis
```json
{
  "dependencies": {
    "bootstrap": "^5.3.0",
    "@popperjs/core": "^2.11.8",
    "bootstrap-icons": "^1.11.0"
  },
  "devDependencies": {
    "sass": "^1.69.0",
    "vite": "^5.0.0",
    "laravel-vite-plugin": "^1.0.0"
  }
}
```

---

## 🎯 Checklist Final de Implementação

### Fase 1: Setup (Semana 1)
- [ ] Instalar e configurar Bootstrap 5.3 + Vite
- [ ] Configurar Spatie Media Library
- [ ] Criar paleta de cores Vale do Sol
- [ ] Estruturar arquivos Sass
- [ ] Configurar database e models

### Fase 2: Core Layout (Semana 2-3)
- [ ] Implementar Header sticky
- [ ] Criar sistema de busca com autocomplete
- [ ] Desenvolver hero section
- [ ] Construir navegação de categorias
- [ ] Implementar filtros sidebar

### Fase 3: Funcionalidades (Semana 4-5)
- [ ] Desenvolver shopping cart (mini + página completa)
- [ ] Criar sistema de login/cadastro
- [ ] Implementar área do usuário
- [ ] Configurar upload de imagens
- [ ] Desenvolver product cards

### Fase 4: Otimização (Semana 6)
- [ ] Implementar lazy loading
- [ ] Otimizar imagens
- [ ] Adicionar cache de queries
- [ ] Testar performance (PageSpeed)
- [ ] Testar responsividade (mobile-first)

### Fase 5: Específico Vale do Sol (Semana 7)
- [ ] Implementar filtros de localização
- [ ] Criar seção de histórias da comunidade
- [ ] Adicionar badges de diversidade
- [ ] Configurar mapa interativo (opcional)
- [ ] Integrar WhatsApp para contato direto

---

## 📱 Responsividade Mobile

### Breakpoints Bootstrap 5.3
```scss
// Extra small devices (portrait phones)
// < 576px - Default

// Small devices (landscape phones)
@media (min-width: 576px) { ... }

// Medium devices (tablets)
@media (min-width: 768px) { ... }

// Large devices (desktops)
@media (min-width: 992px) { ... }

// Extra large devices (large desktops)
@media (min-width: 1200px) { ... }

// Extra extra large devices (larger desktops)
@media (min-width: 1400px) { ... }
```

### Prioridades Mobile-First
1. **Header**: Busca expansível, menu hamburguer
2. **Categorias**: Carrossel horizontal
3. **Filtros**: Offcanvas bottom drawer
4. **Cart**: Offcanvas lateral
5. **Checkout**: Stepper simplificado

---

## 🔒 Segurança

### Headers de Segurança
```php
// app/Http/Middleware/SecurityHeaders.php
public function handle($request, Closure $next)
{
    $response = $next($request);
    
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    
    return $response;
}
```

### Validação de Upload
```php
// app/Rules/SafeUpload.php
public function passes($attribute, $value)
{
    // Verificar extensão real (não apenas nome)
    $mimeType = $value->getMimeType();
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    
    if (!in_array($mimeType, $allowedMimes)) {
        return false;
    }
    
    // Verificar tamanho
    if ($value->getSize() > 5 * 1024 * 1024) { // 5MB
        return false;
    }
    
    return true;
}
```

---

**Documento criado para orientar o desenvolvimento do marketplace valedosol.org**
**Versão 1.0 - Outubro 2025**
