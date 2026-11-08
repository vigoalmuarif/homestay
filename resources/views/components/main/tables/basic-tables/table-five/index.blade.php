@props(['data' => null])
@php
    $orders = $data ?? (include resource_path('views/components/main/tables/basic-tables/table-five/data.php'))['recentOrdersData'];
    $categoryMap = ['uiKits' => 'UI Kits', 'templates' => 'Templates', 'saas' => 'SaaS'];
@endphp
<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pt-4 pb-3 sm:px-6 dark:border-gray-800 dark:bg-white/3']) }}>
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Orders</h3>
        </div>

        <div class="flex items-center gap-3">
            <x-ui.button variant="outline">
                <x-svg.horizontal-slide />
                Filter
            </x-ui.button>
            <x-ui.button variant="outline">See all</x-ui.button>
        </div>
    </div>

    <div class="-mx-4 overflow-x-auto sm:-mx-6">
        <table class="min-w-full">
            <!-- Table Header -->
            <thead class="border-y border-gray-100 dark:border-gray-800">
                <tr>
                    <th class="py-3 ps-4 text-start text-xs font-medium text-gray-500 sm:ps-6 dark:text-gray-400">Products</th>
                    <th class="py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400">Category</th>
                    <th class="py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400">Country</th>
                    <th class="py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400">CR</th>
                    <th class="py-3 pe-4 text-start text-xs font-medium text-gray-500 sm:pe-6 dark:text-gray-400">Value</th>
                </tr>
            </thead>

            <!-- Table Body -->
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($orders as $order)
                    <tr>
                        <td class="py-3 ps-4 text-start text-sm font-medium text-gray-800 sm:ps-6 dark:text-white/90">{{ $order['name'] }}</td>
                        <td class="py-3 text-start text-sm text-gray-500 dark:text-gray-400">{{ $categoryMap[$order['category']] ?? $order['category'] }}</td>
                        <td class="py-3 text-start text-sm text-gray-500 dark:text-gray-400">
                            <div class="h-5 w-5">
                                <img src="{{ $order['country'] }}" class="h-5 w-5 rounded-sm object-fill" alt="{{ $order['name'] }}" width="20" height="20" />
                            </div>
                        </td>
                        <td class="py-3 text-start text-sm text-gray-500 dark:text-gray-400">{{ $order['cr'] }}</td>
                        <td class="py-3 pe-4 text-start text-sm font-medium text-green-600 sm:pe-6 dark:text-green-500">${{ $order['value'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
