@php $colorOptions = ['Blue', 'Green', 'Red', 'Yellow', 'Purple', 'Orange']; @endphp
<x-ui.field>
    <x-ui.field.label>Multiple Select Options</x-ui.field.label>
    <x-ui.select multiple :default-value="['Blue', 'Green']">
        <x-ui.select.trigger class="h-auto min-h-11 cursor-pointer py-1.5 ps-3 pe-3.5">
            <div x-show="value.length > 0" class="flex flex-wrap items-center gap-2">
                <template x-for="val in value" :key="val">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-gray-100 py-1 ps-2.5 pe-1.5 text-sm font-normal text-gray-800 dark:border-gray-800 dark:bg-white/5 dark:text-white/90">
                        <span x-text="val"></span>
                        <span role="button" tabindex="0" x-bind:aria-label="`Remove ${val}`"
                            class="inline-flex size-3.5 cursor-pointer items-center justify-center rounded-full text-gray-400 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                            @click.stop.prevent="value = value.filter((v) => v !== val); $dispatch('change', { value })"
                            @pointerdown.stop.prevent
                            @keydown.enter.stop.prevent="value = value.filter((v) => v !== val)"
                            @keydown.space.stop.prevent="value = value.filter((v) => v !== val)">
                            <x-svg.x class="size-3" />
                        </span>
                    </span>
                </template>
            </div>
            <span x-show="value.length === 0" style="display: none;" class="flex flex-1 items-center gap-2 truncate text-start text-gray-400 dark:text-gray-500">Select Option</span>
        </x-ui.select.trigger>
        <x-ui.select.content>
            @foreach ($colorOptions as $option)
                <x-ui.select.item value="{{ $option }}">{{ $option }}</x-ui.select.item>
            @endforeach
        </x-ui.select.content>
    </x-ui.select>
</x-ui.field>
