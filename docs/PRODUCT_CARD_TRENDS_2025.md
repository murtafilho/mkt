# Product Card Design Trends 2025 - Research Summary

## 📊 Tendências Identificadas

### 1. Layout e Estrutura

**Hierarquia Visual Moderna:**
- ✅ **Imagem dominante** (aspect ratio 4:3 ou 1:1)
- ✅ **Título destacado** (font-weight: 600, 2 linhas truncadas)
- ✅ **Preço em evidência** (fonte maior, cor primária)
- ✅ **Badges estratégicos** (desconto, novo, local)
- ✅ **Informação do vendedor** (pequena, discreta)

**Espaçamento Moderno:**
- Padding interno: **16-20px** (1rem-1.25rem)
- Gap entre elementos: **12px** (0.75rem)
- Margens consistentes: **8px** em badges

### 2. Botões "Adicionar ao Carrinho"

**Tendências 2025:**

**Opção A - Icon Button (minimalista):**
```html
<button class="btn btn-primary btn-icon">
    <i class="bi bi-cart-plus"></i>
</button>
```
- Formato: **Circular ou quadrado arredondado**
- Tamanho: **44x44px** (WCAG 2.2 touch target)
- Ícone: **Cart-plus** ou **Plus**
- Posição: **Canto inferior direito** do card

**Opção B - Full Button (conversão):**
```html
<button class="btn btn-primary w-100">
    <i class="bi bi-cart-plus me-2"></i>
    Adicionar ao Carrinho
</button>
```
- Largura: **100%** do footer
- Altura: **44px**
- Ícone: **Antes do texto**
- Posição: **Footer do card**

**Opção C - Hover Reveal (premium):**
```html
<!-- Escondido por padrão, aparece no hover do card -->
<div class="product-actions">
    <button class="btn btn-primary">
        <i class="bi bi-cart-plus"></i>
    </button>
    <button class="btn btn-outline-primary">
        <i class="bi bi-heart"></i>
    </button>
</div>
```
- Visibilidade: **Opacity 0 → 1** no hover
- Animação: **Slide up** com transição suave
- Múltiplas ações: Carrinho + Favoritos

### 3. Microinterações e Feedback

**Estados do Botão:**
```scss
// Normal
.btn-add-to-cart {
    background: $primary;
    transition: all 0.2s ease;
}

// Hover
.btn-add-to-cart:hover {
    background: darken($primary, 10%);
    transform: scale(1.05);
}

// Active (clique)
.btn-add-to-cart:active {
    transform: scale(0.95);
}

// Success (após adicionar)
.btn-add-to-cart.success {
    background: $success;
    .bi-cart-plus::before {
        content: "\f272"; // bi-check
    }
}
```

**Toast/Notification:**
- Exibir **toast** no canto superior direito
- Duração: **3 segundos**
- Conteúdo: "Produto adicionado ao carrinho"
- Ações: "Ver carrinho" | "Continuar comprando"

### 4. Tipografia e Cores

**Hierarquia de Texto:**
```scss
.product-name {
    font-size: 1rem;      // 16px
    font-weight: 600;     // Semibold
    line-height: 1.4;
    color: $dark;
    -webkit-line-clamp: 2;
}

.product-seller {
    font-size: 0.75rem;   // 12px
    font-weight: 400;
    color: $gray-600;
}

.product-price {
    font-size: 1.5rem;    // 24px
    font-weight: 700;     // Bold
    color: $primary;
}

.product-price-old {
    font-size: 0.875rem;  // 14px
    font-weight: 400;
    color: $gray-500;
    text-decoration: line-through;
}
```

**Contraste e Acessibilidade:**
- Botão primário: **Ratio 4.5:1** mínimo
- Texto sobre imagem: **Sombra ou overlay**
- Touch targets: **44x44px** mínimo

### 5. Badges e Indicadores

**Posicionamento:**
```
┌─────────────────────────┐
│ [Desconto]    [Local] │ ← Top corners
│                         │
│      [IMAGEM]          │
│                         │
│                         │
└─────────────────────────┘
│ Info do produto         │
│ [Carrinho]             │ ← Bottom right
└─────────────────────────┘
```

**Estilos Modernos:**
```scss
.badge-discount {
    background: $danger;
    color: white;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.badge-local {
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(8px);
    color: $dark;
    border: 1px solid rgba(0,0,0,0.1);
}
```

### 6. Efeitos Hover

**Card Hover (2025 Trend):**
```scss
.product-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    
    &:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.12);
        border-color: $primary;
        
        // Image zoom suave
        img {
            transform: scale(1.05);
        }
        
        // Botão aparece
        .btn-add-to-cart {
            opacity: 1;
            transform: translateY(0);
        }
    }
}
```

## 🎯 Recomendação Final

**Para Vale do Sol Marketplace:**

1. **Layout**: Manter aspect ratio 1:1 para uniformidade
2. **Botão**: **Opção A** (Icon Button) - minimalista e moderno
3. **Hover**: Card lift + imagem zoom + border primary
4. **Badges**: Desconto (top-left) + Local (top-right)
5. **Feedback**: Toast notification após adicionar
6. **Microinteração**: Scale 1.05 no hover do botão

**Justificativa:**
- ✅ Clean e moderno (2025 trend)
- ✅ Mobile-first (touch target adequado)
- ✅ Alta conversão (CTA claro)
- ✅ Feedback visual imediato
- ✅ Acessibilidade (WCAG 2.2)

## 📐 Especificações Técnicas

```scss
// Card
.product-card {
    border-radius: 12px;
    border: 1px solid $gray-200;
    transition: all 0.3s ease;
}

// Botão
.btn-add-cart {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    position: absolute;
    bottom: 12px;
    right: 12px;
}

// Spacing
.card-body {
    padding: 16px;
    gap: 12px;
}
```


