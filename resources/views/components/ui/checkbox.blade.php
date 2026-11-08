{{--
    Checkbox.
    Props: checked, id, name, value, disabled, model. Dispatches a `change` event with the new state.
--}}
@use('App\Support\TwMerge')
@props(['checked' => false, 'id' => null, 'name' => null, 'value' => 'on', 'disabled' => false, 'model' => null])
@php
    $bound = $model
        ?? $attributes->get(':checked')
        ?? $attributes->get('::checked')
        ?? $attributes->get('x-bind:checked');

    $classes = TwMerge::merge('peer relative flex size-4 shrink-0 cursor-pointer items-center justify-center rounded-md border border-gray-300 bg-white shadow-theme-xs outline-hidden transition-colors group-has-focus-visible/field-label:ring-0 group-has-disabled/field:opacity-50 after:absolute after:-inset-x-3 after:-inset-y-2 hover:border-brand-500 focus-visible:border-brand-500 focus-visible:ring-3 focus-visible:ring-brand-500/20 disabled:cursor-not-allowed disabled:border-gray-200 disabled:bg-gray-100/50 disabled:shadow-none aria-invalid:border-red-500 aria-invalid:ring-3 aria-invalid:ring-red-500/20 data-checked:border-brand-500 data-checked:bg-brand-500 data-checked:text-white data-checked:hover:border-brand-600 data-checked:hover:bg-brand-600 aria-invalid:data-checked:border-red-500 aria-invalid:data-checked:bg-red-500 data-disabled:data-checked:border-gray-200 data-disabled:data-checked:bg-transparent data-disabled:data-checked:text-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:hover:border-brand-400 dark:focus-visible:border-brand-400 dark:focus-visible:ring-brand-500/30 dark:disabled:border-gray-800 dark:disabled:bg-gray-800/50 dark:aria-invalid:border-red-500/50 dark:aria-invalid:ring-red-500/40 dark:data-checked:border-brand-500 dark:data-checked:bg-brand-500 dark:data-checked:hover:border-brand-600 dark:data-checked:hover:bg-brand-600 dark:data-disabled:data-checked:border-gray-800 dark:data-disabled:data-checked:text-gray-600', $attributes->get('class'));
@endphp
@if ($bound)
<button type="button" role="checkbox" data-slot="checkbox" value="{{ $value }}"
    @if ($id) id="{{ $id }}" @endif @if ($name) name="{{ $name }}" @endif @disabled($disabled)
    x-bind:aria-checked="Boolean({{ $bound }}) ? 'true' : 'false'"
    x-bind:data-state="Boolean({{ $bound }}) ? 'checked' : 'unchecked'"
    x-bind:data-checked="Boolean({{ $bound }}) ? '' : null"
    @unless ($disabled)
    x-on:click="$dispatch('change', { checked: !Boolean({{ $bound }}) })"
    @endunless
    {{ $attributes->except(['class', ':checked', '::checked', 'x-bind:checked', 'model']) }} class="{{ $classes }}">
    <span data-state="checked" data-slot="checkbox-indicator"
        x-show="Boolean({{ $bound }})"
        style="display: none;"
        class="grid place-content-center text-current transition-none [&>svg]:size-3">
        <x-svg.check />
    </span>
</button>
@else
<button type="button" role="checkbox" data-slot="checkbox" value="{{ $value }}"
    @if ($id) id="{{ $id }}" @endif @if ($name) name="{{ $name }}" @endif @disabled($disabled)
    aria-checked="{{ $checked ? 'true' : 'false' }}" data-state="{{ $checked ? 'checked' : 'unchecked' }}" @if ($checked) data-checked="" @endif @if ($disabled) data-disabled="" @endif
    x-data="{ checked: @js((bool) $checked) }"
    x-bind:aria-checked="checked ? 'true' : 'false'" x-bind:data-state="checked ? 'checked' : 'unchecked'" x-bind:data-checked="checked ? '' : null"
    x-on:click="checked = !checked; $dispatch('change', { checked })"
    {{ $attributes->except('class') }} class="{{ $classes }}">
    <span data-state="checked" data-slot="checkbox-indicator" x-show="checked" @unless ($checked) style="display: none;" @endunless class="grid place-content-center text-current transition-none [&>svg]:size-3"><x-svg.check /></span>
</button>
@endif