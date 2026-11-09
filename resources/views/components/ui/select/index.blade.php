{{-- Select root — behaviour in resources/js/ui/select.js. Compose with trigger / value / content / item. Dispatches `change` with { value }. --}}
@props(['defaultValue' => null, 'multiple' => false, 'name' => null])
<div data-slot="select" data-ui-root x-data="uiSelect({{ \Illuminate\Support\Js::from(['value' => $defaultValue, 'multiple' => (bool) $multiple]) }})" {{ $attributes->merge(['class' => 'contents']) }}>
    @if($name)
        <input type="hidden" name="{{ $name }}" :value="multiple ? JSON.stringify(value) : value" />
    @endif
    {{ $slot }}
</div>
