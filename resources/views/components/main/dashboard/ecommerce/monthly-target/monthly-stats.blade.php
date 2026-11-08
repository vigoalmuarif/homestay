@php
    $stats = [
        ['key' => 'target', 'value' => '$20K', 'trend' => 'down'],
        ['key' => 'revenue', 'value' => '$70K', 'trend' => 'up'],
        ['key' => 'today', 'value' => '$30K', 'trend' => 'up'],
    ];
@endphp
<div class="flex items-center justify-center gap-5 px-6 py-3 sm:gap-8 sm:py-5">
    @foreach ($stats as $index => $stat)
        @if ($index > 0)
            <div class="h-7 w-px bg-gray-200 dark:bg-gray-800"></div>
        @endif
        <div>
            <p class="mb-1 text-center text-xs text-gray-500 sm:text-sm dark:text-gray-400">
                {{ __('ecommerce.monthlyTarget.' . $stat['key']) }}
            </p>
            <p class="flex items-center justify-center gap-1 text-base font-semibold text-gray-800 sm:text-lg dark:text-white/90">
                {{ $stat['value'] }}
                @if ($stat['trend'] === 'up')
                    <x-svg.arrow-up class="text-green-500" />
                @else
                    <x-svg.arrow-down class="text-red-500" />
                @endif
            </p>
        </div>
    @endforeach
</div>
