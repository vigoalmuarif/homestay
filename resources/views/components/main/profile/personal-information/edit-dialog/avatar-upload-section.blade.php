<div class="space-y-4">
    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Change Profile Picture</h3>

    <div class="flex items-center gap-4 sm:gap-6">
        <div class="relative">
            <x-ui.avatar class="size-20 shrink-0 sm:size-24">
                <x-ui.avatar.image x-bind:src="previewAvatarUrl" x-bind:alt="profileData.fullName || 'Profile avatar'" />
                <x-ui.avatar.fallback class="bg-cyan-500 text-2xl font-medium text-white dark:bg-cyan-500 dark:text-white">
                    <span x-text="initialLetter"></span>
                </x-ui.avatar.fallback>
            </x-ui.avatar>

            <!-- Overlaid Camera Button -->
            <button
                type="button"
                class="absolute end-0 bottom-0 flex size-8 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
                aria-label="Upload profile picture"
                @click="$refs.avatarInput.click()"
            >
                <x-svg.camera class="size-4.5" />
            </button>

            <input
                id="avatar-file-input"
                x-ref="avatarInput"
                type="file"
                accept="image/png,image/jpeg,image/webp"
                class="hidden"
                @change="const file = $event.target.files && $event.target.files[0]; if (file) avatarFileUrl = URL.createObjectURL(file)"
            />
        </div>

        <div class="flex flex-col gap-1 text-sm text-gray-500 dark:text-gray-400">
            <p class="font-normal">Upload a square image (200×200 px)</p>
            <p class="font-normal">in JPEG or PNG format.</p>
        </div>
    </div>
</div>
