<div class="relative z-1 flex min-h-screen flex-col items-center justify-center overflow-hidden p-6">
    <x-common.common-grid-shape />
    <div class="mx-auto w-full max-w-60.5 text-center sm:max-w-118">
        <h1 class="mb-8 font-bold text-gray-800 dark:text-white/90">ERROR</h1>

        <img src="/assets/images/error/404.svg" alt="404" class="dark:hidden" width="472" height="152" />
        <img src="/assets/images/error/404-dark.svg" alt="404" class="hidden dark:block" width="472" height="152" />

        <p class="mt-10 mb-6 text-base text-gray-700 sm:text-lg dark:text-gray-400">We can’t seem to find the page you are looking for!</p>

        <x-ui.button size="lg" variant="outline" class="h-12.5 bg-white" as-child>
            <a href="/">Back to Home Page</a>
        </x-ui.button>
    </div>

    <p class="absolute start-1/2 bottom-6 -translate-x-1/2 text-center text-sm text-gray-500 rtl:translate-x-1/2 dark:text-gray-400">&copy; {{ date('Y') }} - TailAdmin</p>
</div>
