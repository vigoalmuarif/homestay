@use('App\Support\AsChild')
@props(['asChild' => false])
@php $bindings = ['data-slot' => 'dialog-close', 'x-on:click' => 'close()'] + $attributes->except('class')->getAttributes(); @endphp
@if ($asChild)
{!! AsChild::apply((string) $slot, $bindings, html_entity_decode((string) $attributes->get('class'), ENT_QUOTES)) !!}
@else
<button type="button" @foreach ($bindings as $name => $value) {{ $name }}="{{ $value }}" @endforeach @if ($attributes->get('class')) class="{{ html_entity_decode((string) $attributes->get('class'), ENT_QUOTES) }}" @endif>{{ $slot }}</button>
@endif
