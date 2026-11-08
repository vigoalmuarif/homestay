import { mountChart } from '../helpers';

export default (el) => {
    let dark = false;
    const isDarkMode = { get value() { return dark; } };
    const computed = (fn) => fn;

    const options = computed(() => ({
        colors: ['#465FFF', isDarkMode.value ? '#344054' : '#E4E7EC'],
        chart: {
            fontFamily: 'Outfit, sans-serif',
            type: 'bar',
            height: 200,
            toolbar: {
                show: false,
            },
        },
        plotOptions: {
            bar: {
                horizontal: true,
                barHeight: '35%',
                borderRadius: 4,
                borderRadiusApplication: 'end',
            },
        },
        dataLabels: {
            enabled: false,
        },
        stroke: {
            show: false,
        },
        xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
            min: 0,
            max: 700,
            tickAmount: 7,
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false,
            },
            labels: {
                style: {
                    colors: isDarkMode.value ? '#94A3B8' : '#364153',
                    fontFamily: 'Outfit, sans-serif',
                    fontSize: '12px',
                },
            },
        },
        yaxis: {
            labels: {
                style: {
                    colors: isDarkMode.value ? '#94A3B8' : '#364153',
                    fontFamily: 'Outfit, sans-serif',
                    fontSize: '12px',
                },
            },
        },
        legend: {
            show: false,
        },
        grid: {
            borderColor: isDarkMode.value ? '#1E2640' : '#F3F4F6',
            strokeDashArray: 0,
            xaxis: {
                lines: {
                    show: true,
                },
            },
            yaxis: {
                lines: {
                    show: false,
                },
            },
        },
        fill: {
            opacity: 1,
        },
        tooltip: {
            x: {
                show: true,
            },
        },
    }));
    const series = [
        {
            name: 'Category A',
            data: [610, 530, 490, 465, 600],
        },
        {
            name: 'Category B',
            data: [540, 560, 480, 510, 540],
        },
    ];

    mountChart(el, (isDark) => {
        dark = isDark;
        const opts = options();
        return { ...opts, series: series, chart: { ...opts.chart, type: 'bar', height: 200 } };
    });
};
