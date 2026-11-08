import { mountChart } from '../helpers';

export default (el) => {
    let dark = false;
    const isDarkMode = { get value() { return dark; } };
    const computed = (fn) => fn;

    const darkCount = 18;
    const lightCount = 18;
    const grayCount = 18;
    const totalBars = darkCount + lightCount + grayCount; // 54 bars
    const options = computed(() => ({
        series: [{ name: 'Progress', data: Array(totalBars).fill(100) }],
        colors: [
            ...Array(darkCount).fill('#465FFF'),
            ...Array(lightCount).fill('#9CB9FF'),
            ...Array(grayCount).fill('#E5E7EB'),
        ],
        chart: {
            fontFamily: 'Outfit, sans-serif',
            type: 'bar',
            height: 32,
            sparkline: { enabled: true },
            toolbar: { show: false },
            animations: { enabled: false },
        },
        plotOptions: {
            bar: {
                horizontal: false,
                distributed: true,
                columnWidth: '70%',
                borderRadius: 1,
                borderRadiusApplication: 'around',
            },
        },
        dataLabels: { enabled: false },
        stroke: { show: false },
        xaxis: {
            labels: { show: false },
            axisBorder: { show: false },
            axisTicks: { show: false },
        },
        yaxis: { show: false },
        grid: {
            show: false,
            padding: { top: 0, right: 0, bottom: 0, left: 0 },
        },
        tooltip: { enabled: false },
        legend: { show: false },
    }));
    const series = [
        {
            name: 'Progress',
            data: Array(totalBars).fill(100),
        },
    ];

    mountChart(el, (isDark) => {
        dark = isDark;
        const opts = options();
        return { ...opts, series: series, chart: { ...opts.chart, type: 'bar', height: 32 } };
    });
};
