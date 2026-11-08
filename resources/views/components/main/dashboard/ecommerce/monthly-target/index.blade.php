<div class="flex h-full flex-col justify-between rounded-2xl border border-gray-200 bg-gray-100 dark:border-gray-800 dark:bg-white/3">
    <div class="shadow-default flex flex-1 flex-col justify-between rounded-2xl bg-white px-5 pt-5 pb-5 sm:px-6 sm:pt-6 sm:pb-11 dark:bg-gray-900">
        <x-main.dashboard.ecommerce.monthly-target.card-header />

        <div>
            <div class="relative">
                <div id="chartDarkStyle" class="max-h-45 overflow-hidden">
                    <div data-dashboard-chart="ecommerce-monthly-target" data-config="{{ json_encode(['series' => [75.55]]) }}"></div>
                </div>

                <span class="absolute bottom-2.5 left-1/2 -translate-x-1/2 rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-600 dark:bg-green-500/15 dark:text-green-500">
                    +10%
                </span>
            </div>

            <p class="mx-auto mt-4.5 w-full max-w-95 text-center text-sm text-gray-500 sm:text-base dark:text-gray-400">
                {{ \App\Support\I18n::t('ecommerce.monthlyTarget.earned', ['amount' => '$3287']) }}
            </p>
        </div>
    </div>

    <x-main.dashboard.ecommerce.monthly-target.monthly-stats />
</div>
