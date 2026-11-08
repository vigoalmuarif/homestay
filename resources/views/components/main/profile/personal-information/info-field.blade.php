@props(['label', 'value'])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-1 sm:gap-2']) }}>
    <p class="text-xs font-normal text-gray-500 sm:text-sm dark:text-gray-400">
        {{ $label }}
    </p>
    <p class="text-sm font-medium text-gray-800 sm:text-base dark:text-white/90" x-text="{{ $value }} || '—'"></p>
</div>
