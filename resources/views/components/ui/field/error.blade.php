@use('App\Support\TwMerge')
<div role="alert" data-slot="field-error" {{ $attributes->except('class') }} class="{{ TwMerge::merge('text-sm font-normal text-red-500 dark:text-red-400', $attributes->get('class')) }}">{{ $slot }}</div>
