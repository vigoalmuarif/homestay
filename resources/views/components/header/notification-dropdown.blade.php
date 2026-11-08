@php
    $notifications = [
        ['id' => '1', 'name' => 'Terry Franci', 'avatar' => '/assets/images/user/user-02.jpg', 'timeKey' => 'minAgo', 'timeCount' => 5, 'status' => 'online'],
        ['id' => '2', 'name' => 'Alena Franci', 'avatar' => '/assets/images/user/user-03.jpg', 'timeKey' => 'minAgo', 'timeCount' => 8, 'status' => 'online'],
        ['id' => '3', 'name' => 'Jocelyn Kenter', 'avatar' => '/assets/images/user/user-04.jpg', 'timeKey' => 'minAgo', 'timeCount' => 15, 'status' => 'online'],
        ['id' => '4', 'name' => 'Brandon Philips', 'avatar' => '/assets/images/user/user-05.jpg', 'timeKey' => 'hrAgo', 'timeCount' => 1, 'status' => 'offline'],
        ['id' => '5', 'name' => 'Terry Franci', 'avatar' => '/assets/images/user/user-02.jpg', 'timeKey' => 'minAgo', 'timeCount' => 5, 'status' => 'online'],
        ['id' => '6', 'name' => 'Alena Franci', 'avatar' => '/assets/images/user/user-03.jpg', 'timeKey' => 'minAgo', 'timeCount' => 8, 'status' => 'online'],
        ['id' => '7', 'name' => 'Jocelyn Kenter', 'avatar' => '/assets/images/user/user-04.jpg', 'timeKey' => 'minAgo', 'timeCount' => 15, 'status' => 'online'],
        ['id' => '8', 'name' => 'Brandon Philips', 'avatar' => '/assets/images/user/user-05.jpg', 'timeKey' => 'hrAgo', 'timeCount' => 1, 'status' => 'offline'],
    ];
    $initials = fn (string $name) => strtoupper(collect(explode(' ', $name))->map(fn ($n) => mb_substr($n, 0, 1))->take(2)->implode(''));
@endphp

<div class="contents" x-data="{ notifying: true }">
    <x-ui.dropdown-menu>
        <x-ui.dropdown-menu.trigger as-child>
            <x-ui.button variant="outline" size="icon-lg" class="relative" aria-label="Toggle notifications" x-on:click.capture="notifying = false">
                <span x-bind:class="notifying ? 'flex' : 'hidden'" class="absolute end-0 -top-0.5 z-10 h-2 w-2 translate-x-1/2 rounded-full bg-orange-400 rtl:-translate-x-1/2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-orange-400 opacity-75"></span>
                </span>
                <x-svg.bell class="size-auto!" />
            </x-ui.button>
        </x-ui.dropdown-menu.trigger>

        <x-ui.dropdown-menu.content align="end" :side-offset="12" class="w-87.5 overflow-hidden p-3 sm:w-90.25 dark:bg-[#1E2635]">
            <div class="flex items-center justify-between pb-3">
                <h5 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ __('header.notifications.title') }}</h5>
                <x-ui.button variant="ghost" size="icon-xs" aria-label="Close notifications" x-on:click="close()">
                    <x-svg.x class="size-5" />
                </x-ui.button>
            </div>

            <x-ui.dropdown-menu.separator class="my-0" />

            <div class="h-96 overflow-y-auto overflow-x-hidden custom-scrollbar">
                <div class="flex flex-col pe-2">
                    @foreach ($notifications as $item)
                        <x-ui.dropdown-menu.item class="flex items-start gap-3 rounded-lg p-3 hover:bg-gray-100 dark:hover:bg-white/5">
                            <x-ui.avatar size="default">
                                <x-ui.avatar.image :src="$item['avatar']" :alt="$item['name']" />
                                <x-ui.avatar.fallback>{{ $initials($item['name']) }}</x-ui.avatar.fallback>
                                <x-ui.avatar.badge :variant="$item['status']" />
                            </x-ui.avatar>
                            <span class="block flex-1 text-start">
                                <span class="mb-1.5 block space-x-1 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="font-medium text-gray-800 dark:text-white/90">{{ $item['name'] }}</span>
                                    <span>{{ __('header.notifications.requestsPermission') }}</span>
                                    <span class="font-medium text-gray-800 dark:text-white/90">Project - Nganter App</span>
                                </span>
                                <span class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ __('header.notifications.project') }}</span>
                                    <x-svg.dot class="size-2 text-gray-400 dark:text-gray-500" />
                                    <span>{{ str_replace('{count}', $item['timeCount'], __('header.notifications.' . $item['timeKey'])) }}</span>
                                </span>
                            </span>
                        </x-ui.dropdown-menu.item>
                        <x-ui.dropdown-menu.separator class="my-0.5 bg-gray-100 last-of-type:hidden dark:bg-gray-700" />
                    @endforeach
                </div>
            </div>

            <x-ui.button as="a" href="/" variant="outline" size="default" class="mt-3 w-full" x-on:click="close()">
                {{ __('header.notifications.viewAll') }}
            </x-ui.button>
        </x-ui.dropdown-menu.content>
    </x-ui.dropdown-menu>
</div>
