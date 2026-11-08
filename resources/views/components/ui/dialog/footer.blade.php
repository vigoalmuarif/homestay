@use('App\Support\TwMerge')
@props(['showCloseButton' => false])
<div data-slot="dialog-footer" {{ $attributes->except('class') }} class="{{ TwMerge::merge('-mx-6 -mb-6 flex flex-col-reverse gap-3 rounded-b-2xl p-4 sm:flex-row sm:justify-end', $attributes->get('class')) }}">
    {{ $slot }}
    @if ($showCloseButton)
        <x-ui.dialog.close as-child><x-ui.button variant="outline">Close</x-ui.button></x-ui.dialog.close>
    @endif
</div>
