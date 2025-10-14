# Auditoria: Produtos Públicos - Sellers Aprovados

**Data:** 2025-01-14
**Objetivo:** Verificar se produtos públicos são filtrados apenas de sellers aprovados

## ✅ Aprovados (Corretos)

### 1. ProductController::index()
**Arquivo:** `app/Http/Controllers/ProductController.php:17`
**Status:** ✅ CORRETO
```php
$query = Product::with(['seller.media', 'category', 'media'])
    ->availableForMarketplace()  // ✅ Filtra por published + seller active + approved_at
    ->inStock();
```

### 2. ProductController::show()
**Arquivo:** `app/Http/Controllers/ProductController.php:84`
**Status:** ✅ CORRETO
```php
if ($product->status !== 'published' || ! $seller->isApproved()) {
    abort(404, 'Produto não encontrado ou indisponível.');
}
```

### 3. SearchController::suggestions() - Produtos
**Arquivo:** `app/Http/Controllers/SearchController.php:30`
**Status:** ✅ CORRETO
```php
$products = Product::with(['category', 'seller', 'media'])
    ->availableForMarketplace()  // ✅ Filtra corretamente
```

### 4. HomeController - Featured Products
**Arquivo:** `app/Http/Controllers/HomeController.php:16`
**Status:** ✅ CORRETO
```php
$featuredProducts = Product::with(['seller.media', 'category', 'media'])
    ->availableForMarketplace()  // ✅ Filtra corretamente
```

### 5. HomeController - Latest Products
**Arquivo:** `app/Http/Controllers/HomeController.php:35`
**Status:** ✅ CORRETO
```php
$latestProducts = Product::with(['seller.media', 'category', 'media'])
    ->availableForMarketplace()  // ✅ Filtra corretamente
```

## ❌ Problemas Encontrados

### 1. SearchController::suggestions() - Sellers
**Arquivo:** `app/Http/Controllers/SearchController.php:50`
**Severidade:** 🟡 MÉDIA
**Problema:** Filtra apenas por `status = 'active'`, mas não verifica `approved_at`

**Código Atual:**
```php
$sellers = Seller::with(['media', 'addresses'])
    ->where('status', 'active')  // ❌ Falta verificar approved_at
```

**Correção Necessária:**
```php
$sellers = Seller::with(['media', 'addresses'])
    ->where('status', 'active')
    ->whereNotNull('approved_at')  // ✅ Adicionar
```

### 2. HomeController - Stats (Sellers Count)
**Arquivo:** `app/Http/Controllers/HomeController.php:44`
**Severidade:** 🟡 MÉDIA
**Problema:** Conta sellers apenas por `status = 'active'`

**Código Atual:**
```php
'sellers_count' => \App\Models\Seller::where('status', 'active')->count(),
```

**Correção Necessária:**
```php
'sellers_count' => \App\Models\Seller::where('status', 'active')
    ->whereNotNull('approved_at')
    ->count(),
```

### 3. HomeController - Stats (Products Count)
**Arquivo:** `app/Http/Controllers/HomeController.php:45`
**Severidade:** 🔴 ALTA
**Problema:** Usa `published()` que não verifica se seller está aprovado

**Código Atual:**
```php
'products_count' => Product::published()->count(),
```

**Correção Necessária:**
```php
'products_count' => Product::availableForMarketplace()->count(),
```

**Explicação:** O scope `published()` apenas verifica `status = 'published'`, mas não valida se o seller está ativo e aprovado. Isso significa que produtos de sellers suspensos ou não aprovados estão sendo contados nas estatísticas públicas!

### 4. CartController::add() - CRÍTICO
**Arquivo:** `app/Http/Controllers/CartController.php:94`
**Severidade:** 🔴 CRÍTICA
**Problema:** Busca produto sem validar disponibilidade

**Código Atual:**
```php
$product = \App\Models\Product::findOrFail($request->input('product_id'));
```

**Impacto:**
- ❌ Usuários podem adicionar produtos em **draft** ao carrinho
- ❌ Usuários podem adicionar produtos de sellers **não aprovados**
- ❌ Usuários podem adicionar produtos de sellers **suspensos**
- ❌ Usuários podem adicionar produtos **sem estoque**

**Correção Necessária:**
```php
$product = \App\Models\Product::availableForMarketplace()
    ->inStock()
    ->findOrFail($request->input('product_id'));
```

**Ou adicionar validação:**
```php
$product = \App\Models\Product::findOrFail($request->input('product_id'));

if (!$product->isAvailable()) {
    throw new \Exception('Produto indisponível');
}
```

## 📊 Resumo

| Controller | Método | Status | Severidade |
|------------|--------|--------|-----------|
| ProductController | index() | ✅ OK | - |
| ProductController | show() | ✅ OK | - |
| SearchController | suggestions() produtos | ✅ OK | - |
| SearchController | suggestions() sellers | ❌ FALHA | 🟡 MÉDIA |
| HomeController | featuredProducts | ✅ OK | - |
| HomeController | latestProducts | ✅ OK | - |
| HomeController | stats sellers_count | ❌ FALHA | 🟡 MÉDIA |
| HomeController | stats products_count | ❌ FALHA | 🔴 ALTA |
| CartController | add() | ❌ FALHA | 🔴 CRÍTICA |

**Total de Problemas:** 4
- 🔴 Críticos: 1 (CartController)
- 🔴 Altos: 1 (HomeController stats)
- 🟡 Médios: 2 (SearchController sellers, HomeController sellers_count)

## 🔧 Plano de Correção

1. ✅ Corrigir HomeController stats (products_count)
2. ✅ Corrigir CartController::add() validação
3. ✅ Corrigir SearchController sellers approved_at
4. ✅ Corrigir HomeController sellers_count

## 📝 Scope Disponível

O model `Product` já possui o scope correto:

```php
public function scopeAvailableForMarketplace($query)
{
    return $query->where('products.status', 'published')
        ->whereHas('seller', function ($q) {
            $q->where('status', 'active')
                ->whereNotNull('approved_at');
        });
}
```

**Filtros aplicados:**
- ✅ Produto com status `published`
- ✅ Seller com status `active`
- ✅ Seller com `approved_at` não nulo (aprovado)

## 🎯 Recomendação

**SEMPRE usar** `availableForMarketplace()` ao buscar produtos para exibição pública no marketplace.

**NUNCA usar:**
- ❌ `Product::all()`
- ❌ `Product::find()`
- ❌ `Product::where('status', 'published')` (não valida seller)
- ❌ `Product::published()` (não valida seller)
