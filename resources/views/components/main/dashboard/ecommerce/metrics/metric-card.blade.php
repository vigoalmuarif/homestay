@props(['title', 'value', 'change' => null, 'direction' => 'up'])
<div {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-white/3']) }}>
    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-white/90 [&>svg]:size-6">
        {{ $icon ?? '' }}
    </div>

    <div class="mt-5 flex items-end justify-between">
        <div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ $title }}
            </span>
            <h4 class="mt-2 text-lg font-bold text-gray-800 dark:text-white/90">
                {{ $value }}
            </h4>
        </div>

        @if (isset($badge) && ! $badge->isEmpty())
            {{ $badge }}
        @elseif ($change)
            <x-ui.badge :color="$direction === 'down' ? 'error' : 'success'">
                @if ($direction === 'down')
                    <x-svg.arrow-down class="text-error-500" />
                @else
                    <x-svg.arrow-up />
                @endif
                {{ $change }}
            </x-ui.badge>
        @endif
    </div>
    {{ $slot }}
</div>
