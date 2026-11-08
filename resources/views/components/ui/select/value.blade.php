@use('App\Support\TwMerge')
@props(['placeholder' => ''])
<span data-slot="select-value" {{ $attributes->except('class') }}
    class="{{ TwMerge::merge('flex flex-1 items-center gap-2 truncate text-start text-gray-800 group-data-placeholder/select-trigger:text-gray-400 dark:text-white/90 dark:group-data-placeholder/select-trigger:text-gray-500', $attributes->get('class')) }}"
    x-text="label ?? @js($placeholder)">{{ $placeholder }}</span>
