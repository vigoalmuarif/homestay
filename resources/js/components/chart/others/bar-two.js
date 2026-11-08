import { mountChart } from '../helpers';

export default (el) => {
    let dark = false;
    const isDarkMode = { get value() { return dark; } };
    const computed = (fn) => fn;

    const options = computed(() => ({
        colors: ['#7592FF', '#7CD4FD'],
        chart: {
            fontFamily: 'Outfit, sans-serif',
            type: 'bar',
            stacked: true,
            height: 200,
            toolbar: {
                show: false,
            },
            zoom: {
                enabled: false,
            },
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '39%',
                borderRadius: 8,
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
            categories: [
                'Jan',
                'Feb',
                'Mar',
                'Apr',
                'May',
                'Jun',
                'Jun',
                'Jul',
                'Aug',
                'Sep',
                'Oct',
                'Nov',
                'Dec',
            ],
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
        legend: {
            show: false,
        },
        yaxis: {
            min: 0,
            max: 25000,
            tickAmount: 5,
            labels: {
                style: {
                    colors: isDarkMode.value ? '#94A3B8' : '#364153',
                    fontFamily: 'Outfit, sans-serif',
                    fontSize: '12px',
                },
                formatter: (val) => {
                    if (val === 0)
                        return '0';
                    if (val >= 1000)
                        return `${Math.round(val / 1000)}K`;
                    return val.toString();
                },
            },
        },
        grid: {
            borderColor: isDarkMode.value ? '#1E2640' : '#F3F4F6',
            strokeDashArray: 0,
            yaxis: {
                lines: {
                    show: true,
                },
            },
            xaxis: {
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
                show: false,
            },
            y: {
                formatter: (val) => `$${val?.toLocaleString?.() ?? val}`,
            },
        },
    }));
    const series = [
        {
            name: 'Income',
            data: [9700, 6000, 16300, 4400, 7000, 9700, 4400, 9700, 9700, 7000, 9700, 16300, 9700],
        },
        {
            name: 'Expense',
            data: [10300, 6400, 3000, 14900, 4300, 6900, 14900, 4300, 6900, 4300, 6900, 3000, 6900],
        },
    ];

    mountChart(el, (isDark) => {
        dark = isDark;
        const opts = options();
        return { ...opts, series: series, chart: { ...opts.chart, type: 'bar', height: 200 } };
    });
};
