@aware(['defaultValue' => null])
@use('App\Support\TwMerge')
@props(['value', 'id' => null, 'disabled' => false])
@php
    $checked = $defaultValue === $value;
    $classes = TwMerge::merge('group/radio-group-item peer relative flex size-4 shrink-0 cursor-pointer items-center justify-center rounded-full border border-gray-300 bg-white shadow-theme-xs outline-hidden transition-colors group-has-focus-visible/field-label:ring-0 group-has-disabled/field:opacity-50 after:absolute after:-inset-x-3 after:-inset-y-2 hover:border-brand-500 focus-visible:border-brand-500 focus-visible:ring-3 focus-visible:ring-brand-500/20 disabled:cursor-not-allowed disabled:border-gray-200 disabled:bg-gray-100/50 disabled:shadow-none aria-invalid:border-red-500 aria-invalid:ring-3 aria-invalid:ring-red-500/20 data-checked:border-brand-500 data-checked:bg-brand-500 data-checked:hover:border-brand-600 data-checked:hover:bg-brand-600 aria-invalid:data-checked:border-red-500 aria-invalid:data-checked:bg-red-500 data-disabled:data-checked:border-gray-200 data-disabled:data-checked:bg-transparent dark:border-gray-700 dark:bg-gray-900 dark:hover:border-brand-400 dark:focus-visible:border-brand-400 dark:focus-visible:ring-brand-500/30 dark:disabled:border-gray-800 dark:disabled:bg-gray-800/50 dark:aria-invalid:border-red-500/50 dark:aria-invalid:ring-red-500/40 dark:data-checked:border-brand-500 dark:data-checked:bg-brand-500 dark:data-checked:hover:border-brand-600 dark:data-checked:hover:bg-brand-600 dark:data-disabled:data-checked:border-gray-800', $attributes->get('class'));
@endphp
<button type="button" role="radio" data-slot="radio-group-item" value="{{ $value }}" @if ($id) id="{{ $id }}" @endif @disabled($disabled)
    aria-checked="{{ $checked ? 'true' : 'false' }}" data-state="{{ $checked ? 'checked' : 'unchecked' }}" @if ($checked) data-checked="" @endif tabindex="{{ $checked ? 0 : -1 }}"
    x-bind:aria-checked="value === @js($value) ? 'true' : 'false'" x-bind:data-state="value === @js($value) ? 'checked' : 'unchecked'" x-bind:data-checked="value === @js($value) ? '' : null"
    x-bind:tabindex="value === @js($value) ? 0 : -1" x-on:click="value = @js($value)"
    {{ $attributes->except('class') }} class="{{ $classes }}">
    <span data-state="checked" data-slot="radio-group-indicator" x-show="value === @js($value)" @unless ($checked) style="display: none;" @endunless class="flex size-4 items-center justify-center"><span class="size-2 rounded-full bg-white group-data-disabled/radio-group-item:bg-gray-200 dark:group-data-disabled/radio-group-item:bg-gray-700"></span></span>
</button>
