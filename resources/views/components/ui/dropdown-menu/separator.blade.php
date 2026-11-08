@use('App\Support\TwMerge')
<div role="separator" aria-orientation="horizontal" data-slot="dropdown-menu-separator" {{ $attributes->except('class') }} class="{{ TwMerge::merge('-mx-3 my-1.5 h-px bg-gray-100 dark:bg-gray-700', $attributes->get('class')) }}"></div>
