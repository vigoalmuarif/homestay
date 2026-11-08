@props(['tabs'])

<div class="mb-6 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            {{ __('ecommerce.statistics.title') }}
        </h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ __('ecommerce.statistics.subtitle') }}
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-3 sm:justify-end">
        <x-ui.button-group class="gap-0.5 rounded-lg bg-gray-100 p-0.5 *:data-slot:rounded-md! dark:bg-gray-900">
            @foreach ($tabs as $tab)
                <x-ui.button variant="none" size="sm" class="h-8 px-3 text-sm font-medium transition-colors"
                    x-bind:class="tab === '{{ $tab }}'
                        ? 'bg-white text-gray-900 shadow-theme-xs hover:bg-white hover:text-gray-900 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-800 dark:hover:text-white'
                        : 'text-gray-500 hover:bg-transparent hover:text-gray-900 dark:text-gray-400 dark:hover:bg-transparent dark:hover:text-white'"
                    x-on:click="tab = '{{ $tab }}'">
                    {{ __('ecommerce.statistics.' . $tab) }}
                </x-ui.button>
            @endforeach
        </x-ui.button-group>

        <div class="dashboard-datepicker relative inline-flex items-center">
            <x-svg.calendar class="pointer-events-none absolute start-3 top-1/2 z-10 -translate-y-1/2 text-gray-500 dark:text-gray-400" />
            <input x-ref="picker" placeholder="{{ __('ecommerce.statistics.selectDateRange') }}"
                class="h-9 w-40 cursor-pointer rounded-lg border border-gray-200 bg-white ps-9 pe-3 text-sm font-medium text-gray-700 shadow-theme-xs outline-none hover:bg-gray-50 focus:border-brand-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700/50" />
        </div>
    </div>
</div>
