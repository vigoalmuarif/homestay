import ApexCharts from 'apexcharts';

window.ApexCharts = ApexCharts;

export const isDark = () => document.documentElement.classList.contains('dark');

/** Reads the JSON config rendered on the chart element (`data-config`). */
export const readConfig = (el) => JSON.parse(el.dataset.config || '{}');

/**
 * Renders an ApexCharts instance and rebuilds its options when the light / dark theme is toggled.
 * `build(dark)` must return the full options object.
 */
export function mountChart(el, build) {
    const chart = new ApexCharts(el, build(isDark()));
    chart.render();
    new MutationObserver(() => chart.updateOptions(build(isDark()))).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

    return chart;
}

/** Shared "area chart" tooltip used by the statistics / employee growth style charts. */
export const areaTooltip = ({ series: chartSeries, dataPointIndex, w }) => {
    const category = w.globals?.categoryLabels?.[dataPointIndex] ?? w.config.xaxis?.categories?.[dataPointIndex] ?? '';
    const colors = w.config.colors ?? ['#465FFF'];
    const seriesList = w.config.series ?? [];

    const rows = seriesList
        .map((s, idx) => {
            const val = chartSeries[idx]?.[dataPointIndex];
            const color = colors[idx] ?? '#465FFF';
            const formattedVal = typeof val === 'number' ? val.toLocaleString() : (val ?? '');
            return `
                <div class="flex items-center gap-2">
                  <div class="flex items-center gap-1.5">
                    <span class="size-1.5 rounded-full shrink-0" style="background-color: ${color}"></span>
                    <span class="w-13 text-xs text-gray-800 dark:text-white/90">${s.name ?? ''}</span>
                    <span class="text-xs font-medium text-gray-400 dark:text-gray-500">:</span>
                  </div>
                  <span class="text-xs font-medium text-gray-800 dark:text-white/90">${formattedVal}</span>
                </div>
              `;
        })
        .join('');

    return `
            <div class="min-w-32.5">
              <p class="mb-1 text-[10px] leading-4 text-gray-500 dark:text-gray-400">${category}</p>
              <div class="flex flex-col gap-1">
                ${rows}
              </div>
            </div>
          `;
};
