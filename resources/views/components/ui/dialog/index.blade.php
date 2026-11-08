{{-- Dialog root — behaviour in resources/js/ui/dialog.js. Compose with trigger / content / header / title / description / footer / close. --}}
<div data-slot="dialog" data-ui-root x-data="uiDialog()" {{ $attributes->merge(['class' => 'contents']) }}>{{ $slot }}</div>
