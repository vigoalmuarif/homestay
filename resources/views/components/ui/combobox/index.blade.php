{{-- Combobox root — behaviour in resources/js/ui/combobox.js. Compose with input / content / item / empty. Dispatches `change` with { value }. --}}
@props(['defaultValue' => null])
<div data-slot="combobox" data-ui-root x-data="uiCombobox({{ \Illuminate\Support\Js::from(['value' => $defaultValue]) }})" {{ $attributes->merge(['class' => 'contents']) }}>{{ $slot }}</div>
