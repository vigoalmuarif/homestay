{{-- Dropdown menu root — behaviour in resources/js/ui/dropdown.js. Compose with trigger / content / item / checkbox-item / label / separator / group / sub. --}}
@props(['defaultOpen' => false])
<div data-slot="dropdown-menu" data-ui-root x-data="uiDropdown({{ \Illuminate\Support\Js::from(['defaultOpen' => (bool) $defaultOpen]) }})" {{ $attributes->merge(['class' => 'contents']) }}>{{ $slot }}</div>
