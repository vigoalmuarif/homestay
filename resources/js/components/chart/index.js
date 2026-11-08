// Chart initialization
// Dashboard charts (resources/views/components/dashboard/*): `<div data-dashboard-chart="<file name in ./dashboard>" data-config='{...}'>`
const dashboardCharts = import.meta.glob("./dashboard/*.js");

// Chart pages (resources/views/components/main/others/charts/*): `<div data-others-chart="<file name in ./others>">`
const othersCharts = import.meta.glob("./others/*.js");

export const initCharts = () => {
    document.querySelectorAll("[data-others-chart]").forEach((el) => {
        const load = othersCharts[`./others/${el.dataset.othersChart}.js`];
        if (load) load().then((module) => module.default(el));
    });
    document.querySelectorAll("[data-dashboard-chart]").forEach((el) => {
        const load = dashboardCharts[`./dashboard/${el.dataset.dashboardChart}.js`];
        if (load) load().then((module) => module.default(el));
    });
};

export default initCharts;
