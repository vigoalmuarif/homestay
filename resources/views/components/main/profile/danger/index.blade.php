@props(['title' => 'Danger Zone'])
@php
    $data = include resource_path('views/components/main/profile/danger/data.php');
    $items = $data['defaultDangerActions'];
@endphp

<x-ui.card {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-700 dark:bg-white/3']) }}>
    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">
        {{ $title }}
    </h4>

    <div class="mt-1">
        @foreach ($items as $index => $item)
            <x-main.profile.danger.danger-item
                :title="$item['title']"
                :description="$item['description']"
                :button-text="$item['buttonText']"
                :icon="$item['icon']"
                :icon-class="$item['iconClass']"
                :variant="$item['variant']"
                :has-divider="$index < count($items) - 1"
            />
        @endforeach
    </div>
</x-ui.card>
