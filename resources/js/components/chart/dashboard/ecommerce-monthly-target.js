import { mountChart, readConfig } from '../helpers';

export default (el) => {
    const { series } = readConfig(el);

    mountChart(el, (dark) => ({
        series,
        colors: ['#9CB9FF'],
        chart: { fontFamily: 'Outfit, sans-serif', type: 'radialBar', height: 330, sparkline: { enabled: true } },
        plotOptions: {
            radialBar: {
                startAngle: -90,
                endAngle: 90,
                hollow: { size: '80%' },
                track: { background: dark ? '#1E2640' : '#E4E7EC', strokeWidth: '100%', margin: 5 },
                dataLabels: {
                    name: { show: false },
                    value: { fontSize: '36px', fontWeight: '600', offsetY: -35, color: dark ? '#FFFFFF' : '#1D2939', formatter: (val) => val + '%' },
                },
            },
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'horizontal',
                shadeIntensity: 0.5,
                gradientToColors: ['#465FFF'],
                inverseColors: false,
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 100],
                colorStops: [
                    { offset: 0, color: '#9CB9FF', opacity: 1 },
                    { offset: 100, color: '#465FFF', opacity: 1 },
                ],
            },
        },
        stroke: { lineCap: 'round' },
        labels: ['Progress'],
    }));
};
