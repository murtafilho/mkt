<x-mail::message>
# Pedido Confirmado com Sucesso

Olá **{{ $customer->name }}**,

Seu pedido **#{{ $order->id }}** foi confirmado e o pagamento foi aprovado! Agradecemos pela sua compra no Vale do Sol.

## Informações do Pedido

**Vendedor:** {{ $seller->store_name }}<br>
**Data do Pedido:** {{ $order->created_at->format('d/m/Y') }} às {{ $order->created_at->format('H:i') }}<br>
**Status do Pagamento:** Aprovado

### Itens do Pedido

@foreach($items as $item)
- **{{ $item->product->name }}** ({{ $item->quantity }}x) - R$ {{ number_format($item->unit_price, 2, ',', '.') }}
@endforeach

---

**Subtotal:** R$ {{ number_format($order->subtotal, 2, ',', '.') }}
@if($order->shipping_fee > 0)
**Frete:** R$ {{ number_format($order->shipping_fee, 2, ',', '.') }}
@endif
@if($order->discount > 0)
**Desconto:** -R$ {{ number_format($order->discount, 2, ',', '.') }}
@endif
**Total Pago:** R$ {{ number_format($order->total, 2, ',', '.') }}

## Endereço de Entrega

{{ $address->recipient_name }}
{{ $address->street }}, {{ $address->number }}@if($address->complement), {{ $address->complement }}@endif
{{ $address->neighborhood }} - {{ $address->city }}/{{ $address->state }}
CEP: {{ $address->postal_code }}
Telefone: {{ $address->recipient_phone }}

@if($order->notes)
**Observações:** {{ $order->notes }}
@endif

## Próximos Passos

1. **Preparação do Pedido**: O vendedor {{ $seller->store_name }} foi notificado e irá preparar os itens
2. **Acompanhamento**: Você receberá atualizações por e-mail sobre cada etapa do pedido
3. **Envio**: Assim que o pedido for despachado, você receberá o código de rastreamento

<x-mail::button :url="url('/orders/' . $order->id)">
Acompanhar Meu Pedido
</x-mail::button>

Agradecemos pela sua compra e por apoiar o comércio local!

**Favorencendo a vocação e o comércio local**

Atenciosamente,<br>
Equipe {{ config('app.name') }}
</x-mail::message>
