@props(['order'])
@php
    $statusColor = $order['status'] === 'delivered' ? 'success' : ($order['status'] === 'pending' ? 'warning' : 'error');
@endphp
<tr>
    <td class="py-3">
        <div class="flex items-center gap-3">
            <div class="h-12.5 w-12.5 overflow-hidden rounded-md">
                <img width="50" height="50" src="{{ $order['image'] }}" class="h-12.5 w-12.5" alt="{{ $order['name'] }}" />
            </div>
            <div>
                <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                    {{ $order['name'] }}
                </p>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ \App\Support\I18n::t('ecommerce.recentOrders.variants', ['count' => $order['variants']]) }}
                </span>
            </div>
        </div>
    </td>
    <td class="py-3 text-sm text-gray-500 dark:text-gray-400">
        {{ __('ecommerce.recentOrders.categories.' . $order['category']) }}
    </td>
    <td class="py-3 text-sm text-gray-500 dark:text-gray-400">
        {{ $order['price'] }}
    </td>
    <td class="py-3 text-sm text-gray-500 dark:text-gray-400">
        <x-ui.badge size="sm" :color="$statusColor">
            {{ __('ecommerce.recentOrders.statuses.' . $order['status']) }}
        </x-ui.badge>
    </td>
</tr>
