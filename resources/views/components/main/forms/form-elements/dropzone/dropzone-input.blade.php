<div
    class="w-full"
    x-data="{
        files: [],
        previewImage: null,
        isDragActive: false,
        isDragReject: false,
        isFocused: false,
        ACCEPTED_TYPES: ['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'],
        MAX_SIZE: 5 * 1024 * 1024,
        get isDragAccept() { return this.isDragActive && !this.isDragReject; },
        get borderColor() {
            if (this.isDragReject) return 'border-error-500 bg-error-50/60 dark:bg-error-950/20';
            if (this.isDragAccept) return 'border-brand-500 bg-brand-50/60 dark:bg-brand-950/20';
            if (this.isFocused) return 'border-brand-500 ring-2 ring-brand-500/20 bg-gray-50 dark:bg-gray-900';
            return 'border-gray-300 bg-gray-50 hover:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:hover:border-brand-500';
        },
        addFiles(incoming) {
            const accepted = incoming.filter((f) => this.ACCEPTED_TYPES.includes(f.type) && f.size <= this.MAX_SIZE);
            this.files = [
                ...this.files,
                ...accepted.map((file) => ({ file, preview: file.type.startsWith('image/') ? URL.createObjectURL(file) : undefined })),
            ];
        },
        removeFile(fileName) {
            const target = this.files.find((f) => f.file.name === fileName);
            if (target?.preview) URL.revokeObjectURL(target.preview);
            this.files = this.files.filter((f) => f.file.name !== fileName);
        },
        clearAll() {
            this.files.forEach((f) => { if (f.preview) URL.revokeObjectURL(f.preview); });
            this.files = [];
        },
        openPreview(item) {
            this.previewImage = item;
            Alpine.$data(this.$root.querySelector('[data-dlg=preview]')).show();
        },
        openPicker() { this.$refs.input.click(); },
        onInputChange(e) {
            const input = e.target;
            if (input.files) this.addFiles(Array.from(input.files));
            input.value = '';
        },
        onDragEnter(e) {
            this.isDragActive = true;
            const items = Array.from(e.dataTransfer?.items ?? []);
            this.isDragReject = items.length > 0 && items.some((i) => !this.ACCEPTED_TYPES.includes(i.type));
        },
        onDragLeave() { this.isDragActive = false; this.isDragReject = false; },
        onDrop(e) {
            this.isDragActive = false;
            this.isDragReject = false;
            if (e.dataTransfer?.files) this.addFiles(Array.from(e.dataTransfer.files));
        },
        formatFileSize(bytes) {
            if (bytes < 1024) return `${bytes} B`;
            if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
            return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
        },
    }"
>
    <div
        role="button"
        tabindex="0"
        class="relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed p-7 text-center transition-all duration-200 outline-hidden lg:p-10"
        :class="borderColor"
        @click="openPicker()"
        @keydown.enter.prevent="openPicker()"
        @keydown.space.prevent="openPicker()"
        @focus="isFocused = true"
        @blur="isFocused = false"
        @dragenter.prevent="onDragEnter($event)"
        @dragover.prevent
        @dragleave.prevent="onDragLeave()"
        @drop.prevent="onDrop($event)"
    >
        <input x-ref="input" type="file" multiple accept=".png,.jpg,.jpeg,.webp,.svg" class="hidden" tabindex="-1" @click.stop @change="onInputChange($event)" />

        <div class="flex flex-col items-center">
            <!-- Circular Upload Icon -->
            <div class="mb-4 flex size-17 items-center justify-center rounded-full bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <x-svg.upload class="size-7 text-current" />
            </div>

            <!-- Heading -->
            <h4 class="mb-2 text-xl font-semibold text-gray-800 dark:text-white/90" x-text="isDragReject ? 'File type not supported' : isDragAccept ? 'Drop images here' : isDragActive ? 'Drop files here' : 'Drop File Here'">Drop File Here</h4>

            <!-- Subtitle -->
            <p class="mb-4 max-w-72.5 text-sm text-gray-700 dark:text-gray-400" x-text="isDragReject ? 'Only PNG, JPG, WebP, and SVG images up to 5MB are allowed' : 'Drag and drop your PNG, JPG, WebP, SVG images here or browse'">Drag and drop your PNG, JPG, WebP, SVG images here or browse</p>

            <!-- Browse Action -->
            <span class="text-sm font-medium text-brand-500 underline hover:text-brand-600">Browse File</span>
        </div>
    </div>

    <!-- Uploaded Images Preview Gallery -->
    <div x-show="files.length > 0" style="display: none;" class="mt-6 space-y-4">
        <div class="flex items-center justify-between">
            <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300">Uploaded Images (<span x-text="files.length"></span>)</h5>
            <button type="button" class="text-xs font-medium text-error-500 hover:text-error-600 dark:text-error-400" @click="clearAll()">Clear all</button>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
            <template x-for="item in files" :key="`${item.file.name}-${item.file.lastModified}`">
                <div class="group relative overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-xs transition hover:shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                    <!-- Image Preview Container -->
                    <div class="relative aspect-4/3 w-full overflow-hidden bg-gray-100 dark:bg-gray-800">
                        <template x-if="item.preview">
                            <img :src="item.preview" :alt="item.file.name" class="absolute inset-0 size-full object-cover transition-transform duration-300 group-hover:scale-105" />
                        </template>
                        <template x-if="!item.preview">
                            <div class="flex h-full w-full items-center justify-center text-gray-400">
                                <x-svg.upload class="size-8" />
                            </div>
                        </template>

                        <!-- Hover Action Overlay -->
                        <div class="absolute inset-0 flex items-center justify-center gap-2 bg-gray-900/60 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                            <template x-if="item.preview">
                                <button type="button" class="flex size-8 items-center justify-center rounded-full bg-white/90 text-gray-800 backdrop-blur-xs transition hover:bg-white dark:bg-gray-800/90 dark:text-white dark:hover:bg-gray-800" title="Preview image" :aria-label="`Preview ${item.file.name}`" @click="openPreview(item)">
                                    <x-svg.eye class="size-4" />
                                </button>
                            </template>
                            <button type="button" class="flex size-8 items-center justify-center rounded-full bg-white/90 text-error-500 backdrop-blur-xs transition hover:bg-white dark:bg-gray-800/90 dark:text-error-400 dark:hover:bg-gray-800" title="Remove image" :aria-label="`Remove ${item.file.name}`" @click="removeFile(item.file.name)">
                                <x-svg.trash-bin class="size-4" />
                            </button>
                        </div>
                    </div>

                    <!-- File Metadata -->
                    <div class="p-3">
                        <p class="truncate text-xs font-medium text-gray-800 dark:text-white/90" :title="item.file.name" x-text="item.file.name"></p>
                        <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400" x-text="formatFileSize(item.file.size)"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Image Preview Dialog -->
    <x-ui.dialog data-dlg="preview">
        <x-ui.dialog.content class="max-w-2xl p-6 sm:p-8">
            <div x-show="previewImage">
                <x-ui.dialog.header class="mb-4">
                    <x-ui.dialog.title class="text-lg font-semibold text-gray-800 dark:text-white/90">Image Preview</x-ui.dialog.title>
                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="previewImage ? `${previewImage.file.name} • ${formatFileSize(previewImage.file.size)}` : ''"></p>
                </x-ui.dialog.header>

                <div class="relative aspect-video w-full overflow-hidden rounded-xl border border-gray-200 bg-gray-100 dark:border-gray-800 dark:bg-gray-800">
                    <template x-if="previewImage && previewImage.preview">
                        <img :src="previewImage.preview" :alt="previewImage.file.name" class="absolute inset-0 size-full object-contain" />
                    </template>
                </div>
            </div>
        </x-ui.dialog.content>
    </x-ui.dialog>
</div>
