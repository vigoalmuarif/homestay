<header
    class="sticky top-0 z-50 flex w-full shrink-0 border-gray-200 bg-white xl:border-b dark:border-gray-800 dark:bg-gray-900"
    x-data="{
        isApplicationMenuOpen: false,
        toggleApplicationMenu() {
            this.isApplicationMenuOpen = !this.isApplicationMenuOpen;
        }
    }">
    <div class="flex grow flex-col items-center justify-between xl:flex-row xl:px-6">
        <!-- Left side content -->
        <div
            class="flex w-full items-center justify-between gap-2 border-b border-gray-200 px-3 py-3 sm:gap-4 xl:justify-normal xl:border-b-0 xl:px-0 xl:py-4 dark:border-gray-800">

            <!-- Desktop sidebar toggle (xl and up) -->
            <x-ui.button variant="outline" size="icon" class="hidden text-gray-500 xl:inline-flex lg:size-11 dark:text-gray-400"
                aria-label="{{ __('header.toggleSidebar') }}" @click.stop="$store.sidebar.toggleExpanded()">
                <x-svg.menu-two class="size-auto! rtl:-scale-x-100" />
            </x-ui.button>

            <!-- Mobile sidebar toggle (below xl) -->
            <x-ui.button variant="outline" size="icon" class="text-gray-500 xl:hidden lg:size-11 dark:text-gray-400"
                aria-label="{{ __('header.toggleSidebar') }}" @click.stop="$store.sidebar.toggleMobileOpen()">
                <x-svg.x x-show="$store.sidebar.isMobileOpen" class="size-6" />
                <x-svg.menu-two x-show="!$store.sidebar.isMobileOpen" class="size-auto! rtl:-scale-x-100" />
            </x-ui.button>

            <!-- Logo (mobile only) -->
            <a href="/" class="xl:hidden">
                <img width="154" height="32" class="dark:hidden" src="/assets/images/logo/logo.svg" alt="Logo" />
                <img width="154" height="32" class="hidden dark:block" src="/assets/images/logo/logo-dark.svg" alt="Logo" />
            </a>

            <!-- Application menu toggle (mobile only) -->
            <x-ui.button variant="ghost" size="icon" aria-label="Toggle application menu"
                class="text-gray-700 xl:hidden dark:text-gray-400" @click="toggleApplicationMenu()">
                <x-svg.horizontal-dots />
            </x-ui.button>

            <!-- Search (desktop only) -->
            <div class="hidden xl:block" x-data="{}"
                @keydown.window="if (($event.metaKey || $event.ctrlKey) && $event.key.toLowerCase() === 'k') { $event.preventDefault(); $refs.searchInput?.focus(); }">
                <form @submit.prevent>
                    <x-ui.field class="xl:w-107.5">
                        <x-ui.input-group class="border-gray-200 bg-transparent dark:border-gray-800 dark:bg-white/3">
                            <x-ui.input-group.addon align="inline-start">
                                <x-svg.search class="size-5 text-gray-500 dark:text-gray-400" />
                            </x-ui.input-group.addon>
                            <x-ui.input-group.input x-ref="searchInput" type="text" placeholder="{{ __('header.searchPlaceholder') }}"
                                class="text-gray-800 placeholder:text-gray-400 dark:text-white/90 dark:placeholder:text-white/30" />
                            <x-ui.input-group.addon align="inline-end">
                                <x-ui.badge variant="light" color="gray" size="sm"
                                    class="cursor-pointer gap-0.5 rounded-lg border border-gray-200 bg-gray-50 px-1.75 py-[4.5px] text-xs tracking-[-0.2px] text-gray-500 shadow-none dark:border-gray-800 dark:bg-white/3 dark:text-gray-400">
                                    <span>⌘</span>
                                    <span>K</span>
                                </x-ui.badge>
                            </x-ui.input-group.addon>
                        </x-ui.input-group>
                    </x-ui.field>
                </form>
            </div>
        </div>

        <!-- Right side content -->
        <div :class="isApplicationMenuOpen ? 'flex' : 'hidden'"
            class="w-full items-center justify-between gap-4 px-5 py-4 shadow-theme-md xl:flex xl:justify-end xl:px-0 xl:shadow-none">
            <div class="2xsm:gap-3 flex items-center gap-2">
                <!-- Theme toggler -->
                <x-ui.button variant="outline" size="icon-lg" class="relative" aria-label="Toggle theme" @click="$store.theme.toggle()">
                    <x-svg.sun x-show="$store.theme.resolvedTheme === 'dark'" />
                    <x-svg.moon x-show="$store.theme.resolvedTheme !== 'dark'" />
                </x-ui.button>

                <x-header.notification-dropdown />
            </div>
            <x-header.user-dropdown />
        </div>
    </div>
</header>
