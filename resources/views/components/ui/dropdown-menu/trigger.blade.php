@use('App\Support\AsChild')
@props(['asChild' => false])
@php
    $bindings = [
        'data-slot' => 'dropdown-menu-trigger',
        'aria-haspopup' => 'menu',
        'aria-expanded' => 'false',
        'data-state' => 'closed',
        'x-ref' => 'trigger',
        'x-bind:id' => 'id + \'-trigger\'',
        'x-bind:aria-expanded' => 'open ? \'true\' : \'false\'',
        'x-bind:data-state' => 'state',
        'x-bind:data-popup-open' => 'open ? \'\' : null',
        'x-on:click' => 'toggle()',
        'x-on:keydown' => 'onTriggerKeydown($event)',
    ] + $attributes->except('class')->getAttributes();
@endphp
@if ($asChild)
{!! AsChild::apply((string) $slot, $bindings, html_entity_decode((string) $attributes->get('class'), ENT_QUOTES)) !!}
@else
<button type="button" @foreach ($bindings as $name => $value) {{ $name }}="{{ $value }}" @endforeach @if ($attributes->get('class')) class="{{ html_entity_decode((string) $attributes->get('class'), ENT_QUOTES) }}" @endif>{{ $slot }}</button>
@endif
