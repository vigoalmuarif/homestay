@php
    $countries = include resource_path('views/components/main/dashboard/ecommerce/demographic-card/data.php');
    $mapMarkers = collect($countries)->map(fn ($country) => [
        'latLng' => $country['latLng'],
        'name' => $country['name'],
        'style' => ['fill' => '#465FFF', 'borderWidth' => 1, 'borderColor' => 'white'],
    ])->all();
@endphp
<div class="h-full rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-white/3">
    <x-main.dashboard.ecommerce.demographic-card.card-header />

    <div class="my-6 overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 px-4 py-6 sm:px-6 dark:border-gray-800 dark:bg-gray-900">
        <div class="mapOne map-btn 2xsm:w-76.75 xsm:w-89.5 -mx-4 -my-6 h-53 w-63 sm:-mx-6 md:w-167 lg:w-158.5 xl:w-98.25 2xl:w-138.5">
            <x-main.dashboard.ecommerce.demographic-card.country-map :markers="$mapMarkers" />
        </div>
    </div>

    <x-main.dashboard.ecommerce.demographic-card.country-list :countries="$countries" />
</div>
