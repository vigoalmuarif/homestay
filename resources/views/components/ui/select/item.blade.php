@use('App\Support\TwMerge')
@props(['value', 'disabled' => false])
@php
    $classes = TwMerge::merge("group/select-item relative flex w-full cursor-pointer items-center gap-3 rounded-lg py-2.5 ps-3 pe-8 text-sm font-medium text-gray-700 outline-hidden transition-colors duration-200 select-none hover:bg-gray-100 hover:text-gray-700 focus:bg-gray-100 focus:text-gray-700 data-disabled:pointer-events-none data-disabled:opacity-50 data-highlighted:bg-gray-100 data-highlighted:text-gray-700 data-inset:ps-8 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-gray-300 dark:focus:bg-white/5 dark:focus:text-gray-300 dark:data-highlighted:bg-white/5 dark:data-highlighted:text-gray-300 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg]:text-gray-500 group-hover/select-item:[&_svg]:text-gray-700 data-highlighted:[&_svg]:text-gray-700 dark:[&_svg]:text-gray-400 dark:group-hover/select-item:[&_svg]:text-gray-300 dark:data-highlighted:[&_svg]:text-gray-300 [&_svg:not([class*=size-])]:size-5", $attributes->get('class'));
@endphp
<div role="option" tabindex="-1" data-slot="select-item" data-value="{{ $value }}" @if ($disabled) data-disabled="" @endif
    x-init="register(@js($value), $el.querySelector('[data-slot=select-item-text]').textContent.trim())"
    x-bind:aria-selected="isSelected(@js($value)) ? 'true' : 'false'" x-bind:data-state="isSelected(@js($value)) ? 'checked' : 'unchecked'"
    x-on:pointermove="highlight($el)" x-on:click="pick(@js($value))"
    {{ $attributes->except('class') }} class="{{ $classes }}">
    <span data-slot="select-item-text" class="flex flex-1 shrink-0 items-center gap-2 whitespace-nowrap">{{ $slot }}</span>
    <span class="pointer-events-none absolute end-3 flex items-center justify-center text-brand-500 dark:text-brand-400" data-slot="select-item-indicator">
        <span x-show="isSelected(@js($value))" style="display: none;"><x-svg.check class="size-4" /></span>
    </span>
</div>
