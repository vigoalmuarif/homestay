{{-- Sub menu root: wraps one <x-ui.dropdown-menu.sub-trigger> and its <x-ui.dropdown-menu.sub-content>. --}}
<div data-slot="dropdown-menu-sub" data-ui-root x-data="uiDropdown({ sub: true })" {{ $attributes->merge(['class' => 'contents']) }}>{{ $slot }}</div>
