{{--
    Button.
    Props: variant (default|outline|ghost|destructive|link), size (default|xs|sm|lg|icon|icon-xs|icon-sm|icon-lg),
    as (button|a|…), type, asChild (apply the button styles to the first element of the slot instead of rendering a wrapper).
    Extra attributes and classes are merged (later classes win).
--}}
@use('App\Support\AsChild')
@use('App\Support\Ui\ButtonVariants')
@props([
    'variant' => 'default',
    'size' => 'default',
    'as' => 'button',
    'type' => 'button',
    'asChild' => false,
])
@php
    $classes = ButtonVariants::classes($variant, $size, $attributes->get('class'));
@endphp
@if ($asChild)
{!! AsChild::apply((string) $slot, ['data-slot' => 'button'] + $attributes->except('class')->getAttributes(), $classes) !!}
@elseif ($as === 'button')
<button data-slot="button" type="{{ $type }}" {{ $attributes->except('class') }} class="{{ $classes }}">{{ $slot }}</button>
@else
<{{ $as }} data-slot="button" {{ $attributes->except('class') }} class="{{ $classes }}">{{ $slot }}</{{ $as }}>
@endif
