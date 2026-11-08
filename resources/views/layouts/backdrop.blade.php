<div x-show="$store.sidebar.isMobileOpen" @click="$store.sidebar.setMobileOpen(false)"
    x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:leave="transition-opacity duration-200" x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-100 bg-black/50 supports-backdrop-filter:backdrop-blur-xs xl:hidden dark:bg-black/70" x-cloak></div>

<style>
  [x-cloak] {
    display: none;
  }
</style>
