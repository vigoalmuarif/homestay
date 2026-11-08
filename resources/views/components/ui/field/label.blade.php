@use('App\Support\TwMerge')
@props(['for' => null])
@php
    $classes = TwMerge::merge(
        'group/field-label peer/field-label flex w-fit gap-2 text-sm leading-snug font-medium text-gray-700 select-none group-data-[disabled=true]/field:opacity-50 dark:text-gray-300 has-data-checked:border-brand-500/30 has-data-checked:bg-brand-500/5 dark:has-data-checked:border-brand-500/20 dark:has-data-checked:bg-brand-500/10',
        $attributes->get('class')
    );
@endphp
<label data-slot="field-label" @if($for) for="{{ $for }}" @endif {{ $attributes->except('class') }} class="{{ $classes }}">{{ $slot }}</label>
