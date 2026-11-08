@php
    $config = include resource_path('views/components/main/dashboard/ecommerce/monthly-sales-chart/data.php');
    $chart = [
        'categories' => collect($config['monthKeys'])->map(fn ($k) => __('ecommerce.months.' . $k))->all(),
        'data' => $config['data'],
        'seriesName' => __('ecommerce.monthlySales.series'),
    ];
@endphp
<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 pb-5 sm:px-6 sm:pt-6 sm:pb-5 dark:border-gray-800 dark:bg-white/3">
    <x-main.dashboard.ecommerce.monthly-sales-chart.card-header />
    <x-ui.scroll-area class="max-w-full">
        <div class="min-w-162.5 xl:min-w-full">
            <div data-dashboard-chart="ecommerce-monthly-sales" data-config="{{ json_encode($chart) }}"></div>
        </div>
    </x-ui.scroll-area>
</div>
