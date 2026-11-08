@use('Illuminate\Support\Js')
@props(['data' => null])
@php
    $transactions = $data ?? (include resource_path('views/components/main/tables/basic-tables/table-three/data.php'))['latestTransactionsData'];
    $brandConfig = [
        'paypal' => ['icon' => 'paypal', 'iconClass' => 'size-4', 'bgClass' => 'bg-[#1b4bf1] text-white'],
        'apple' => ['icon' => 'apple', 'iconClass' => 'size-4 text-white dark:text-black', 'bgClass' => 'bg-black dark:bg-white'],
        'netflix' => ['icon' => 'netflix', 'iconClass' => 'size-4', 'bgClass' => 'bg-black text-white'],
        'tesla' => ['icon' => 'tesla', 'iconClass' => 'size-4 text-white', 'bgClass' => 'bg-[#E82127]'],
        'amazon' => ['icon' => 'amazon', 'iconClass' => 'size-4.5 text-white', 'bgClass' => 'bg-black'],
        'spotify' => ['icon' => 'spotify', 'iconClass' => 'size-4 text-white', 'bgClass' => 'bg-[#1DB954]'],
        'envato' => ['icon' => 'envato', 'iconClass' => 'size-3.5 text-white', 'bgClass' => 'bg-[#82B541]'],
    ];
    $badgeColor = fn ($status) => match (strtolower($status)) { 'success' => 'success', 'pending' => 'warning', default => 'error' };
    $searchable = array_map(fn ($t) => strtolower(implode(' ', [$t['name'], $t['category'], $t['status'], $t['price'], $t['date']])), $transactions);
    $headClass = 'h-11 px-4 py-3 text-start text-sm font-normal text-gray-500 dark:text-gray-400';
@endphp
<x-ui.card
    x-data="{
        searchQuery: '',
        haystack: {{ Js::from($searchable) }},
        matches(index) {
            const query = this.searchQuery.trim().toLowerCase();
            return !query || this.haystack[index].includes(query);
        },
        get visibleCount() {
            return this.haystack.filter((_, index) => this.matches(index)).length;
        },
    }"
    {{ $attributes->merge(['class' => 'w-full max-w-full min-w-0 gap-0 overflow-hidden rounded-2xl border border-gray-200 bg-white p-0 sm:p-0 dark:border-gray-800 dark:bg-white/3']) }}
>
    <!-- Table Header Controls -->
    <div class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div>
            <x-ui.card.title class="text-lg font-semibold text-gray-800 dark:text-white/90">Latest Transactions</x-ui.card.title>
        </div>

        <div class="flex w-full items-center gap-3 sm:w-auto">
            <x-ui.input-group class="h-10 flex-1 sm:w-75 sm:flex-initial">
                <x-ui.input-group.addon align="inline-start">
                    <x-svg.search class="size-5 text-gray-400" />
                </x-ui.input-group.addon>
                <x-ui.input-group.input type="text" placeholder="Search..." x-model="searchQuery" />
            </x-ui.input-group>

            <x-ui.button variant="outline" class="h-10 shrink-0 px-3.5 text-gray-700 sm:px-4 sm:py-2.5 dark:text-gray-300">
                <x-svg.calendar class="size-5" />
                <span class="hidden sm:inline">05 Feb - 06 March</span>
            </x-ui.button>
        </div>
    </div>

    <!-- Table Content -->
    <x-ui.card.content class="p-0">
        <x-ui.scroll-area class="w-full" viewport-class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-y border-gray-100 dark:border-gray-800">
                    <tr>
                        <th class="h-11 min-w-44 py-3 ps-5 text-start text-sm font-normal text-gray-500 sm:ps-6 dark:text-gray-400">Name</th>
                        <th class="{{ $headClass }} min-w-44">Date</th>
                        <th class="{{ $headClass }} min-w-32">Price</th>
                        <th class="{{ $headClass }} min-w-36">Category</th>
                        <th class="{{ $headClass }} min-w-28">Status</th>
                        <th class="h-11 min-w-16 py-3 pe-5 sm:pe-6"></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($transactions as $index => $transaction)
                        @php $brand = $brandConfig[$transaction['brand']] ?? null; @endphp
                        <tr x-show="matches({{ $index }})" class="h-16 border-b border-gray-100 dark:border-gray-800">
                            <td class="min-w-44 py-3 ps-5 text-start sm:ps-6">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-8 shrink-0 items-center justify-center overflow-hidden rounded-full {{ $brand['bgClass'] ?? '' }}">
                                        @if ($brand)
                                            <x-dynamic-component :component="'svg.' . $brand['icon']" :class="$brand['iconClass']" />
                                        @endif
                                    </div>
                                    <span class="text-sm font-medium whitespace-nowrap text-gray-800 dark:text-white/90">{{ $transaction['name'] }}</span>
                                </div>
                            </td>
                            <td class="min-w-44 px-4 py-3 text-start text-sm whitespace-nowrap text-gray-700 dark:text-gray-400">{{ $transaction['date'] }}</td>
                            <td class="min-w-32 px-4 py-3 text-start text-sm whitespace-nowrap text-gray-700 dark:text-gray-400">{{ $transaction['price'] }}</td>
                            <td class="min-w-36 px-4 py-3 text-start text-sm whitespace-nowrap text-gray-700 dark:text-gray-400">{{ $transaction['category'] }}</td>
                            <td class="min-w-28 px-4 py-3 text-start text-sm whitespace-nowrap">
                                <x-ui.badge size="sm" :color="$badgeColor($transaction['status'])">{{ $transaction['status'] }}</x-ui.badge>
                            </td>
                            <td class="min-w-16 py-3 pe-5 text-end whitespace-nowrap sm:pe-6">
                                <x-ui.button size="icon-sm" variant="ghost" aria-label="More options">
                                    <x-svg.horizontal-dots class="size-5" />
                                </x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                    <tr x-show="visibleCount === 0" style="display: none;">
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center">
                                <div class="mb-3 flex size-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                    <x-svg.box class="size-6 text-gray-800 dark:text-white/90" />
                                </div>
                                <h4 class="text-base font-semibold text-gray-800 dark:text-white/90">No transactions found</h4>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your search to find what you're looking for.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </x-ui.scroll-area>
    </x-ui.card.content>

    <!-- Table Footer - Pagination -->
    <x-main.tables.basic-tables.table-three.card-footer :current-page="1" :total-pages="10" />
</x-ui.card>
