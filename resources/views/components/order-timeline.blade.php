@props(['order'])

@php
 $statuses = [
 'awaiting_payment' => ['label' => 'Aguardando Pagamento', 'icon' => 'clock'],
 'paid' => ['label' => 'Pago', 'icon' => 'check-circle'],
 'preparing' => ['label' => 'Em Preparação', 'icon' => 'cog'],
 'shipped' => ['label' => 'Enviado', 'icon' => 'truck'],
 'delivered' => ['label' => 'Entregue', 'icon' => 'check-double'],
 'cancelled' => ['label' => 'Cancelado', 'icon' => 'x-circle'],
 ];

 $currentStatus = $order->status;
 $isCancelled = $currentStatus === 'cancelled';

 // Define order of statuses for timeline
 $statusOrder = ['awaiting_payment', 'paid', 'preparing', 'shipped', 'delivered'];
 $currentStatusIndex = array_search($currentStatus, $statusOrder);
@endphp

<div class="relative space-y-6">
 @foreach($statuses as $statusKey => $statusData)
 @php
 // Skip non-cancelled statuses if order is cancelled
 if ($isCancelled && $statusKey !== 'awaiting_payment' && $statusKey !== 'paid' && $statusKey !== 'cancelled') {
 continue;
 }

 // Skip cancelled status if order is not cancelled
 if (!$isCancelled && $statusKey === 'cancelled') {
 continue;
 }

 $statusIndex = array_search($statusKey, $statusOrder);
 $isActive = $statusKey === $currentStatus;
 $isCompleted = !$isCancelled && $statusIndex !== false && $currentStatusIndex !== false && $statusIndex < $currentStatusIndex;
 $isPending = !$isCancelled && $statusIndex !== false && $currentStatusIndex !== false && $statusIndex > $currentStatusIndex;

 // Get history entry for this status
 $historyEntry = $order->history->firstWhere('new_status', $statusKey);
 @endphp

 <div class="flex items-start relative">
 {{-- Vertical line (except for last item) --}}
 @if(!$loop->last)
 <div class="absolute left-5 top-10 w-0.5 h-6 -ml-px
 @if($isCompleted) bg-green-600 @else bg-gray-300 @endif">
 </div>
 @endif

 {{-- Icon container --}}
 <div class="flex-shrink-0 z-10">
 <div class="w-10 h-10 rounded-full flex items-center justify-center
 @if($isActive && !$isCancelled) bg-primary-600 text-white
 @elseif($isActive && $isCancelled) bg-red-600 text-neutral-900
 @elseif($isCompleted) bg-green-600 text-neutral-900
 @else bg-gray-300 text-neutral-600
 @endif">
 @if($statusData['icon'] === 'clock')
 <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 @elseif($statusData['icon'] === 'check-circle')
 <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 @elseif($statusData['icon'] === 'cog')
 <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
 </svg>
 @elseif($statusData['icon'] === 'truck')
 <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
 </svg>
 @elseif($statusData['icon'] === 'check-double')
 <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
 </svg>
 @elseif($statusData['icon'] === 'x-circle')
 <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 @endif
 </div>
 </div>

 {{-- Label and Date --}}
 <div class="ml-4 flex-1">
 <h4 class="font-medium
 @if($isActive && !$isCancelled) text-primary-600
 @elseif($isActive && $isCancelled) text-red-600
 @else text-neutral-900
 @endif">
 {{ $statusData['label'] }}
 </h4>
 @if($historyEntry)
 <p class="text-sm text-neutral-500 mt-0.5">
 {{ $historyEntry->created_at->format('d/m/Y \à\s H:i') }}
 </p>
 @if($historyEntry->note)
 <p class="text-sm text-neutral-600 mt-1">{{ $historyEntry->note }}</p>
 @endif
 @elseif($isActive)
 <p class="text-sm text-neutral-500 mt-0.5">
 {{ $order->updated_at->format('d/m/Y \à\s H:i') }}
 </p>
 @endif
 </div>
 </div>
 @endforeach
</div>
