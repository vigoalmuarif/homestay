@use('App\Support\TwMerge')
@props(['showTrigger' => true, 'showClear' => false, 'placeholder' => ''])
<x-ui.input-group x-ref="anchor" {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    <x-ui.input-group.input type="text" role="combobox" aria-autocomplete="list" x-ref="input" x-model="query" placeholder="{{ $placeholder }}"
        x-bind:aria-expanded="open ? 'true' : 'false'" x-bind:aria-controls="id" x-on:input="onInput()" x-on:keydown="onKeydown($event)" x-on:click="if (!open) show()"
        x-on:change.stop=""
        {{ $attributes->except('class') }} />
    <x-ui.input-group.addon align="inline-end">
        @if ($showTrigger)
            <button type="button" tabindex="-1" data-slot="combobox-trigger" x-bind:data-popup-open="open ? '' : null" x-on:click="toggle()"
                class="group/combobox-trigger flex items-center justify-center text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 [&_svg:not([class*='size-'])]:size-4 group-has-data-[slot=combobox-clear]/input-group:hidden">
                <x-svg.chevron-down class="pointer-events-none size-4 text-gray-500 transition-transform duration-200 group-hover/combobox-trigger:text-gray-700 group-data-popup-open/combobox-trigger:rotate-180 dark:text-gray-400 dark:group-hover/combobox-trigger:text-gray-300" />
            </button>
        @endif
        @if ($showClear)
            <x-ui.button variant="ghost" size="icon-xs" data-slot="combobox-clear" class="text-gray-400 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" @click="clear()">
                <x-svg.x class="pointer-events-none size-4" />
            </x-ui.button>
        @endif
    </x-ui.input-group.addon>
</x-ui.input-group>
