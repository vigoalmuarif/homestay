<x-ui.radio-group default-value="selected" class="flex flex-wrap items-center gap-6 sm:gap-8">
    <x-ui.field orientation="horizontal" class="w-auto gap-2.5">
        <x-ui.radio-group.item id="radio-default" value="default" />
        <x-ui.label for="radio-default" class="cursor-pointer text-sm font-medium text-gray-700 dark:text-gray-300">Default</x-ui.label>
    </x-ui.field>

    <x-ui.field orientation="horizontal" class="w-auto gap-2.5">
        <x-ui.radio-group.item id="radio-selected" value="selected" />
        <x-ui.label for="radio-selected" class="cursor-pointer text-sm font-medium text-gray-700 dark:text-gray-300">Selected</x-ui.label>
    </x-ui.field>

    <x-ui.field orientation="horizontal" class="w-auto gap-2.5">
        <x-ui.radio-group.item id="radio-disabled" value="disabled" disabled />
        <x-ui.label for="radio-disabled" class="cursor-not-allowed text-sm font-medium text-gray-400 dark:text-gray-600">Disabled</x-ui.label>
    </x-ui.field>
</x-ui.radio-group>
