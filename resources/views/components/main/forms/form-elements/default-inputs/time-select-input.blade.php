<x-ui.field>
    <x-ui.field.label>Time Select Input</x-ui.field.label>
    <div class="relative" x-init="window.flatpickr($el.querySelector('input'), { enableTime: true, noCalendar: true, dateFormat: 'h:i K' })">
        <x-ui.input type="text" placeholder="12:00 AM" class="pe-10" />
        <span class="pointer-events-none absolute end-3.5 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
            <x-svg.time class="size-5" />
        </span>
    </div>
</x-ui.field>
