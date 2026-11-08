<x-ui.field>
    <x-ui.field.label>Date Picker Input</x-ui.field.label>
    <div class="relative" x-init="window.flatpickr($el.querySelector('input'), { mode: 'single', dateFormat: 'm/d/Y' })">
        <x-ui.input type="text" placeholder="mm/dd/yyy" class="pe-10" />
        <span class="pointer-events-none absolute end-3.5 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
            <x-svg.calendar class="size-5" />
        </span>
    </div>
</x-ui.field>
