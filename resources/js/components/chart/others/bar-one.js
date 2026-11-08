import { mountChart } from '../helpers';

export default (el) => {
    let dark = false;
    const isDarkMode = { get value() { return dark; } };
    const computed = (fn) => fn;

    const options = computed(() => ({
        colors: ['#465FFF'],
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
                columnWidth: '38%',
                borderRadius: 8,
                borderRadiusApplication: 'around',
                dataLabels: {
                    position: 'top',
                },
            },
        },
        dataLabels: {
            enabled: true,
            formatter: (val) => `${(val / 100).toFixed(1)}%`,
            offsetY: -20,
            style: {
                fontSize: '12px',
                fontFamily: 'Outfit, sans-serif',
                fontWeight: 400,
                colors: [isDarkMode.value ? '#94A3B8' : '#364153'],
            },
            dropShadow: {
                enabled: false,
            },
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
                'June',
                'July',
                'Aug',
                'Sept',
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
                },
            },
        },
        legend: {
            show: false,
        },
        yaxis: {
            max: 420,
            min: 0,
            tickAmount: 4,
            labels: {
                show: false,
            },
        },
        grid: {
            borderColor: isDarkMode.value ? '#1E2640' : '#F3F4F6',
            strokeDashArray: 0,
            padding: {
                left: -10,
                right: 0,
            },
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
            type: 'gradient',
            gradient: {
                type: 'vertical',
                shadeIntensity: 1,
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 100],
                colorStops: [
                    {
                        offset: 0,
                        color: '#7592FF',
                        opacity: 1,
                    },
                    {
                        offset: 100,
                        color: '#465FFF',
                        opacity: 1,
                    },
                ],
            },
        },
        tooltip: {
            x: {
                show: false,
            },
            y: {
                formatter: (val) => `${(val / 100).toFixed(1)}%`,
            },
        },
    }));
    const series = [
        {
            name: 'Sales',
            data: [230, 310, 270, 390, 210, 170, 260, 270, 150, 80, 180, 120],
        },
    ];

    mountChart(el, (isDark) => {
        dark = isDark;
        const opts = options();
        return { ...opts, series: series, chart: { ...opts.chart, type: 'bar', height: 200 } };
    });
};
