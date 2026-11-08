@extends('layouts.app')

@section('content')
    <div x-data="{
        taskFormData: {
            room_id: '{{ old('room_id', '') }}',
            task_type: '{{ old('task_type', 'checkout_clean') }}',
            priority: '{{ old('priority', 'normal') }}',
            user_id: '{{ old('user_id', '') }}',
            notes: '{{ old('notes', '') }}',
            errors: {}
        },
        init() {
            @if ($errors->has('room_id') || $errors->has('task_type') || $errors->has('priority') || $errors->has('notes'))
                this.$nextTick(() => {
                    this.showDialog('taskDialog');
                });
            @endif
        },
        showDialog(id) {
            if (window.showUiDialog) {
                window.showUiDialog(id);
                return;
            }
            const el = document.getElementById(id) || (this.$refs ? this.$refs[id] : null);
            if (el && window.Alpine) {
                Alpine.$data(el).show();
            }
        },
        closeDialog(id) {
            if (window.closeUiDialog) {
                window.closeUiDialog(id);
                return;
            }
            const el = document.getElementById(id) || (this.$refs ? this.$refs[id] : null);
            if (el && window.Alpine) {
                Alpine.$data(el).close();
            }
        },
        validateTaskForm() {
            this.taskFormData.errors = {};
            if (!this.taskFormData.room_id || this.taskFormData.room_id === '') {
                this.taskFormData.errors.room_id = 'Silakan pilih unit kamar terlebih dahulu.';
            }
            if (!this.taskFormData.task_type || this.taskFormData.task_type === '') {
                this.taskFormData.errors.task_type = 'Pilih jenis pekerjaan kebersihan.';
            }
            if (!this.taskFormData.priority || this.taskFormData.priority === '') {
                this.taskFormData.errors.priority = 'Tentukan tingkat prioritas tugas.';
            }
            return Object.keys(this.taskFormData.errors).length === 0;
        },
        handleTaskSubmit(e) {
            if (!this.validateTaskForm()) {
                e.preventDefault();
                return false;
            }
        },
        openCreateTask(roomId = null) {
            this.taskFormData.room_id = roomId || '';
            this.taskFormData.errors = {};
            this.$nextTick(() => {
                const cb = document.getElementById('task-room-combobox');
                if (cb && window.Alpine) {
                    Alpine.$data(cb).setValue(roomId || null);
                }
            });
            this.showDialog('taskDialog');
        },
        roomModalData: {
            id: null,
            room_number: '',
            status: 'available',
            housekeeping_notes: ''
        },
        openRoomModal(room) {
            this.roomModalData = {
                id: room.id,
                room_number: room.room_number,
                status: room.status,
                housekeeping_notes: room.housekeeping_notes || ''
            };
            this.$nextTick(() => {
                const sel = document.getElementById('modal-room-status-select');
                if (sel && window.Alpine) {
                    Alpine.$data(sel).setValue(room.status);
                }
            });
            this.showDialog('roomNoteDialog');
        }
    }" class="space-y-6">

        <!-- Breadcrumb Navigation -->
        <x-common.page-breadcrumb pageTitle="Housekeeping & Manajemen Kamar" />

        <!-- Flash Session Alerts -->
        @if (session('success'))
            <x-ui.alert variant="success" :dismissible="true">
                <span class="font-semibold">{{ session('success') }}</span>
            </x-ui.alert>
        @endif

        @if (session('error') || $errors->any())
            <x-ui.alert variant="error" :dismissible="true">
                <span class="font-semibold">{{ session('error') ?? $errors->first() }}</span>
            </x-ui.alert>
        @endif

        <!-- Header Actions & Overview -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-title-md font-bold text-gray-800 dark:text-white/90">
                    Operasional Housekeeping
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Pantau status kebersihan unit kamar fisik, alur pembersihan pasca check-out, dan penugasan staf.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.dialog id="taskDialog" x-ref="taskDialog">
                    <x-ui.button type="button" @click="openCreateTask()">
                        <x-svg.plus class="size-4" />
                        <span>+ Buat Tugas Kebersihan</span>
                    </x-ui.button>

                    <x-ui.dialog.content class="sm:max-w-lg" :close-on-overlay="false">
                        <x-ui.dialog.header class="text-start pe-10">
                            <x-ui.dialog.title>Tugas Kebersihan / Maintenance Baru</x-ui.dialog.title>
                            <x-ui.dialog.description>
                                Terbitkan instruksi pembersihan atau perbaikan teknis untuk staf housekeeping.
                            </x-ui.dialog.description>
                        </x-ui.dialog.header>

                        <!-- Error Alert Notice in Dialog -->
                        <div x-show="Object.keys(taskFormData.errors).length > 0" x-cloak
                            class="mt-2 rounded-xl border border-error-200 bg-error-50 p-3 text-xs text-error-700 dark:border-error-800 dark:bg-error-900/30 dark:text-error-400">
                            <div class="flex items-center gap-1.5 font-semibold">
                                <x-svg.alert class="size-4 shrink-0 text-error-600 dark:text-error-400" />
                                <span>Harap lengkapi field berikut sebelum menyimpan:</span>
                            </div>
                            <ul class="mt-1 list-disc list-inside space-y-0.5 ps-1 font-medium">
                                <template x-for="(err, key) in taskFormData.errors" :key="key">
                                    <li x-text="err"></li>
                                </template>
                            </ul>
                        </div>

                        @if ($errors->any())
                            <div class="mt-2 rounded-xl border border-error-200 bg-error-50 p-3 text-xs text-error-700 dark:border-error-800 dark:bg-error-900/30 dark:text-error-400">
                                <div class="flex items-center gap-1.5 font-semibold">
                                    <x-svg.alert class="size-4 shrink-0 text-error-600 dark:text-error-400" />
                                    <span>Terdapat kesalahan validasi server:</span>
                                </div>
                                <ul class="mt-1 list-disc list-inside space-y-0.5 ps-1 font-medium">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('housekeeping.tasks.store') }}" method="POST" @submit="handleTaskSubmit($event)" class="space-y-4 py-2">
                            @csrf

                            <!-- Pilih Kamar (Searchable Combobox Component) -->
                            <x-ui.field>
                                <x-ui.field.label for="task-room-id" required>
                                    Pilih Unit Kamar
                                </x-ui.field.label>
                                <input type="hidden" name="room_id" id="task-room-id-input" x-model="taskFormData.room_id" />
                                <x-ui.combobox id="task-room-combobox" @change="taskFormData.room_id = $event.detail?.value ?? $event.target?.value; delete taskFormData.errors.room_id">
                                    <x-ui.combobox.input id="task-room-id" placeholder="-- Cari / Pilih Kamar --" show-trigger show-clear />
                                    <x-ui.combobox.content class="z-102 max-h-64 overflow-y-auto custom-scrollbar [&_[data-slot=combobox-list]]:max-h-56 [&_[data-slot=combobox-list]]:overflow-y-auto [&_[data-slot=combobox-list]]:custom-scrollbar [&_[data-slot=combobox-list]]:pe-1">
                                        <x-ui.combobox.empty>Kamar tidak ditemukan.</x-ui.combobox.empty>
                                        @foreach ($rooms as $r)
                                            <x-ui.combobox.item value="{{ $r->id }}">
                                                Kamar {{ $r->room_number }} ({{ $r->roomType->name }} &bull; {{ $r->floor }}) - [{{ $r->status_badge['label'] }}]
                                            </x-ui.combobox.item>
                                        @endforeach
                                    </x-ui.combobox.content>
                                </x-ui.combobox>
                                <p x-show="taskFormData.errors.room_id" x-text="taskFormData.errors.room_id" x-cloak class="mt-1 text-xs font-medium text-error-500"></p>
                                @error('room_id')
                                    <p class="mt-1 text-xs font-medium text-error-500">{{ $message }}</p>
                                @enderror
                            </x-ui.field>

                            <!-- Jenis Tugas & Prioritas (Select Primitives) -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <x-ui.field>
                                    <x-ui.field.label for="task-type-trigger" required>
                                        Jenis Pekerjaan
                                    </x-ui.field.label>
                                    <input type="hidden" name="task_type" id="task-type-input" x-model="taskFormData.task_type" />
                                    <x-ui.select :default-value="'checkout_clean'" @change="taskFormData.task_type = $event.detail.value; delete taskFormData.errors.task_type">
                                        <x-ui.select.trigger id="task-type-trigger">
                                            <x-ui.select.value placeholder="Pilih Jenis Pekerjaan" />
                                        </x-ui.select.trigger>
                                        <x-ui.select.content class="z-102">
                                            <x-ui.select.item value="checkout_clean">Pembersihan Check-Out</x-ui.select.item>
                                            <x-ui.select.item value="stayover_clean">Pembersihan Harian (Stayover)</x-ui.select.item>
                                            <x-ui.select.item value="deep_clean">Deep Cleaning (Menyeluruh)</x-ui.select.item>
                                            <x-ui.select.item value="inspection">Inspeksi Kesiapan Kamar</x-ui.select.item>
                                            <x-ui.select.item value="maintenance_repair">Perbaikan Teknis / Maintenance</x-ui.select.item>
                                        </x-ui.select.content>
                                    </x-ui.select>
                                    <p x-show="taskFormData.errors.task_type" x-text="taskFormData.errors.task_type" x-cloak class="mt-1 text-xs font-medium text-error-500"></p>
                                    @error('task_type')
                                        <p class="mt-1 text-xs font-medium text-error-500">{{ $message }}</p>
                                    @enderror
                                </x-ui.field>

                                <x-ui.field>
                                    <x-ui.field.label for="task-priority-trigger" required>
                                        Tingkat Prioritas
                                    </x-ui.field.label>
                                    <input type="hidden" name="priority" id="task-priority-input" x-model="taskFormData.priority" />
                                    <x-ui.select :default-value="'normal'" @change="taskFormData.priority = $event.detail.value; delete taskFormData.errors.priority">
                                        <x-ui.select.trigger id="task-priority-trigger">
                                            <x-ui.select.value placeholder="Pilih Prioritas" />
                                        </x-ui.select.trigger>
                                        <x-ui.select.content class="z-102">
                                            <x-ui.select.item value="normal">Normal</x-ui.select.item>
                                            <x-ui.select.item value="high">Tinggi (Ada Tamu Hari Ini)</x-ui.select.item>
                                            <x-ui.select.item value="urgent">Mendesak / Urgent</x-ui.select.item>
                                            <x-ui.select.item value="low">Rendah</x-ui.select.item>
                                        </x-ui.select.content>
                                    </x-ui.select>
                                    <p x-show="taskFormData.errors.priority" x-text="taskFormData.errors.priority" x-cloak class="mt-1 text-xs font-medium text-error-500"></p>
                                    @error('priority')
                                        <p class="mt-1 text-xs font-medium text-error-500">{{ $message }}</p>
                                    @enderror
                                </x-ui.field>
                            </div>

                            <!-- Penugasan Staf Housekeeper (Select Primitive) -->
                            <x-ui.field>
                                <x-ui.field.label for="task-user-trigger">
                                    Tugaskan Kepada Staf (Opsional)
                                </x-ui.field.label>
                                <input type="hidden" name="user_id" id="task-user-input" value="" />
                                <x-ui.select :default-value="''" @change="document.getElementById('task-user-input').value = $event.detail.value">
                                    <x-ui.select.trigger id="task-user-trigger">
                                        <x-ui.select.value placeholder="-- Belum Ditugaskan (Antrean Umum) --" />
                                    </x-ui.select.trigger>
                                    <x-ui.select.content class="z-102">
                                        <x-ui.select.item value="">-- Belum Ditugaskan (Antrean Umum) --</x-ui.select.item>
                                        @foreach ($housekeepers as $staff)
                                            <x-ui.select.item value="{{ $staff->id }}">
                                                {{ $staff->name }} ({{ $staff->email }})
                                            </x-ui.select.item>
                                        @endforeach
                                    </x-ui.select.content>
                                </x-ui.select>
                            </x-ui.field>

                            <!-- Catatan & Instruksi (Textarea Primitive) -->
                            <x-ui.field>
                                <x-ui.field.label for="task-notes">
                                    Instruksi Khusus / Catatan Kerusakan
                                </x-ui.field.label>
                                <x-ui.textarea name="notes" id="task-notes" rows="3"
                                    placeholder="Contoh: Ganti bedcover king, isi ulang sabun mandi, wastafel airnya agak lambat mengalir..." />
                            </x-ui.field>

                            <x-ui.dialog.footer class="pt-2">
                                <x-ui.dialog.close as-child>
                                    <x-ui.button type="button" variant="outline" size="sm">
                                        Batal
                                    </x-ui.button>
                                </x-ui.dialog.close>
                                <x-ui.button type="submit" variant="primary" size="sm">
                                    Simpan &amp; Terbitkan Tugas
                                </x-ui.button>
                            </x-ui.dialog.footer>
                        </form>
                    </x-ui.dialog.content>
                </x-ui.dialog>
            </div>
        </div>

        <!-- 5 Metric KPI Cards -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-5 md:gap-5">
            <!-- 1. Kamar Kotor (Dirty) -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 md:p-5 dark:border-gray-800 dark:bg-white/3 {{ $metrics['dirty'] > 0 ? 'ring-2 ring-amber-400/40 dark:ring-amber-500/30' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="inline-flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </span>
                    <x-ui.badge variant="light" color="warning" size="sm">
                        Perlu Dibersihkan
                    </x-ui.badge>
                </div>
                <div class="mt-3">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Kamar Kotor</span>
                    <h3 class="mt-0.5 text-2xl font-extrabold text-amber-600 dark:text-amber-400">
                        {{ $metrics['dirty'] }} <span class="text-sm font-normal text-gray-500">Unit</span>
                    </h3>
                </div>
            </div>

            <!-- 2. Sedang Dibersihkan (Cleaning) -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 md:p-5 dark:border-gray-800 dark:bg-white/3">
                <div class="flex items-center justify-between">
                    <span class="inline-flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400">
                        <svg class="size-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </span>
                    <x-ui.badge variant="light" color="info" size="sm">
                        In Progress
                    </x-ui.badge>
                </div>
                <div class="mt-3">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Sedang Dibersihkan</span>
                    <h3 class="mt-0.5 text-2xl font-extrabold text-blue-600 dark:text-blue-400">
                        {{ $metrics['cleaning'] }} <span class="text-sm font-normal text-gray-500">Unit</span>
                    </h3>
                </div>
            </div>

            <!-- 3. Bersih & Siap Huni (Available) -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 md:p-5 dark:border-gray-800 dark:bg-white/3">
                <div class="flex items-center justify-between">
                    <span class="inline-flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                        <x-svg.check-circle class="size-5" />
                    </span>
                    <x-ui.badge variant="light" color="success" size="sm">
                        Siap Check-In
                    </x-ui.badge>
                </div>
                <div class="mt-3">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Bersih & Siap Huni</span>
                    <h3 class="mt-0.5 text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">
                        {{ $metrics['available'] }} <span class="text-sm font-normal text-gray-500">Unit</span>
                    </h3>
                </div>
            </div>

            <!-- 4. Kamar Terisi (Occupied) -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 md:p-5 dark:border-gray-800 dark:bg-white/3">
                <div class="flex items-center justify-between">
                    <span class="inline-flex size-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-500/15 dark:text-purple-400">
                        <x-svg.user class="size-5" />
                    </span>
                    <x-ui.badge variant="light" color="primary" size="sm">
                        In-House
                    </x-ui.badge>
                </div>
                <div class="mt-3">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Kamar Terisi Tamu</span>
                    <h3 class="mt-0.5 text-2xl font-extrabold text-purple-600 dark:text-purple-400">
                        {{ $metrics['occupied'] }} <span class="text-sm font-normal text-gray-500">Unit</span>
                    </h3>
                </div>
            </div>

            <!-- 5. Maintenance / Perbaikan -->
            <div class="col-span-2 sm:col-span-1 rounded-2xl border border-gray-200 bg-white p-4 md:p-5 dark:border-gray-800 dark:bg-white/3">
                <div class="flex items-center justify-between">
                    <span class="inline-flex size-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
                        <x-svg.gear class="size-5" />
                    </span>
                    <x-ui.badge variant="light" color="error" size="sm">
                        Perbaikan
                    </x-ui.badge>
                </div>
                <div class="mt-3">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Perbaikan / OOO</span>
                    <h3 class="mt-0.5 text-2xl font-extrabold text-rose-600 dark:text-rose-400">
                        {{ $metrics['maintenance'] }} <span class="text-sm font-normal text-gray-500">Unit</span>
                    </h3>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
            <form method="GET" action="{{ route('housekeeping.index') }}" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Floor Filter -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Lantai:</span>
                        <x-ui.select :default-value="$selectedFloor ?? 'all'" @change="$refs.filterFloorInput.value = $event.detail.value; $refs.filterFloorInput.form.submit()">
                            <input type="hidden" name="floor" x-ref="filterFloorInput" value="{{ $selectedFloor ?? 'all' }}" />
                            <x-ui.select.trigger id="housekeeping-filter-floor" size="sm" class="w-40">
                                <x-ui.select.value placeholder="Semua Lantai" />
                            </x-ui.select.trigger>
                            <x-ui.select.content>
                                <x-ui.select.item value="all">Semua Lantai</x-ui.select.item>
                                @foreach ($floors as $fl)
                                    <x-ui.select.item value="{{ $fl }}">{{ $fl }}</x-ui.select.item>
                                @endforeach
                            </x-ui.select.content>
                        </x-ui.select>
                    </div>

                    <!-- Status Filter -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Status:</span>
                        <x-ui.select :default-value="$selectedStatus ?? 'all'" @change="$refs.filterStatusInput.value = $event.detail.value; $refs.filterStatusInput.form.submit()">
                            <input type="hidden" name="status" x-ref="filterStatusInput" value="{{ $selectedStatus ?? 'all' }}" />
                            <x-ui.select.trigger id="housekeeping-filter-status" size="sm" class="w-52">
                                <x-ui.select.value placeholder="Semua Status" />
                            </x-ui.select.trigger>
                            <x-ui.select.content>
                                <x-ui.select.item value="all">Semua Status</x-ui.select.item>
                                <x-ui.select.item value="dirty">Kotor (Dirty)</x-ui.select.item>
                                <x-ui.select.item value="cleaning">Sedang Dibersihkan (Cleaning)</x-ui.select.item>
                                <x-ui.select.item value="available">Bersih & Siap Huni (Available)</x-ui.select.item>
                                <x-ui.select.item value="occupied">Terisi (Occupied)</x-ui.select.item>
                                <x-ui.select.item value="maintenance">Perbaikan (Maintenance)</x-ui.select.item>
                            </x-ui.select.content>
                        </x-ui.select>
                    </div>
                </div>

                <!-- Search Input -->
                <div class="flex items-center gap-2">
                    <div class="relative w-full sm:w-64">
                        <x-ui.input type="text" name="search" value="{{ $search }}"
                            placeholder="Cari nomor / tipe kamar..."
                            class="ps-9 pe-3" size="sm" />
                        <x-svg.search class="absolute start-2.5 top-1/2 size-4 -translate-y-1/2 text-gray-400 pointer-events-none" />
                    </div>
                    <x-ui.button type="submit" variant="outline" size="sm">
                        Filter
                    </x-ui.button>
                    @if ($selectedFloor || $selectedStatus || $search)
                        <x-ui.button as="a" href="{{ route('housekeeping.index') }}" variant="ghost" size="sm" class="text-gray-500">
                            Reset
                        </x-ui.button>
                    @endif
                </div>
            </form>
        </div>

        <!-- Room Cards Grid -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-800 dark:text-white/90">
                    Papan Status Kamar Fisik ({{ $rooms->count() }} Unit)
                </h2>
                <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-2.5 rounded-full bg-amber-500"></span> Kotor
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-2.5 rounded-full bg-blue-500"></span> Dibersihkan
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-2.5 rounded-full bg-emerald-500"></span> Siap Huni
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-2.5 rounded-full bg-purple-500"></span> Terisi
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-2.5 rounded-full bg-rose-500"></span> Maintenance
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse ($rooms as $room)
                    @php
                        $todayBooking = $room->bookings->first();
                        $borderAccent = match($room->status) {
                            'dirty' => 'border-amber-300 dark:border-amber-700/60 bg-amber-50/20 dark:bg-amber-950/10',
                            'cleaning' => 'border-blue-300 dark:border-blue-700/60 bg-blue-50/20 dark:bg-blue-950/10',
                            'available' => 'border-emerald-200 dark:border-emerald-800/40 bg-white dark:bg-white/3',
                            'occupied' => 'border-purple-200 dark:border-purple-800/40 bg-white dark:bg-white/3',
                            'maintenance' => 'border-rose-300 dark:border-rose-800/60 bg-rose-50/20 dark:bg-rose-950/10',
                            default => 'border-gray-200 dark:border-gray-800 bg-white dark:bg-white/3'
                        };
                    @endphp

                    <div class="relative flex flex-col justify-between rounded-2xl border p-4 shadow-theme-xs transition hover:shadow-md {{ $borderAccent }}">
                        <!-- High Priority Check-in Today Banner -->
                        @if ($todayBooking)
                            <div class="mb-3 flex items-center justify-between rounded-lg bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 dark:bg-rose-500/15 dark:text-rose-300">
                                <span class="flex items-center gap-1">
                                    <span class="relative flex size-2">
                                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-400 opacity-75"></span>
                                        <span class="relative inline-flex size-2 rounded-full bg-rose-600"></span>
                                    </span>
                                    Check-In Hari Ini!
                                </span>
                                <span class="text-[11px] font-normal truncate max-w-[120px]" title="{{ $todayBooking->customer->full_name }}">
                                    {{ $todayBooking->customer->full_name }}
                                </span>
                            </div>
                        @endif

                        <div>
                            <!-- Header: Room Number, Floor & Status Badge -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-2xl font-black text-gray-900 dark:text-white">
                                        {{ $room->room_number }}
                                    </span>
                                    <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[11px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $room->floor ?? 'Lantai 1' }}
                                    </span>
                                </div>
                                <x-ui.badge variant="light" :color="$room->status_badge['color']" size="default">
                                    {{ $room->status_badge['label'] }}
                                </x-ui.badge>
                            </div>

                            <!-- Room Type Name -->
                            <div class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                                {{ $room->roomType->name }}
                            </div>

                            <!-- Active Housekeeping Task / Staf -->
                            @if ($room->activeHousekeepingTask)
                                <div class="mt-3 rounded-lg border border-gray-100 bg-white/70 p-2.5 text-xs dark:border-gray-800 dark:bg-white/5">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold text-gray-700 dark:text-gray-300">
                                            {{ $room->activeHousekeepingTask->task_type_label }}
                                        </span>
                                        <x-ui.badge variant="light" :color="$room->activeHousekeepingTask->priority_badge['color']" size="sm">
                                            {{ $room->activeHousekeepingTask->priority_badge['label'] }}
                                        </x-ui.badge>
                                    </div>
                                    <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                        Petugas: <span class="font-medium text-gray-700 dark:text-gray-200">{{ $room->activeHousekeepingTask->assignedUser?->name ?? 'Belum ada staf' }}</span>
                                    </div>
                                </div>
                            @endif

                            <!-- Housekeeping Notes / Cleaning History -->
                            <div class="mt-3 space-y-1 text-[11px] text-gray-500 dark:text-gray-400">
                                @if ($room->last_cleaned_at)
                                    <div class="flex items-center gap-1">
                                        <x-svg.check class="size-3 text-emerald-500" />
                                        <span>Dibersihkan: {{ $room->last_cleaned_at->diffForHumans() }}</span>
                                        @if ($room->lastCleanedByUser)
                                            <span>({{ $room->lastCleanedByUser->name }})</span>
                                        @endif
                                    </div>
                                @endif

                                @if ($room->housekeeping_notes)
                                    <div class="rounded bg-amber-50/80 p-2 text-amber-800 dark:bg-amber-950/40 dark:text-amber-200">
                                        <span class="font-semibold">Catatan:</span> {{ $room->housekeeping_notes }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Card Action Buttons -->
                        <div class="mt-4 border-t border-gray-200/60 pt-3 dark:border-gray-800">
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- If Dirty: Button "Mulai Bersihkan" & "Tandai Bersih" -->
                                @if ($room->status === 'dirty')
                                    <form method="POST" action="{{ route('housekeeping.rooms.status', $room) }}" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="status" value="cleaning" />
                                        <x-ui.button type="submit" variant="primary" size="sm" class="w-full justify-center bg-blue-600 hover:bg-blue-700">
                                            Mulai Bersihkan
                                        </x-ui.button>
                                    </form>
                                    <form method="POST" action="{{ route('housekeeping.rooms.status', $room) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="available" />
                                        <x-ui.button type="submit" variant="outline" size="sm" class="text-emerald-600 border-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/20" title="Langsung Tandai Siap Huni">
                                            &check; Siap
                                        </x-ui.button>
                                    </form>

                                <!-- If Cleaning: Button "Selesai (Siap Huni)" -->
                                @elseif ($room->status === 'cleaning')
                                    <form method="POST" action="{{ route('housekeeping.rooms.status', $room) }}" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="status" value="available" />
                                        <x-ui.button type="submit" variant="primary" size="sm" class="w-full justify-center bg-emerald-600 hover:bg-emerald-700">
                                            &check; Selesai (Siap Huni)
                                        </x-ui.button>
                                    </form>

                                <!-- If Maintenance: Button "Selesai Perbaikan" -->
                                @elseif ($room->status === 'maintenance')
                                    <form method="POST" action="{{ route('housekeeping.rooms.status', $room) }}" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="status" value="available" />
                                        <x-ui.button type="submit" variant="outline" size="sm" class="w-full justify-center text-emerald-600 border-emerald-300 hover:bg-emerald-50">
                                            &check; Selesai Perbaikan
                                        </x-ui.button>
                                    </form>

                                <!-- If Available: Button Set Dirty or Maintenance -->
                                @elseif ($room->status === 'available')
                                    <form method="POST" action="{{ route('housekeeping.rooms.status', $room) }}" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="status" value="dirty" />
                                        <x-ui.button type="submit" variant="ghost" size="sm" class="w-full justify-center text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/20">
                                            Tandai Kotor
                                        </x-ui.button>
                                    </form>
                                @endif

                                <!-- Open Note / Quick Edit Dialog -->
                                <x-ui.button type="button" variant="ghost" size="icon-sm"
                                    :data-room="json_encode($room)"
                                    @click="openRoomModal(JSON.parse($el.dataset.room))"
                                    title="Catatan & Detail Housekeeping">
                                    <x-svg.pencil class="size-4 text-gray-500" />
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                        Tidak ada unit kamar yang sesuai dengan filter pencarian.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Housekeeping Tasks & Work Orders Section -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-white/3">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-800 dark:text-white/90">
                        Daftar Penugasan & Work Order Kebersihan
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Pantau antrean tugas kebersihan staf dan riwayat pekerjaan yang telah selesai.
                    </p>
                </div>

                <!-- Task Filter Tabs -->
                <div class="flex items-center gap-2">
                    <x-ui.button as="a" href="{{ request()->fullUrlWithQuery(['task_filter' => 'active']) }}"
                        :variant="$taskFilter === 'active' ? 'primary' : 'outline'" size="sm">
                        Tugas Aktif ({{ $metrics['active_tasks'] }})
                    </x-ui.button>
                    <x-ui.button as="a" href="{{ request()->fullUrlWithQuery(['task_filter' => 'completed']) }}"
                        :variant="$taskFilter === 'completed' ? 'primary' : 'outline'" size="sm">
                        Riwayat Selesai
                    </x-ui.button>
                </div>
            </div>

            <!-- Task Table -->
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-start text-xs text-gray-700 dark:text-gray-300">
                    <thead class="border-b border-gray-200 bg-gray-50 text-[11px] font-semibold uppercase text-gray-500 dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-400">
                        <tr>
                            <th class="py-3 px-3">Kamar</th>
                            <th class="py-3 px-3">Jenis Tugas</th>
                            <th class="py-3 px-3">Prioritas</th>
                            <th class="py-3 px-3">Petugas Staf</th>
                            <th class="py-3 px-3">Instruksi / Catatan</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3 text-end">Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($tasks as $task)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/2">
                                <td class="py-3 px-3 font-bold text-gray-900 dark:text-white">
                                    Kamar {{ $task->room->room_number }}
                                    <span class="block text-[11px] font-normal text-gray-500">
                                        {{ $task->room->roomType->name }} &bull; {{ $task->room->floor }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-medium text-gray-800 dark:text-white/90">
                                        {{ $task->task_type_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <x-ui.badge variant="light" :color="$task->priority_badge['color']" size="sm">
                                        {{ $task->priority_badge['label'] }}
                                    </x-ui.badge>
                                </td>
                                <td class="py-3 px-3">
                                    @if ($task->assignedUser)
                                        <div class="flex items-center gap-1.5 font-medium text-gray-800 dark:text-gray-200">
                                            <span class="inline-flex size-6 items-center justify-center rounded-full bg-brand-50 text-[10px] font-bold text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">
                                                {{ strtoupper(substr($task->assignedUser->name, 0, 1)) }}
                                            </span>
                                            <span>{{ $task->assignedUser->name }}</span>
                                        </div>
                                    @else
                                        <span class="italic text-gray-400">Belum Ditugaskan</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 max-w-xs truncate" title="{{ $task->notes }}">
                                    {{ $task->notes ?: '-' }}
                                </td>
                                <td class="py-3 px-3">
                                    <x-ui.badge variant="light" :color="$task->status_badge['color']" size="sm">
                                        {{ $task->status_badge['label'] }}
                                    </x-ui.badge>
                                </td>
                                <td class="py-3 px-3 text-end">
                                    <div class="inline-flex items-center gap-1.5">
                                        @if ($task->status === 'pending')
                                            <form method="POST" action="{{ route('housekeeping.tasks.update-status', $task) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="in_progress" />
                                                <x-ui.button type="submit" variant="outline" size="sm" class="text-blue-600 border-blue-200 hover:bg-blue-50">
                                                    Mulai
                                                </x-ui.button>
                                            </form>
                                        @elseif ($task->status === 'in_progress')
                                            <form method="POST" action="{{ route('housekeeping.tasks.update-status', $task) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="completed" />
                                                <x-ui.button type="submit" variant="primary" size="sm" class="bg-emerald-600 hover:bg-emerald-700">
                                                    &check; Selesai
                                                </x-ui.button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('housekeeping.tasks.destroy', $task) }}"
                                            onsubmit="return confirm('Hapus tugas kebersihan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" variant="ghost" size="icon-sm" class="text-gray-400 hover:text-rose-600">
                                                <x-svg.trash-bin class="size-3.5" />
                                            </x-ui.button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Tidak ada tugas kebersihan dalam antrean saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Tasks Pagination -->
            @if ($tasks->hasPages())
                <div class="mt-4 border-t border-gray-100 pt-3 dark:border-gray-800">
                    {{ $tasks->links() }}
                </div>
            @endif
        </div>

        <!-- Quick Modal for Room Note & Housekeeping Status Switcher -->
        <x-ui.dialog id="roomNoteDialog" x-ref="roomNoteDialog">
            <x-ui.dialog.content class="sm:max-w-md" :close-on-overlay="false">
                <x-ui.dialog.header class="text-start pe-10">
                    <x-ui.dialog.title>
                        Update Kamar <span x-text="roomModalData.room_number"></span>
                    </x-ui.dialog.title>
                    <x-ui.dialog.description>
                        Perbarui status kebersihan dan catatan operasional housekeeping.
                    </x-ui.dialog.description>
                </x-ui.dialog.header>

                <form :action="'/housekeeping/rooms/' + roomModalData.id + '/status'" method="POST" class="space-y-4 py-2">
                    @csrf

                    <!-- Status Selector (< 10 opsi -> x-ui.select) -->
                    <x-ui.field>
                        <x-ui.field.label for="modal-room-status-trigger" required>
                            Status Kamar
                        </x-ui.field.label>
                        <input type="hidden" name="status" id="modal-room-status-input" x-model="roomModalData.status" required />
                        <x-ui.select id="modal-room-status-select" :default-value="'available'" @change="roomModalData.status = $event.detail.value; document.getElementById('modal-room-status-input').value = $event.detail.value">
                            <x-ui.select.trigger id="modal-room-status-trigger">
                                <x-ui.select.value placeholder="Pilih Status" />
                            </x-ui.select.trigger>
                            <x-ui.select.content class="z-102">
                                <x-ui.select.item value="dirty">Kotor (Perlu Dibersihkan)</x-ui.select.item>
                                <x-ui.select.item value="cleaning">Sedang Dibersihkan (In Progress)</x-ui.select.item>
                                <x-ui.select.item value="available">Bersih & Siap Huni (Available)</x-ui.select.item>
                                <x-ui.select.item value="occupied">Terisi Tamu (Occupied)</x-ui.select.item>
                                <x-ui.select.item value="maintenance">Dalam Perbaikan (Maintenance)</x-ui.select.item>
                            </x-ui.select.content>
                        </x-ui.select>
                    </x-ui.field>

                    <!-- Catatan Housekeeping -->
                    <x-ui.field>
                        <x-ui.field.label for="modal_notes">
                            Catatan Kebersihan / Maintenance
                        </x-ui.field.label>
                        <x-ui.textarea name="housekeeping_notes" id="modal_notes" rows="3"
                            x-model="roomModalData.housekeeping_notes"
                            placeholder="Tulis catatan seperti: ganti sprei, handuk tambahan, keluhan AC, dsb..." />
                    </x-ui.field>

                    <x-ui.dialog.footer class="pt-2">
                        <x-ui.dialog.close as-child>
                            <x-ui.button type="button" variant="outline" size="sm">
                                Batal
                            </x-ui.button>
                        </x-ui.dialog.close>
                        <x-ui.button type="submit" variant="primary" size="sm">
                            Simpan Perubahan
                        </x-ui.button>
                    </x-ui.dialog.footer>
                </form>
            </x-ui.dialog.content>
        </x-ui.dialog>

    </div>
@endsection
