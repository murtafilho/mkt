@extends('layouts.admin')

@section('title', 'Pedido #' . $order->order_number . ' - Admin')

@section('header', 'Pedido #' . $order->order_number)

@section('page-content')
    <div class="vstack gap-4">
        {{-- Back Button --}}
        <div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-link text-primary p-0">
                <i class="bi bi-arrow-left me-1"></i>
                Voltar para lista de pedidos
            </a>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Order Header --}}
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h2 class="h3 fw-bold mb-1">Pedido #{{ $order->order_number }}</h2>
                        <p class="small text-muted mb-0">
                            <i class="bi bi-calendar3 me-1"></i>
                            Criado em {{ $order->created_at->format('d/m/Y \à\s H:i') }}
                        </p>
                    </div>
                    <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'primary') }} fs-5 px-3 py-2">
                        {{ $order->status_label }}
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Left Column: Details --}}
            <div class="col-lg-8">
                {{-- Customer Information --}}
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h3 class="h5 fw-medium mb-0">
                            <i class="bi bi-person me-2"></i>
                            Informações do Cliente
                        </h3>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <div class="col-sm-4">
                                <dt class="small text-muted">Nome:</dt>
                            </div>
                            <div class="col-sm-8">
                                <dd class="mb-2">{{ $order->user->name }}</dd>
                            </div>

                            <div class="col-sm-4">
                                <dt class="small text-muted">Email:</dt>
                            </div>
                            <div class="col-sm-8">
                                <dd class="mb-2">{{ $order->user->email }}</dd>
                            </div>

                            @if($order->user->phone)
                                <div class="col-sm-4">
                                    <dt class="small text-muted">Telefone:</dt>
                                </div>
                                <div class="col-sm-8">
                                    <dd class="mb-0">{{ $order->user->phone }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                {{-- Seller Information --}}
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h3 class="h5 fw-medium mb-0">
                            <i class="bi bi-shop me-2"></i>
                            Informações do Vendedor
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            @if($order->seller->hasMedia('seller_logo'))
                                <img src="{{ $order->seller->getFirstMediaUrl('seller_logo', 'thumb') }}"
                                     alt="{{ $order->seller->store_name }}"
                                     loading="lazy"
                                     class="rounded-circle"
                                     style="width: 48px; height: 48px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                     style="width: 48px; height: 48px;">
                                    <span class="fw-medium">{{ substr($order->seller->store_name, 0, 2) }}</span>
                                </div>
                            @endif
                            <div>
                                <a href="{{ route('admin.sellers.show', $order->seller) }}" class="fw-medium text-primary text-decoration-none">
                                    {{ $order->seller->store_name }}
                                </a>
                                <p class="small text-muted mb-0">{{ $order->seller->user->email }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Delivery Address --}}
                @if($order->address)
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h3 class="h5 fw-medium mb-0">
                                <i class="bi bi-geo-alt me-2"></i>
                                Endereço de Entrega
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="small">
                                <p class="mb-1">{{ $order->address->street }}, {{ $order->address->number }}</p>
                                @if($order->address->complement)
                                    <p class="mb-1">{{ $order->address->complement }}</p>
                                @endif
                                <p class="mb-1">{{ $order->address->neighborhood }}</p>
                                <p class="mb-1">{{ $order->address->city }} - {{ $order->address->state }}</p>
                                <p class="mb-0 fw-medium">CEP: {{ $order->address->postal_code }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Atenção:</strong> Endereço de entrega não disponível para este pedido (pedido criado antes da implementação do sistema de endereços).
                    </div>
                @endif

                {{-- Order Items --}}
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h3 class="h5 fw-medium mb-0">
                            <i class="bi bi-box-seam me-2"></i>
                            Itens do Pedido
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="vstack gap-3">
                            @foreach($order->items as $item)
                                <div class="d-flex align-items-center gap-3 pb-3 border-bottom {{ $loop->last ? 'border-0 pb-0' : '' }}">
                                    @if($item->product && $item->product->hasMedia('product_images'))
                                        <img src="{{ $item->product->getFirstMediaUrl('product_images', 'thumb') }}"
                                             alt="{{ $item->product_name }}"
                                             loading="lazy"
                                             class="rounded"
                                             style="width: 64px; height: 64px; object-fit: cover;">
                                    @else
                                        <div class="rounded bg-light d-flex align-items-center justify-content-center"
                                             style="width: 64px; height: 64px;">
                                            <i class="bi bi-image text-muted fs-3"></i>
                                        </div>
                                    @endif
                                    <div class="flex-fill">
                                        <h4 class="h6 fw-medium mb-1">{{ $item->product_name }}</h4>
                                        <p class="small text-muted mb-0">Quantidade: {{ $item->quantity }}</p>
                                        <p class="small text-muted mb-0">Preço unitário: R$ {{ number_format($item->unit_price, 2, ',', '.') }}</p>
                                    </div>
                                    <div class="text-end">
                                        <p class="fw-semibold mb-0">R$ {{ number_format($item->quantity * $item->unit_price, 2, ',', '.') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Order History/Timeline --}}
                <div class="card">
                    <div class="card-header bg-white">
                        <h3 class="h5 fw-medium mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Histórico do Pedido
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($order->history && $order->history->isNotEmpty())
                            <div class="timeline">
                                @foreach($order->history as $history)
                                    <div class="d-flex gap-3 mb-3 {{ $loop->last ? 'mb-0' : '' }}">
                                        <div class="flex-shrink-0">
                                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center"
                                                 style="width: 32px; height: 32px;">
                                                <i class="bi bi-circle-fill text-white" style="font-size: 0.5rem;"></i>
                                            </div>
                                        </div>
                                        <div class="flex-fill">
                                            <p class="mb-1 fw-medium">{{ $history->status_label }}</p>
                                            @if($history->note)
                                                <p class="mb-1 small text-muted">{{ $history->note }}</p>
                                            @endif
                                            <p class="mb-0 small text-muted">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                {{ $history->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0 small">Nenhum histórico disponível.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right Column: Actions --}}
            <div class="col-lg-4">
                {{-- Order Summary --}}
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h3 class="h5 fw-medium mb-0">
                            <i class="bi bi-receipt me-2"></i>
                            Resumo do Pedido
                        </h3>
                    </div>
                    <div class="card-body">
                        <dl class="row small mb-0">
                            <div class="col-6">
                                <dt>Subtotal:</dt>
                            </div>
                            <div class="col-6 text-end">
                                <dd class="mb-2">R$ {{ number_format($order->subtotal, 2, ',', '.') }}</dd>
                            </div>

                            <div class="col-6">
                                <dt>Frete:</dt>
                            </div>
                            <div class="col-6 text-end">
                                <dd class="mb-2">R$ {{ number_format($order->shipping_fee, 2, ',', '.') }}</dd>
                            </div>

                            @if($order->discount > 0)
                                <div class="col-6">
                                    <dt class="text-success">Desconto:</dt>
                                </div>
                                <div class="col-6 text-end">
                                    <dd class="mb-2 text-success">- R$ {{ number_format($order->discount, 2, ',', '.') }}</dd>
                                </div>
                            @endif

                            <div class="col-12"><hr class="my-2"></div>

                            <div class="col-6">
                                <dt class="fw-bold">Total:</dt>
                            </div>
                            <div class="col-6 text-end">
                                <dd class="mb-0 fw-bold">R$ {{ number_format($order->total, 2, ',', '.') }}</dd>
                            </div>
                        </dl>

                        @if($order->tracking_code)
                            <div class="mt-3 pt-3 border-top">
                                <dt class="small fw-medium mb-1">Código de Rastreio:</dt>
                                <dd class="small font-monospace bg-light px-3 py-2 rounded mb-0">
                                    {{ $order->tracking_code }}
                                </dd>
                            </div>
                        @endif

                        @if($order->paid_at)
                            <div class="mt-3 pt-3 border-top">
                                <dt class="small fw-medium">Pago em:</dt>
                                <dd class="small mb-0">{{ $order->paid_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        @endif

                        @if($order->notes)
                            <div class="mt-3 pt-3 border-top">
                                <dt class="small fw-medium mb-1">Observações:</dt>
                                <dd class="small text-muted mb-0">{{ $order->notes }}</dd>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Update Status Form --}}
                @if($order->status !== 'cancelled' && $order->status !== 'delivered')
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h3 class="h5 fw-medium mb-0">
                                <i class="bi bi-arrow-repeat me-2"></i>
                                Atualizar Status
                            </h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                                @csrf
                                @method('PATCH')

                                <div class="mb-3">
                                    <label for="status" class="form-label">Novo Status</label>
                                    <select name="status" id="status" class="form-select" required>
                                        <option value="">Selecione um status</option>
                                        <option value="awaiting_payment">Aguardando Pagamento</option>
                                        <option value="paid">Pago</option>
                                        <option value="preparing">Preparando</option>
                                        <option value="shipped">Enviado</option>
                                        <option value="delivered">Entregue</option>
                                    </select>
                                </div>

                                <div class="mb-3" id="tracking-code-field" style="display: none;">
                                    <label for="tracking_code" class="form-label">Código de Rastreio</label>
                                    <input type="text" name="tracking_code" id="tracking_code" class="form-control" placeholder="Ex: BR123456789BR">
                                </div>

                                <div class="mb-3">
                                    <label for="note" class="form-label">Observação (opcional)</label>
                                    <textarea name="note" id="note" rows="3" class="form-control" placeholder="Adicione uma observação sobre esta atualização..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Atualizar Status
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- Cancel Order --}}
                @if(in_array($order->status, ['awaiting_payment', 'paid', 'preparing']))
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h3 class="h5 fw-medium mb-0">
                                <i class="bi bi-x-circle me-2"></i>
                                Cancelar Pedido
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-3">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Atenção: Esta ação irá cancelar o pedido e restaurar o estoque dos produtos.
                            </p>
                            <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja cancelar este pedido? Esta ação não pode ser desfeita.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="bi bi-x-circle me-2"></i>
                                    Cancelar Pedido
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    // Show/hide tracking code field based on status selection
    document.getElementById('status').addEventListener('change', function() {
        const trackingCodeField = document.getElementById('tracking-code-field');
        if (this.value === 'shipped') {
            trackingCodeField.style.display = 'block';
            document.getElementById('tracking_code').required = true;
        } else {
            trackingCodeField.style.display = 'none';
            document.getElementById('tracking_code').required = false;
        }
    });
    </script>
    @endpush
@endsection
