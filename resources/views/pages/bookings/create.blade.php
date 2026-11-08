@extends('layouts.app')

@section('content')
    <div x-data="{
        // Quick Guest Modal
        newCustomer: {
            full_name: '',
            phone_number: '',
            identity_type: 'ktp',
            identity_number: '',
            email: ''
        },
        customerErrorMsg: '',
    
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
        openQuickCustomer() {
            this.customerErrorMsg = '';
            this.newCustomer = { full_name: '', phone_number: '', identity_type: 'ktp', identity_number: '', email: '' };
            this.showDialog('quickCustomerDialog');
        },
    
        submitQuickCustomer() {
            this.customerErrorMsg = '';
            fetch('{{ route('customers.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.newCustomer)
                })
                .then(res => res.json().then(data => ({ status: res.status, body: data })))
                .then(result => {
                    if (result.status === 201 || result.body.success) {
                        const cust = result.body.data;
                        const $s2 = window.jQuery ? window.jQuery('#select2-customer-id') : null;
                        if ($s2 && $s2.length) {
                            const opt = new Option(cust.full_name + ' (' + cust.phone_number + ')', cust.id, true, true);
                            $s2.append(opt).trigger('change');
                        }
                        this.closeDialog('quickCustomerDialog');
                    } else {
                        this.customerErrorMsg = result.body.message || 'Gagal menyimpan data tamu.';
                    }
                })
                .catch(() => {
                    this.customerErrorMsg = 'Terjadi kesalahan sistem.';
                });
        }
    }" class="space-y-6">

        <x-common.page-breadcrumb pageTitle="Buat Reservasi Baru" />

        <!-- Top Header & Back -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Pilih tamu pemesan, tentukan tanggal menginap, serta alokasikan unit kamar fisik tanpa risiko
                    overbooking.
                </p>
            </div>
            <div>
                <x-ui.button as="a" href="{{ route('bookings.index') }}" variant="outline" size="sm">
                    &larr; Kembali ke Daftar Reservasi
                </x-ui.button>
            </div>
        </div>

        @if ($errors->any())
            <x-ui.alert variant="destructive">
                <x-ui.alert.title>Terjadi kesalahan validasi:</x-ui.alert.title>
                <x-ui.alert.description>
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert.description>
            </x-ui.alert>
        @endif

        <form action="{{ route('bookings.store') }}" method="POST" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            @csrf

            <!-- Left / Main Form (2 Cols) -->
            <div class="space-y-6 lg:col-span-2">

                <!-- 1. Data Tamu Section (Select2 basis server-side > 20 opsi) -->
                <x-ui.card class="p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <h2 class="text-base font-bold text-gray-800 dark:text-white/90">
                            1. Data Tamu / Pemesan
                        </h2>
                        <x-ui.button type="button" variant="outline" size="sm" @click="openQuickCustomer()">
                            <x-svg.plus class="size-4" />
                            <span>Tamu Baru Cepat</span>
                        </x-ui.button>
                    </div>

                    @php
                        $selectedCustId = old('customer_id', $selectedCustomerId ?? '');
                        $selectedCustomerObj = null;
                        if ($selectedCustId) {
                            $selectedCustomerObj =
                                isset($selectedCustomer) &&
                                $selectedCustomer &&
                                $selectedCustomer->id == $selectedCustId
                                    ? $selectedCustomer
                                    : $customers->firstWhere('id', $selectedCustId) ??
                                        \App\Models\Customer::find($selectedCustId);
                        }
                    @endphp
                    <x-ui.field>
                        <x-ui.field.label for="select2-customer-id">
                            Pilih Tamu Terdaftar <span class="text-error-500">*</span>
                        </x-ui.field.label>
                        <x-common.select2 id="select2-customer-id" name="customer_id"
                            placeholder="-- Ketik Nama Tamu, WhatsApp, atau NIK --" :ajax-url="route('customers.search', [], false)" :value="$selectedCustId"
                            :value-label="$selectedCustomerObj
                                ? $selectedCustomerObj->full_name . ' (' . $selectedCustomerObj->phone_number . ')'
                                : null" required />
                        <p class="mt-1 text-xs text-gray-400">
                            Pencarian live server-side terintegrasi. Tamu berstatus Blacklist dilarang membuat reservasi.
                        </p>
                    </x-ui.field>
                </x-ui.card>

                <!-- 2. Periode Menginap & Alokasi Kamar Section -->
                <x-ui.card class="p-6 space-y-4">
                    <h2
                        class="text-base font-bold text-gray-800 dark:text-white/90 border-b border-gray-100 pb-3 dark:border-gray-800">
                        2. Periode &amp; Pilihan Kamar
                    </h2>

                    <!-- Dates & Duration with Flatpickr (Date + Time) -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-ui.field>
                            <x-ui.field.label for="booking-check-in-date">
                                Tanggal &amp; Jam Check-In <span class="text-error-500">*</span>
                            </x-ui.field.label>
                            <div class="relative">
                                <x-ui.input id="booking-check-in-date" type="text" name="check_in_date"
                                    value="{{ old('check_in_date', $defaultCheckIn . ' 14:00') }}"
                                    placeholder="YYYY-MM-DD 14:00" required class="pe-10" />
                                <span class="pointer-events-none absolute end-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                                    <x-svg.calendar class="size-5" />
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Standar Check-in mulai jam 14:00.</p>
                        </x-ui.field>

                        <x-ui.field>
                            <x-ui.field.label for="booking-check-out-date">
                                Tanggal &amp; Jam Check-Out <span class="text-error-500">*</span>
                            </x-ui.field.label>
                            <div class="relative">
                                <x-ui.input id="booking-check-out-date" type="text" name="check_out_date"
                                    value="{{ old('check_out_date', $defaultCheckOut . ' 12:00') }}"
                                    placeholder="YYYY-MM-DD 12:00" required class="pe-10" />
                                <span class="pointer-events-none absolute end-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                                    <x-svg.calendar class="size-5" />
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Standar Check-out maksimal jam 12:00.</p>
                        </x-ui.field>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-3 text-xs text-gray-600 dark:bg-gray-800/60 dark:text-gray-300">
                        Durasi Menginap Terhitung: <strong id="nights-badge" class="text-brand-600 dark:text-brand-400">1
                            Malam</strong>
                    </div>

                    <!-- Room Type Filter (< 10 opsi -> ui.select) & Room Allocation (10-20 opsi -> ui.combobox) -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-ui.field>
                            <x-ui.field.label for="booking-room-type-trigger">
                                Filter Tipe Kamar
                            </x-ui.field.label>
                            <x-ui.select id="booking-room-type-select" :default-value="old('room_type_id', $selectedRoomTypeId ?? '')"
                                @change="window.onRoomTypeFilterChange($event.detail?.value ?? $event.target?.value)">
                                <x-ui.select.trigger id="booking-room-type-trigger">
                                    <x-ui.select.value placeholder="Semua Tipe Kamar" />
                                </x-ui.select.trigger>
                                <x-ui.select.content>
                                    <x-ui.select.item value="">Semua Tipe Kamar</x-ui.select.item>
                                    @foreach ($roomTypes as $type)
                                        <x-ui.select.item value="{{ $type->id }}">
                                            {{ $type->name }} (Rp {{ number_format($type->base_price, 0, ',', '.') }})
                                        </x-ui.select.item>
                                    @endforeach
                                </x-ui.select.content>
                            </x-ui.select>
                        </x-ui.field>

                        <x-ui.field>
                            <x-ui.field.label for="booking-room-id">
                                Alokasi Unit Kamar Fisik <span class="text-error-500">*</span>
                            </x-ui.field.label>
                            <input type="hidden" name="room_id" id="booking-room-id-input"
                                value="{{ old('room_id', $selectedRoomId ?? '') }}" required>
                            <x-ui.combobox id="booking-room-combobox" :default-value="old('room_id', $selectedRoomId ?? '')"
                                @change="window.onRoomSelected($event.detail?.value ?? $event.target?.value)">
                                <x-ui.combobox.input id="booking-room-id" placeholder="-- Pilih Unit Kamar Tersedia --"
                                    show-trigger show-clear />
                                <x-ui.combobox.content
                                    class="z-102 max-h-60 overflow-y-auto custom-scrollbar [&_[data-slot=combobox-list]]:max-h-56 [&_[data-slot=combobox-list]]:overflow-y-auto [&_[data-slot=combobox-list]]:custom-scrollbar">
                                    <x-ui.combobox.empty>Tidak ada kamar tersedia.</x-ui.combobox.empty>
                                    @foreach ($rooms as $r)
                                        <x-ui.combobox.item value="{{ $r->id }}"
                                            data-room-type-id="{{ $r->room_type_id }}"
                                            data-price="{{ $r->roomType?->base_price ?? 0 }}"
                                            x-show="$store.booking.isRoomAvailable('{{ $r->id }}', '{{ $r->room_type_id }}')">
                                            Kamar {{ $r->room_number }} ({{ $r->roomType?->name ?? 'Standard' }}) -
                                            {{ $r->floor }}
                                        </x-ui.combobox.item>
                                    @endforeach
                                </x-ui.combobox.content>
                            </x-ui.combobox>
                            <div id="room-loading-msg" class="hidden mt-1 text-xs text-brand-500">Memeriksa ketersediaan
                                kamar...</div>
                            <div id="room-availability-msg" class="hidden mt-1 text-xs text-amber-500"></div>
                        </x-ui.field>
                    </div>

                    <!-- Room Type Benefit & Inklusi Paket Section -->
                    <div>
                        <!-- Info placeholder saat belum memilih tipe kamar -->
                        <div x-show="!$store.booking.roomTypeId"
                            class="rounded-xl border border-dashed border-gray-200 bg-gray-50/60 p-3.5 text-start dark:border-gray-800 dark:bg-gray-800/30">
                            <div class="flex items-center gap-2.5 text-xs text-gray-500 dark:text-gray-400">
                                <span
                                    class="flex size-6 shrink-0 items-center justify-center rounded-full bg-gray-200/70 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    <x-svg.info class="size-3.5" />
                                </span>
                                <span>Pilih <strong>Tipe Kamar</strong> atau <strong>Unit Kamar</strong> di atas untuk
                                    melihat rincian benefit &amp; fasilitas paket yang diperoleh tamu.</span>
                            </div>
                        </div>

                        @php
                            $catLabels = [
                                'meal' => 'Makanan / Resto',
                                'beverage' => 'Minuman / Minibar',
                                'amenity' => 'Amenitas & Ekstra Bed',
                                'rental' => 'Sewa Kendaraan / Alat',
                                'laundry' => 'Laundry',
                                'any' => 'Semua Kategori (Bebas)',
                            ];
                        @endphp

                        @foreach ($roomTypes as $type)
                            <div x-show="$store.booking.roomTypeId == '{{ $type->id }}'" x-cloak
                                class="rounded-xl border border-brand-200/80 bg-brand-50/30 p-4 transition-all duration-200 dark:border-brand-900/50 dark:bg-brand-950/20">

                                <!-- Header Paket Kamar -->
                                <div
                                    class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border-b border-brand-100 pb-3 dark:border-brand-900/40">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-500 text-white shadow-xs">
                                            <x-svg.box class="size-4" />
                                        </div>
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h4 class="text-sm font-bold text-gray-800 dark:text-white">
                                                    Paket {{ $type->name }}
                                                </h4>
                                                <x-ui.badge variant="light" color="gray" size="sm">
                                                    Maks. {{ $type->capacity }} Tamu
                                                </x-ui.badge>
                                                @if ($type->inclusions->isNotEmpty())
                                                    <x-ui.badge variant="light" color="success" size="sm">
                                                        {{ $type->inclusions->count() }} Benefit Inklusi
                                                    </x-ui.badge>
                                                @else
                                                    <x-ui.badge variant="light" color="gray" size="sm">
                                                        Room Only
                                                    </x-ui.badge>
                                                @endif
                                            </div>
                                            @if ($type->description)
                                                <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                                    {{ $type->description }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-start sm:text-end shrink-0 ps-11 sm:ps-0">
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400">Harga Standar:</div>
                                        <div class="text-sm font-bold text-brand-600 dark:text-brand-400">
                                            Rp {{ number_format($type->base_price, 0, ',', '.') }}
                                            <span class="text-[11px] font-normal text-gray-500 dark:text-gray-400">/
                                                malam</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Benefit Items Section -->
                                <div class="mt-3 space-y-2">
                                    <div
                                        class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Hak Benefit &amp; Fasilitas Inklusi Paket:
                                    </div>

                                    @if ($type->inclusions->isEmpty())
                                        <div
                                            class="flex items-center gap-2.5 rounded-lg border border-gray-200/80 bg-white/90 p-3 text-xs text-gray-600 dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-300">
                                            <x-svg.info-circle class="size-4 shrink-0 text-amber-500" />
                                            <span>
                                                <strong>Room Only:</strong> Tipe kamar ini belum memiliki paket benefit
                                                makanan/layanan khusus bawaan. Semua pesanan room service, sarapan tambahan,
                                                atau laundry akan ditagihkan ke tagihan kamar (folio).
                                            </span>
                                        </div>
                                    @else
                                        <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                                            @foreach ($type->inclusions as $inc)
                                                <div
                                                    class="flex items-start gap-2.5 rounded-lg border border-brand-100/90 bg-white/90 p-3 shadow-xs dark:border-brand-900/40 dark:bg-gray-900/70">
                                                    <span
                                                        class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-success-50 text-success-600 dark:bg-success-950/60 dark:text-success-400">
                                                        <x-svg.check class="size-3.5" />
                                                    </span>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center justify-between gap-1">
                                                            <span
                                                                class="text-xs font-semibold text-gray-800 truncate dark:text-white/90">
                                                                @if ($inc->service)
                                                                    {{ $inc->service->name }}
                                                                @else
                                                                    Semua
                                                                    {{ $catLabels[$inc->category] ?? ucfirst($inc->category) }}
                                                                @endif
                                                            </span>
                                                            @if ((float) $inc->allowance_limit <= 0)
                                                                <x-ui.badge variant="light" color="success"
                                                                    size="sm"
                                                                    class="text-[10px] px-1.5 py-0.5 shrink-0">
                                                                    100% Gratis
                                                                </x-ui.badge>
                                                            @else
                                                                <x-ui.badge variant="light" color="primary"
                                                                    size="sm"
                                                                    class="text-[10px] px-1.5 py-0.5 shrink-0">
                                                                    Plafon Rp
                                                                    {{ number_format($inc->allowance_limit, 0, ',', '.') }}
                                                                </x-ui.badge>
                                                            @endif
                                                        </div>

                                                        <div
                                                            class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px] text-gray-500 dark:text-gray-400">
                                                            <span>Kategori: <strong
                                                                    class="text-gray-700 dark:text-gray-300">{{ $catLabels[$inc->category] ?? ucfirst($inc->category) }}</strong></span>
                                                            <span>&bull;</span>
                                                            <span>
                                                                @if ($inc->session_time === 'breakfast')
                                                                    Sarapan Pagi
                                                                @elseif ($inc->session_time === 'lunch')
                                                                    Makan Siang
                                                                @elseif ($inc->session_time === 'dinner')
                                                                    Makan Malam
                                                                @elseif ($inc->session_time === 'daily')
                                                                    Setiap Hari
                                                                @else
                                                                    {{ $inc->session_time ? ucfirst($inc->session_time) : 'Bebas Sesi' }}
                                                                @endif
                                                            </span>
                                                            @if ($inc->service)
                                                                <span>&bull;</span>
                                                                <span class="text-gray-400">(Normal: Rp
                                                                    {{ number_format($inc->service->price, 0, ',', '.') }})</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Room Price Snapshot & Status (< 10 opsi -> ui.select) -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-ui.field>
                            <x-ui.field.label for="booking-room-price">
                                Tarif Kamar per Malam (Rp) <span class="text-error-500">*</span>
                            </x-ui.field.label>
                            <x-ui.input id="booking-room-price" type="number" step="1000" min="0"
                                name="room_price_snapshot" value="{{ old('room_price_snapshot', 0) }}" required />
                            <p class="mt-1 text-xs text-gray-400">Harga dasar otomatis diambil dari tipe kamar, dapat
                                disesuaikan bila ada diskon.</p>
                        </x-ui.field>

                        <x-ui.field>
                            <x-ui.field.label for="booking-status-trigger">
                                Status Awal Reservasi
                            </x-ui.field.label>
                            <input type="hidden" name="status" id="booking-status-input"
                                value="{{ old('status', 'confirmed') }}">
                            <x-ui.select :default-value="old('status', 'confirmed')"
                                @change="document.getElementById('booking-status-input').value = $event.detail.value">
                                <x-ui.select.trigger id="booking-status-trigger">
                                    <x-ui.select.value placeholder="Pilih Status" />
                                </x-ui.select.trigger>
                                <x-ui.select.content>
                                    <x-ui.select.item value="confirmed">Terkonfirmasi (Siap Huni)</x-ui.select.item>
                                    <x-ui.select.item value="pending">Menunggu Konfirmasi (Pending)</x-ui.select.item>
                                    <x-ui.select.item value="checked_in">Langsung Check-In (In-House)</x-ui.select.item>
                                </x-ui.select.content>
                            </x-ui.select>
                        </x-ui.field>
                    </div>

                    <x-ui.field>
                        <x-ui.field.label for="booking-notes">
                            Catatan Khusus Reservasi
                        </x-ui.field.label>
                        <x-ui.textarea id="booking-notes" name="notes" rows="2"
                            placeholder="Contoh: Tamu meminta ekstra bantal, check-in larut malam jam 21:00...">{{ old('notes') }}</x-ui.textarea>
                    </x-ui.field>
                </x-ui.card>

            </div>

            <!-- Right Sidebar: Billing & Confirmation (1 Col) -->
            <div class="space-y-6">
                <x-ui.card class="p-6 space-y-4">
                    <h2
                        class="text-base font-bold text-gray-800 dark:text-white/90 border-b border-gray-100 pb-3 dark:border-gray-800">
                        Ringkasan Biaya
                    </h2>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between text-gray-500 dark:text-gray-400">
                            <span>Durasi Menginap:</span>
                            <span id="summary-nights" class="font-medium text-gray-800 dark:text-white">1 Malam</span>
                        </div>
                        <div class="flex justify-between text-gray-500 dark:text-gray-400">
                            <span>Tarif per Malam:</span>
                            <span id="summary-price" class="font-medium text-gray-800 dark:text-white">Rp 0</span>
                        </div>
                        <div
                            class="border-t border-gray-100 pt-2 dark:border-gray-800 flex justify-between font-bold text-sm text-gray-800 dark:text-white">
                            <span>Total Biaya Kamar:</span>
                            <span id="summary-total" class="text-brand-600 dark:text-brand-400">Rp 0</span>
                        </div>
                    </div>

                    @foreach ($roomTypes as $type)
                        <div x-show="$store.booking.roomTypeId == '{{ $type->id }}'" x-cloak
                            class="rounded-lg bg-gray-50/80 p-3 text-xs dark:bg-gray-800/50 space-y-1.5 border border-gray-100 dark:border-gray-800">
                            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                                <span>Tipe Paket:</span>
                                <span class="font-semibold text-gray-800 dark:text-white">{{ $type->name }}</span>
                            </div>
                            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                                <span>Inklusi Bawaan:</span>
                                <span
                                    class="font-semibold {{ $type->inclusions->isNotEmpty() ? 'text-success-600 dark:text-success-400' : 'text-gray-500 dark:text-gray-400' }}">
                                    {{ $type->inclusions->isNotEmpty() ? $type->inclusions->count() . ' Benefit Aktif' : 'Room Only' }}
                                </span>
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                        <x-ui.field>
                            <x-ui.field.label for="booking-deposit-amount">
                                Pembayaran (Rp)
                                {{-- Uang Muka / Deposit Diterima (Rp) --}}
                            </x-ui.field.label>
                            <x-ui.input id="booking-deposit-amount" type="number" step="50000" min="0"
                                name="deposit_amount" value="{{ old('deposit_amount', 0) }}" placeholder="0" />
                            <p class="mt-1 text-xs text-gray-400">Deposit kunci atau pembayaran DP yang diterima di awal.
                            </p>
                        </x-ui.field>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-3">
                        <x-ui.button type="submit" id="handleSubmit"
                            class="w-full justify-center py-3 text-sm font-semibold">
                            Konfirmasi &amp; Simpan Reservasi
                        </x-ui.button>
                        <x-ui.button as="a" href="{{ route('bookings.index') }}" variant="outline"
                            class="w-full justify-center">
                            Batal
                        </x-ui.button>
                    </div>
                </x-ui.card>
            </div>
        </form>

        <!-- Quick Customer Modal Dialog -->
        <x-ui.dialog id="quickCustomerDialog" x-ref="quickCustomerDialog">
            <x-ui.dialog.content class="sm:max-w-md">
                <x-ui.dialog.header class="text-start pe-10">
                    <x-ui.dialog.title>
                        Tambah Data Tamu Cepat
                    </x-ui.dialog.title>
                    <x-ui.dialog.description>
                        Simpan tamu baru langsung dari form reservasi tanpa berpindah halaman.
                    </x-ui.dialog.description>
                </x-ui.dialog.header>

                <div x-show="customerErrorMsg" class="mt-2 rounded-lg bg-error-50 p-3 text-xs text-error-700"
                    x-text="customerErrorMsg"></div>

                <div class="mt-3 space-y-3">
                    <x-ui.field>
                        <x-ui.field.label for="quick-cust-name">Nama Lengkap <span
                                class="text-error-500">*</span></x-ui.field.label>
                        <x-ui.input id="quick-cust-name" type="text" x-model="newCustomer.full_name"
                            placeholder="contoh: Nama Tamu" required />
                    </x-ui.field>

                    <x-ui.field>
                        <x-ui.field.label for="quick-cust-phone">Nomor Telepon / WhatsApp <span
                                class="text-error-500">*</span></x-ui.field.label>
                        <x-ui.input id="quick-cust-phone" type="text" x-model="newCustomer.phone_number"
                            placeholder="contoh: 08123456789" required />
                    </x-ui.field>

                    <div class="grid grid-cols-2 gap-3">
                        <x-ui.field>
                            <x-ui.field.label for="quick-cust-id-type">Jenis Identitas</x-ui.field.label>
                            <x-ui.select :default-value="'ktp'" @change="newCustomer.identity_type = $event.detail.value">
                                <x-ui.select.trigger id="quick-cust-id-type">
                                    <x-ui.select.value placeholder="Pilih Jenis Identitas" />
                                </x-ui.select.trigger>
                                <x-ui.select.content>
                                    <x-ui.select.item value="ktp">KTP</x-ui.select.item>
                                    <x-ui.select.item value="paspor">Paspor</x-ui.select.item>
                                    <x-ui.select.item value="sim">SIM</x-ui.select.item>
                                </x-ui.select.content>
                            </x-ui.select>
                        </x-ui.field>

                        <x-ui.field>
                            <x-ui.field.label for="quick-cust-id-number">Nomor NIK / Paspor</x-ui.field.label>
                            <x-ui.input id="quick-cust-id-number" type="text" x-model="newCustomer.identity_number"
                                placeholder="Nomor ID" />
                        </x-ui.field>
                    </div>

                    <x-ui.field>
                        <x-ui.field.label for="quick-cust-email">Email (Opsional)</x-ui.field.label>
                        <x-ui.input id="quick-cust-email" type="email" x-model="newCustomer.email"
                            placeholder="nama@email.com" />
                    </x-ui.field>
                </div>

                <x-ui.dialog.footer class="pt-2">
                    <x-ui.dialog.close as-child>
                        <x-ui.button variant="outline" type="button">Batal</x-ui.button>
                    </x-ui.dialog.close>
                    <x-ui.button type="button" @click="submitQuickCustomer()">
                        Simpan &amp; Pilih Tamu
                    </x-ui.button>
                </x-ui.dialog.footer>
            </x-ui.dialog.content>
        </x-ui.dialog>

    </div>

    <!-- Reactive Cascading Selects, Date Sync & Realtime Pricing -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const allRooms = @js($rooms);
            const roomTypes = @js($roomTypes);
            var grandTotal = 0;
            var uangMuka = 0;

            $("#handleSubmit").prop('disabled', true)

            const store = window.Alpine ? Alpine.store('booking') : null;
            if (store) {
                store.roomTypeId = '{{ old('room_type_id', $selectedRoomTypeId ?? '') }}';
                store.roomId = '{{ old('room_id', $selectedRoomId ?? '') }}';
                store.roomPrice = {{ (float) old('room_price_snapshot', 0) }};
            }

            function formatRupiah(num) {
                return 'Rp ' + (Number(num) || 0).toLocaleString('id-ID');
            }

            function calculateNights() {
                const inEl = document.getElementById('booking-check-in-date');
                const outEl = document.getElementById('booking-check-out-date');
                const inStr = inEl ? inEl.value : '';
                const outStr = outEl ? outEl.value : '';
                if (!inStr || !outStr) return 1;

                const dIn = inStr.split(' ')[0];
                const dOut = outStr.split(' ')[0];
                const start = new Date(dIn);
                const end = new Date(dOut);
                const diffDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
                return diffDays > 0 ? diffDays : 1;
            }

            function updateBillingSummary() {
                const nights = calculateNights();
                const priceEl = document.getElementById('booking-room-price');
                const price = priceEl ? (parseFloat(priceEl.value) || 0) : 0;
                const total = nights * price;

                const nightsBadge = document.getElementById('nights-badge');
                const summaryNights = document.getElementById('summary-nights');
                const summaryPrice = document.getElementById('summary-price');
                const summaryTotal = document.getElementById('summary-total');

                if (nightsBadge) nightsBadge.textContent = nights + ' Malam';
                if (summaryNights) summaryNights.textContent = nights + ' Malam';
                if (summaryPrice) summaryPrice.textContent = formatRupiah(price);
                if (summaryTotal) summaryTotal.textContent = formatRupiah(total);
                grandTotal = total;
            }


            $("#booking-deposit-amount").keyup(function() {
                var value = $(this).val();

                if (parseInt(value) >= parseInt(grandTotal)) {
                    $("#handleSubmit").prop('disabled', false)
                } else {
                    $("#handleSubmit").prop('disabled', true)
                }
            })

            function fetchAvailableRooms() {
                const inEl = document.getElementById('booking-check-in-date');
                const outEl = document.getElementById('booking-check-out-date');
                const inStr = inEl ? inEl.value : '';
                const outStr = outEl ? outEl.value : '';
                if (!inStr || !outStr) return;

                const dIn = inStr.split(' ')[0];
                const dOut = outStr.split(' ')[0];
                if (!dIn || !dOut || dOut <= dIn) return;

                const loadingMsg = document.getElementById('room-loading-msg');
                const availMsg = document.getElementById('room-availability-msg');
                if (loadingMsg) loadingMsg.classList.remove('hidden');

                const store = window.Alpine ? Alpine.store('booking') : null;
                const typeId = store ? store.roomTypeId : '';
                let url = `/bookings/available-rooms?check_in=${dIn}&check_out=${dOut}`;
                if (typeId) url += `&room_type_id=${typeId}`;

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        if (loadingMsg) loadingMsg.classList.add('hidden');
                        if (store) {
                            store.availableRooms = data;
                            store.isRoomsLoaded = true;
                        }

                        const currentRoomId = store ? store.roomId : document.getElementById(
                            'booking-room-id-input')?.value;
                        if (currentRoomId && !data.some(r => String(r.id) === String(currentRoomId))) {
                            if (availMsg) {
                                availMsg.textContent =
                                    'Perhatian: Unit kamar terpilih tidak tersedia pada tanggal ini.';
                                availMsg.classList.remove('hidden');
                            }
                        } else {
                            if (availMsg) availMsg.classList.add('hidden');
                        }
                    })
                    .catch(() => {
                        if (loadingMsg) loadingMsg.classList.add('hidden');
                        if (store) store.isRoomsLoaded = false;
                    });
            }

            window.onRoomTypeFilterChange = function(typeId) {
                const store = window.Alpine ? Alpine.store('booking') : null;
                if (store) store.roomTypeId = typeId || '';

                if (typeId) {
                    const rt = roomTypes.find(t => String(t.id) === String(typeId));
                    if (rt) {
                        const price = parseFloat(rt.base_price) || 0;
                        const priceInput = document.getElementById('booking-room-price');
                        if (priceInput) priceInput.value = price;
                        if (store) store.roomPrice = price;
                    }
                }

                // If currently selected room is not of this type, clear it
                if (store && store.roomId) {
                    const room = allRooms.find(r => String(r.id) === String(store.roomId));
                    if (room && typeId && String(room.room_type_id) !== String(typeId)) {
                        store.roomId = '';
                        const input = document.getElementById('booking-room-id-input');
                        if (input) input.value = '';
                        const cb = document.getElementById('booking-room-combobox');
                        if (cb && window.Alpine) {
                            Alpine.$data(cb).clear();
                        }
                    }
                }

                fetchAvailableRooms();
                updateBillingSummary();
            };

            window.onRoomSelected = function(roomId) {
                const store = window.Alpine ? Alpine.store('booking') : null;
                if (store) store.roomId = roomId || '';
                const input = document.getElementById('booking-room-id-input');
                if (input) input.value = roomId || '';

                if (roomId) {
                    const room = allRooms.find(r => String(r.id) === String(roomId));
                    if (room) {
                        if (room.room_type_id && (!store || String(store.roomTypeId) !== String(room
                                .room_type_id))) {
                            if (store) store.roomTypeId = String(room.room_type_id);
                            const selectEl = document.getElementById('booking-room-type-select');
                            if (selectEl && window.Alpine) {
                                Alpine.$data(selectEl).setValue(room.room_type_id);
                            }
                        }

                        const price = room.room_type?.base_price ?? (roomTypes.find(t => String(t.id) ===
                            String(room.room_type_id))?.base_price ?? 0);
                        if (price) {
                            const priceInput = document.getElementById('booking-room-price');
                            if (priceInput) priceInput.value = parseFloat(price) || 0;
                            if (store) store.roomPrice = parseFloat(price) || 0;
                        }
                    }
                }

                updateBillingSummary();
            };

            // Initialize Flatpickr for Date + Time
            if (window.flatpickr) {
                window.flatpickr('#booking-check-in-date', {
                    enableTime: true,
                    time_24hr: true,
                    dateFormat: 'Y-m-d H:i',
                    defaultDate: '{{ old('check_in_date', $defaultCheckIn . ' 14:00') }}',
                    minDate: 'today',
                    onChange: function(selectedDates, dateStr) {
                        const el = document.getElementById('booking-check-in-date');
                        if (el) el.value = dateStr;
                        fetchAvailableRooms();
                        updateBillingSummary();
                    }
                });

                window.flatpickr('#booking-check-out-date', {
                    enableTime: true,
                    time_24hr: true,
                    dateFormat: 'Y-m-d H:i',
                    defaultDate: '{{ old('check_out_date', $defaultCheckOut . ' 12:00') }}',
                    minDate: 'today',
                    onChange: function(selectedDates, dateStr) {
                        const el = document.getElementById('booking-check-out-date');
                        if (el) el.value = dateStr;
                        fetchAvailableRooms();
                        updateBillingSummary();
                    }
                });
            }

            const priceInput = document.getElementById('booking-room-price');
            if (priceInput) {
                priceInput.addEventListener('input', updateBillingSummary);
            }
            const depositInput = document.getElementById('booking-deposit-amount');
            if (depositInput) {
                depositInput.addEventListener('input', updateBillingSummary);
            }

            // Initial calculation & fetch
            const initRoomId = '{{ old('room_id', $selectedRoomId ?? '') }}';
            const initTypeId = '{{ old('room_type_id', $selectedRoomTypeId ?? '') }}';
            if (initRoomId) {
                window.onRoomSelected(initRoomId);
            } else if (initTypeId) {
                window.onRoomTypeFilterChange(initTypeId);
            } else {
                updateBillingSummary();
            }

            fetchAvailableRooms();
        });
    </script>
@endsection
