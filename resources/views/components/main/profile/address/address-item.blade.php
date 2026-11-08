{{-- `value` is an Alpine expression resolved in the address scope. --}}
@props(['label', 'value'])

<div {{ $attributes }}>
    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
        {{ $label }}
    </p>
    <p class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="{{ $value }}"></p>
</div>
