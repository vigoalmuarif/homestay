@use('Illuminate\Support\Js')
@props(['data' => null])
@php
    $orders = $data ?? (include resource_path('views/components/main/tables/basic-tables/table-two/data.php'))['recentOrdersData'];
    $filterOptions = [['value' => 'all', 'label' => 'All'], ['value' => 'complete', 'label' => 'Complete'], ['value' => 'pending', 'label' => 'Pending'], ['value' => 'cancel', 'label' => 'Cancel']];
    $statusConfig = [
        'complete' => ['label' => 'Complete', 'class' => \App\Support\Ui\BadgeVariants::classes('light', 'success', 'sm')],
        'pending' => ['label' => 'Pending', 'class' => \App\Support\Ui\BadgeVariants::classes('light', 'warning', 'sm')],
        'cancel' => ['label' => 'Cancel', 'class' => \App\Support\Ui\BadgeVariants::classes('light', 'error', 'sm')],
    ];
    $headClass = 'px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400';
@endphp
<x-ui.card
    x-data="{
        orders: {{ Js::from($orders) }},
        statusConfig: {{ Js::from($statusConfig) }},
        searchQuery: '',
        statusFilter: 'all',
        rowSelection: { '1': true },
        get rows() {
            const query = this.searchQuery.trim().toLowerCase();
            return this.orders.filter((order) => {
                if (this.statusFilter !== 'all' && order.status !== this.statusFilter) return false;
                if (!query) return true;
                return order.dealId.toLowerCase().includes(query)
                    || order.user.name.toLowerCase().includes(query)
                    || order.user.email.toLowerCase().includes(query)
                    || order.productName.toLowerCase().includes(query);
            });
        },
        get isAllSelected() {
            return this.rows.length > 0 && this.rows.every((order) => !!this.rowSelection[order.id]);
        },
        handleSelectAll() {
            const next = { ...this.rowSelection };
            const select = !this.isAllSelected;
            this.rows.forEach((order) => { if (select) next[order.id] = true; else delete next[order.id]; });
            this.rowSelection = next;
        },
        handleRowSelect(id) {
            const next = { ...this.rowSelection };
            if (next[id]) delete next[id]; else next[id] = true;
            this.rowSelection = next;
        },
        handleDelete(id) {
            this.orders = this.orders.filter((order) => order.id !== id);
            const next = { ...this.rowSelection };
            delete next[id];
            this.rowSelection = next;
        },
        config(status) { return this.statusConfig[status] ?? { label: status, class: this.statusConfig.pending.class }; },
    }"
    {{ $attributes->merge(['class' => 'w-full max-w-full min-w-0 gap-0 overflow-hidden rounded-2xl border border-gray-200 bg-white p-0 sm:p-0 dark:border-gray-800 dark:bg-white/3']) }}
>
    <!-- Table Header Controls -->
    <div class="flex flex-col gap-4 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div>
            <x-ui.card.title class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Orders</x-ui.card.title>
        </div>

        <div class="flex w-full items-center gap-3 sm:w-auto">
            <div class="flex-1 sm:w-64 sm:flex-initial md:w-72">
                <x-ui.input-group class="h-10 rounded-[10px] border-gray-300 dark:border-gray-700">
                    <x-ui.input-group.addon class="ps-3.5 pe-0">
                        <x-svg.search class="size-5 text-gray-700 dark:text-gray-300" />
                    </x-ui.input-group.addon>
                    <x-ui.input-group.input type="text" placeholder="Search..." x-model="searchQuery" class="text-sm placeholder:text-gray-400 dark:placeholder:text-gray-500" />
                </x-ui.input-group>
            </div>

            <x-ui.dropdown-menu>
                <x-ui.dropdown-menu.trigger as-child>
                    <x-ui.button variant="outline" size="sm" class="h-10 shrink-0 gap-2 rounded-[10px] border-gray-300 px-4 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                        <x-svg.horizontal-slide class="size-5" />
                        Filter
                    </x-ui.button>
                </x-ui.dropdown-menu.trigger>
                <x-ui.dropdown-menu.content align="end" class="w-40">
                    @foreach ($filterOptions as $option)
                        <x-ui.dropdown-menu.item ::class="statusFilter === '{{ $option['value'] }}' ? 'font-semibold text-brand-500 dark:text-brand-400' : ''" @click="statusFilter = '{{ $option['value'] }}'">
                            {{ $option['label'] }}
                        </x-ui.dropdown-menu.item>
                    @endforeach
                </x-ui.dropdown-menu.content>
            </x-ui.dropdown-menu>
        </div>
    </div>

    <!-- Table Content -->
    <div class="max-w-full overflow-x-auto">
        <div class="min-w-175 2xl:min-w-202">
            <table class="min-w-full">
                <thead class="border-y border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                    <tr>
                        <th class="{{ $headClass }}">
                            <div class="flex items-center gap-3">
                                <x-ui.checkbox ::checked="isAllSelected" aria-label="Select all orders" @change="handleSelectAll()" />
                                <span>Deal ID</span>
                            </div>
                        </th>
                        <th class="{{ $headClass }}">Customer</th>
                        <th class="{{ $headClass }}">Product/Service</th>
                        <th class="{{ $headClass }}">Deal Value</th>
                        <th class="{{ $headClass }}">Close Date</th>
                        <th class="{{ $headClass }}">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Action</th>
                    </tr>
                </thead>

                <tbody class="min-h-96 divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-for="order in rows" :key="order.id">
                        <tr>
                            <td class="px-6 py-4 text-start">
                                <div class="flex items-center gap-3">
                                    <x-ui.checkbox ::checked="!!rowSelection[order.id]" x-bind:aria-label="`Select deal ${order.dealId}`" @change="handleRowSelect(order.id)" />
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-400" x-text="order.dealId"></span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-start">
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar class="size-10">
                                        <x-ui.avatar.fallback ::class="order.user.avatarClassName" class="size-full text-xs font-semibold select-none">
                                            <span x-text="order.user.initials"></span>
                                        </x-ui.avatar.fallback>
                                    </x-ui.avatar>
                                    <div>
                                        <span class="block text-sm font-medium text-gray-800 dark:text-white/90" x-text="order.user.name"></span>
                                        <span class="block text-sm text-gray-500 dark:text-gray-400" x-text="order.user.email"></span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-start"><span class="text-sm text-gray-700 dark:text-gray-400" x-text="order.productName"></span></td>
                            <td class="px-6 py-4 text-start"><span class="text-sm text-gray-700 dark:text-gray-400" x-text="order.dealValue"></span></td>
                            <td class="px-6 py-4 text-start"><span class="text-sm text-gray-700 dark:text-gray-400" x-text="order.closeDate"></span></td>

                            <td class="px-6 py-4 text-start">
                                <span data-slot="badge" x-bind:class="config(order.status).class" x-text="config(order.status).label"></span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <x-ui.button variant="ghost" size="icon-xs" class="text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-500" aria-label="Delete order" @click="handleDelete(order.id)">
                                    <x-svg.trash-bin class="size-5" />
                                </x-ui.button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="rows.length === 0" style="display: none;">
                        <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center">
                                <div class="mb-3 flex size-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                    <x-svg.box class="size-6 text-gray-800 dark:text-white/90" />
                                </div>
                                <h4 class="text-base font-semibold text-gray-800 dark:text-white/90">No orders found</h4>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your search or filter to find what you're looking for.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-ui.card>
