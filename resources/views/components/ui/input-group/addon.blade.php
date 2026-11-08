@use('App\Support\TwMerge')
@props(['align' => 'inline-start'])
@php
    $aligns = [
        'inline-start' => 'order-first ps-3.5 has-[>button]:-ms-1 has-[>kbd]:-ms-0.5',
        'inline-end' => 'order-last pe-3.5 has-[>button]:-me-1 has-[>kbd]:-me-0.5',
        'block-start' => 'order-first w-full justify-start ps-4 pe-4 pt-2.5 group-has-[>input]/input-group:pt-2.5 [.border-b]:pb-2',
        'block-end' => 'order-last w-full justify-start ps-4 pe-4 pb-2.5 group-has-[>input]/input-group:pb-2.5 [.border-t]:pt-2',
    ];
    $classes = TwMerge::merge("flex h-auto cursor-text items-center justify-center gap-2 py-1.5 text-sm font-medium text-gray-500 select-none group-data-[disabled=true]/input-group:opacity-50 dark:text-gray-400 [&>kbd]:rounded-md [&>kbd]:border [&>kbd]:border-gray-200 [&>kbd]:bg-gray-50 dark:[&>kbd]:border-gray-700 dark:[&>kbd]:bg-gray-800 [&>svg:not([class*='size-'])]:size-4", $aligns[$align] ?? $aligns['inline-start'], $attributes->get('class'));
@endphp
<div role="group" data-slot="input-group-addon" data-align="{{ $align }}" {{ $attributes->except('class') }}
    x-on:click="if (! $event.target.closest('button')) $el.parentElement.querySelector('input, textarea')?.focus()"
    class="{{ $classes }}">{{ $slot }}</div>
