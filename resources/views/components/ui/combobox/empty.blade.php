@use('App\Support\TwMerge')
<div data-slot="combobox-empty" x-show="visibleCount === 0" style="display: none;" {{ $attributes->except('class') }} class="{{ TwMerge::merge('flex w-full justify-center py-3 text-center text-sm text-gray-500 dark:text-gray-400', $attributes->get('class')) }}">{{ $slot }}</div>
