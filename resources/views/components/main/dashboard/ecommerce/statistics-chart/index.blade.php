@php
    $config = include resource_path('views/components/main/dashboard/ecommerce/statistics-chart/data.php');
    $chart = [
        'categories' => collect($config['monthKeys'])->map(fn ($k) => __('ecommerce.months.' . $k))->all(),
        'labels' => ['sales' => __('ecommerce.statistics.sales'), 'revenue' => __('ecommerce.statistics.revenue')],
        'sales' => $config['sales'],
        'revenue' => $config['revenue'],
    ];
@endphp
<div x-data="{
        tab: 'overview',
        factor: 1,
        init() {
            const today = new Date();
            const start = new Date();
            start.setDate(today.getDate() - 30);
            window.flatpickr(this.$refs.picker, {
                mode: 'range',
                static: true,
                monthSelectorType: 'static',
                dateFormat: 'd M',
                defaultDate: [start, today],
                clickOpens: true,
                prevArrow: '<svg class=&quot;stroke-current&quot; width=&quot;20&quot; height=&quot;20&quot; viewBox=&quot;0 0 20 20&quot; fill=&quot;none&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;><path d=&quot;M12.5 15L7.5 10L12.5 5&quot; stroke=&quot;&quot; stroke-width=&quot;1.5&quot; stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot;/></svg>',
                nextArrow: '<svg class=&quot;stroke-current&quot; width=&quot;20&quot; height=&quot;20&quot; viewBox=&quot;0 0 20 20&quot; fill=&quot;none&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;><path d=&quot;M7.5 15L12.5 10L7.5 5&quot; stroke=&quot;&quot; stroke-width=&quot;1.5&quot; stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot;/></svg>',
                onChange: (dates) => {
                    if (dates.length === 2) {
                        const days = Math.round(Math.abs(dates[1].getTime() - dates[0].getTime()) / (1000 * 60 * 60 * 24));
                        this.factor = Math.max(0.6, Math.min(1.4, Number((days / 30).toFixed(2))));
                    }
                },
            });
            this.$watch('tab', () => this.push());
            this.$watch('factor', () => this.push());
        },
        push() {
            this.$refs.chart.dispatchEvent(new CustomEvent('chart-update', { detail: { tab: this.tab, factor: this.factor } }));
        },
    }"
    class="rounded-2xl border border-gray-200 bg-white px-5 pt-5 pb-5 sm:px-6 sm:pt-6 dark:border-gray-800 dark:bg-white/3">
    <x-main.dashboard.ecommerce.statistics-chart.card-header :tabs="$config['tabs']" />

    <div class="custom-scrollbar max-w-full overflow-x-auto">
        <div class="min-w-250 xl:min-w-full">
            <div x-ref="chart" data-dashboard-chart="ecommerce-statistics" data-config="{{ json_encode($chart) }}"></div>
        </div>
    </div>
</div>
