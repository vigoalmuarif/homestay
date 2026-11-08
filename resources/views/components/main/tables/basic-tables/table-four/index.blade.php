@props(['data' => null])
@php
    $campaigns = $data ?? (include resource_path('views/components/main/tables/basic-tables/table-four/data.php'))['featuredCampaignsData'];
    $badgeColor = fn ($status) => match (strtolower($status)) { 'success' => 'success', 'pending' => 'warning', default => 'error' };
    $headClass = 'py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400';
    $itemClass = 'flex w-full rounded-lg text-start font-normal text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300';
@endphp
<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-4 pb-3 sm:px-6 dark:border-gray-800 dark:bg-white/3']) }}>
    <div class="mb-4 flex justify-between gap-2 sm:items-center">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Featured Campaigns</h3>
        </div>

        <x-ui.dropdown-menu>
            <x-ui.dropdown-menu.trigger class="dropdown-toggle">
                <x-svg.vertical-dots class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-300" />
            </x-ui.dropdown-menu.trigger>
            <x-ui.dropdown-menu.content align="end" class="w-40 p-2">
                <x-ui.dropdown-menu.item class="{{ $itemClass }}">View More</x-ui.dropdown-menu.item>
                <x-ui.dropdown-menu.item class="{{ $itemClass }}">Delete</x-ui.dropdown-menu.item>
            </x-ui.dropdown-menu.content>
        </x-ui.dropdown-menu>
    </div>

    <div class="custom-scrollbar max-w-full overflow-x-auto">
        <div class="min-w-full">
            <table class="min-w-full">
                <!-- Table Header -->
                <thead class="border-y border-gray-100 dark:border-gray-800">
                    <tr>
                        <th class="{{ $headClass }}">Products</th>
                        <th class="{{ $headClass }}">Campaign</th>
                        <th class="{{ $headClass }}">Status</th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($campaigns as $campaign)
                        <tr>
                            <td class="py-3">
                                <div class="flex items-center gap-4.5">
                                    <div class="h-10 w-10 overflow-hidden rounded-full">
                                        <img width="40" height="40" src="{{ $campaign['creator']['image'] }}" alt="{{ $campaign['creator']['name'] }}" />
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-700 dark:text-gray-400">{{ $campaign['creator']['name'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">
                                <div class="flex w-full items-center gap-5">
                                    <div class="w-full max-w-8">
                                        <img width="40" height="40" src="{{ $campaign['campaign']['image'] }}" alt="brand" />
                                    </div>
                                    <div class="truncate">
                                        <p class="mb-0.5 truncate text-sm font-medium text-gray-700 dark:text-gray-400">{{ $campaign['campaign']['name'] }}</p>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $campaign['campaign']['type'] }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">
                                <x-ui.badge size="sm" :color="$badgeColor($campaign['status'])">{{ $campaign['status'] }}</x-ui.badge>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
