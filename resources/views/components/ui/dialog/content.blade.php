@use('App\Support\TwMerge')
@props(['showCloseButton' => true, 'fullScreen' => false, 'closeOnOverlay' => true])
@php
    $classes = TwMerge::merge($fullScreen
        ? 'fixed inset-0 z-101 flex flex-col justify-between w-screen h-dvh max-w-none rounded-none border-none bg-white p-6 sm:p-10 text-sm text-gray-700 shadow-none duration-100 outline-none overflow-y-auto data-closed:animate-out data-closed:fade-out-0 data-open:animate-in data-open:fade-in-0 dark:bg-[#1E2635] dark:text-gray-300'
        : 'fixed start-1/2 top-1/2 z-101 grid w-full max-w-[calc(100%-2rem)] max-h-[calc(100dvh-2rem)] sm:max-h-[calc(100dvh-3.5rem)] overflow-y-auto custom-scrollbar -translate-x-1/2 -translate-y-1/2 gap-4 rounded-2xl border border-gray-200 bg-white p-6 text-sm text-gray-700 shadow-theme-lg duration-100 outline-none data-closed:animate-out data-closed:fade-out-0 data-closed:zoom-out-95 data-open:animate-in data-open:fade-in-0 data-open:zoom-in-95 sm:max-w-lg rtl:translate-x-1/2 dark:border-gray-800 dark:bg-[#1E2635] dark:text-gray-300', $attributes->get('class'));
    $closeClass = 'rounded-full text-gray-400 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white ' . ($fullScreen
        ? 'absolute end-4 top-4 size-11 bg-gray-100 hover:bg-gray-200 sm:end-6 sm:top-6 dark:bg-gray-800 dark:hover:bg-gray-700'
        : 'absolute end-3 top-3');
    $overlay = 'fixed inset-0 isolate z-100 bg-black/50 duration-100 data-closed:animate-out data-closed:fade-out-0 data-open:animate-in data-open:fade-in-0 supports-backdrop-filter:backdrop-blur-xs dark:bg-black/70';
@endphp
<template x-teleport="body">
    <div x-show="open" style="display: none;" data-dialog-portal data-ui-root>
        <div data-slot="dialog-overlay" x-bind:data-state="state" x-bind:data-open="state === 'open' ? '' : null" data-state="closed" @if ($closeOnOverlay) x-on:click="close()" @endif class="{{ $overlay }}" style="pointer-events: auto;"></div>
        <div x-ref="content" role="dialog" tabindex="-1" x-bind:id="id" x-bind:aria-labelledby="titleId" x-bind:aria-describedby="descriptionId" data-slot="dialog-content" x-bind:data-state="state" x-bind:data-open="state === 'open' ? '' : null" data-state="closed" style="pointer-events: auto;" {{ $attributes->except('class') }} class="{{ $classes }}">
            {{ $slot }}
            @if ($showCloseButton)
                <x-ui.dialog.close as-child>
                    <x-ui.button variant="ghost" :size="$fullScreen ? 'icon-lg' : 'icon-sm'" :class="$closeClass">
                        <x-svg.x :class="$fullScreen ? 'size-6' : null" />
                        <span class="sr-only">Close</span>
                    </x-ui.button>
                </x-ui.dialog.close>
            @endif
        </div>
    </div>
</template>
