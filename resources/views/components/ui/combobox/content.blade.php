@use('App\Support\TwMerge')
@props(['side' => 'bottom', 'sideOffset' => 8, 'align' => 'start', 'alignOffset' => 0])
@php
    $classes = TwMerge::merge('group/combobox-content relative z-99999 flex max-h-(--reka-combobox-content-available-height) w-(--reka-combobox-trigger-width) max-w-(--reka-combobox-content-available-width) min-w-[calc(var(--reka-combobox-trigger-width)+(--spacing(7)))] origin-(--reka-combobox-content-transform-origin) flex-col gap-1 overflow-x-hidden overflow-y-auto rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg duration-100 outline-none data-closed:animate-out data-closed:overflow-hidden data-closed:fade-out-0 data-closed:zoom-out-95 data-open:animate-in data-open:fade-in-0 data-open:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=top]:slide-in-from-bottom-2 dark:border-gray-800 dark:bg-[#1E2635]', $attributes->get('class'));
    $options = ['side' => $side, 'sideOffset' => $sideOffset, 'align' => $align, 'alignOffset' => $alignOffset];
@endphp
<template x-teleport="body">
    <div x-show="open" style="display: none; position: fixed; left: 0; top: 0; min-width: max-content; transform: translate(0, -200%);" x-ref="wrapper" data-popper-content-wrapper>
        <div x-ref="content" role="listbox" tabindex="-1" style="outline: none;" x-bind:id="id" x-bind:data-state="state" x-bind:data-open="state === 'open' ? '' : null" data-state="closed" data-side="{{ $side }}" data-align="{{ $align }}"
            data-slot="combobox-content" data-options="{{ json_encode($options) }}" {{ $attributes->except('class') }} class="{{ $classes }}">
            <div data-slot="combobox-list" class="no-scrollbar flex scroll-py-1 flex-col gap-1 overflow-y-auto overscroll-contain outline-none">{{ $slot }}</div>
        </div>
    </div>
</template>
