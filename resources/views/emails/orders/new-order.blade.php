<x-mail::message>
# Novo Pedido Recebido! 🔔

Olá **{{ $seller->store_name }}**,

Você recebeu um novo pedido! O pagamento foi confirmado e o pedido está aguardando preparação.

## Informações do Pedido

**Pedido:** #{{ $order->id }}
**Data:** {{ $order->created_at->format('d/m/Y H:i') }}
**Status:** Pago ✅
**Valor Total:** R$ {{ number_format($order->total, 2, ',', '.') }}

### Itens do Pedido

@foreach($items as $item)
- **{{ $item->product->name }}**
 - Quantidade: {{ $item->quantity }}
 - Preço unitário: R$ {{ number_format($item->unit_price, 2, ',', '.') }}
 - Subtotal: R$ {{ number_format($item->unit_price * $item->quantity, 2, ',', '.') }}
@endforeach

---

**Subtotal:** R$ {{ number_format($order->subtotal, 2, ',', '.') }}
@if($order->shipping_fee > 0)
**Frete:** R$ {{ number_format($order->shipping_fee, 2, ',', '.') }}
@endif
@if($order->discount > 0)
**Desconto:** -R$ {{ number_format($order->discount, 2, ',', '.') }}
@endif

## Dados do Cliente

**Nome:** {{ $customer->name }}
**E-mail:** {{ $customer->email }}
@if($customer->phone)
**Telefone:** {{ $customer->phone }}
@endif

## Endereço de Entrega

{{ $address->recipient_name }}
{{ $address->street }}, {{ $address->number }}@if($address->complement), {{ $address->complement }}@endif
{{ $address->neighborhood }} - {{ $address->city }}/{{ $address->state }}
CEP: {{ $address->postal_code }}
Telefone: {{ $address->recipient_phone }}

@if($order->notes)
## Observações do Cliente

{{ $order->notes }}
@endif

## Próximas Ações

1. ✅ Prepare o pedido para envio
2. 📦 Atualize o status do pedido no painel do vendedor
3. 🚚 Adicione o código de rastreamento quando disponível

<x-mail::button :url="url('/seller/orders/' . $order->id)">
Ver Detalhes do Pedido
</x-mail::button>

Atenciosamente,<br>
{{ config('app.name') }}
</x-mail::message>
