import jsVectorMap from 'jsvectormap';
import 'jsvectormap/dist/jsvectormap.min.css';
import 'jsvectormap/dist/maps/world';
import 'jsvectormap/dist/maps/world.js';

export const initMap = () => {
    // map one
    const mapSelectorOne = document.querySelectorAll('#mapOne');
    if (mapSelectorOne.length) {
        const mapOne = new jsVectorMap({
            selector: "#mapOne",
            map: "world",
            zoomButtons: false,
            regionStyle: {
                initial: {
                    fontFamily: "Outfit",
                    fill: "#D9D9D9",
                },
                hover: {
                    fillOpacity: 1,
                    fill: "#465fff",
                },
            },
            markers: [
                {
                    name: "Egypt",
                    coords: [26.8206, 30.8025],
                },
                {
                    name: "United Kingdom",
                    coords: [55.3781, 3.436],
                },
                {
                    name: "United States",
                    coords: [37.0902, -95.7129],
                },
            ],

            markerStyle: {
                initial: {
                    strokeWidth: 1,
                    fill: "#465fff",
                    fillOpacity: 1,
                    r: 4,
                },
                hover: {
                    fill: "#465fff",
                    fillOpacity: 1,
                },
                selected: {},
                selectedHover: {},
            },

            onRegionTooltipShow: function (event, tooltip, code) {
                tooltip.text(
                    tooltip.text() + (code === "EG" ? " <b>(Hello Russia)</b>" : ""),
                    true // This second parameter enables HTML
                );
            },
        });
    }

    // map two
    const mapSelectorTwo = document.querySelectorAll("#mapTwo");
    if (mapSelectorTwo.length) {
        new jsVectorMap({
            selector: "#mapTwo",
            map: "world",
            zoomButtons: false,
            zoomOnScroll: false,
            regionStyle: {
                initial: {
                    fontFamily: "Outfit",
                    fill: "#9CB9FF",
                    stroke: "transparent",
                    strokeWidth: 1,
                },
                hover: {
                    fillOpacity: 1,
                    fill: "#8098F9",
                },
                selected: {
                    fill: "#465FFF",
                },
                selectedHover: {},
            },
            selectedRegions: ["US"],
            markers: [
                {
                    name: "United States",
                    coords: [37.0902, -95.7129],
                },
                {
                    name: "Brazil",
                    coords: [-14.235, -51.9253],
                },
                {
                    name: "Nigeria",
                    coords: [9.082, 8.6753],
                },
                {
                    name: "India",
                    coords: [20.5937, 78.9629],
                },
                {
                    name: "Australia",
                    coords: [-25.2744, 133.7751],
                },
            ],

            markerStyle: {
                initial: {
                    strokeWidth: 0,
                    fill: "#465FFF",
                    fillOpacity: 1,
                    r: 5,
                },
                hover: {
                    fill: "#3538CD",
                    fillOpacity: 1,
                    r: 7,
                },
                selected: {},
                selectedHover: {},
            },

            onRegionTooltipShow: function (tooltip) { },
        });
    }
};

export default initMap;
