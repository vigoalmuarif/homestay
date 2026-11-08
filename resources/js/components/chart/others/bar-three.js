import { mountChart } from '../helpers';

export default (el) => {
    let dark = false;
    const isDarkMode = { get value() { return dark; } };
    const computed = (fn) => fn;

    const options = computed(() => ({
        colors: ['#7592FF', '#BDB4FE', '#7CD4FD'],
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
                horizontal: false,
                columnWidth: '39%',
                borderRadius: 2,
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
            max: 200,
            tickAmount: 4,
            labels: {
                style: {
                    colors: isDarkMode.value ? '#94A3B8' : '#364153',
                    fontFamily: 'Outfit, sans-serif',
                    fontSize: '12px',
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
        },
    }));
    const series = [
        {
            name: 'Shipment',
            data: [50, 75, 78, 78, 50, 92, 92, 118, 50, 116, 102, 50],
        },
        {
            name: 'In Transit',
            data: [120, 140, 192, 185, 120, 142, 142, 204, 120, 202, 172, 120],
        },
        {
            name: 'Delivery',
            data: [42, 54, 44, 32, 42, 60, 60, 76, 42, 74, 64, 42],
        },
    ];

    mountChart(el, (isDark) => {
        dark = isDark;
        const opts = options();
        return { ...opts, series: series, chart: { ...opts.chart, type: 'bar', height: 200 } };
    });
};
