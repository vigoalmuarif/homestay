@extends('layouts.app')

@section('content')
    <div class="space-y-6">

        <!-- Breadcrumb Navigation -->
        <x-common.page-breadcrumb pageTitle="Edit Reservasi {{ $booking->booking_code }}" />

        <!-- Top Header & Back Action -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Perbarui tanggal menginap, kamar hunian, atau data penyesuaian biaya sewa kamar untuk tamu.
                </p>
            </div>
            <div>
                <x-ui.button as="a" href="{{ route('bookings.show', $booking) }}" variant="outline" size="sm">
                    &larr; Kembali ke Detail Reservasi
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

        <form action="{{ route('bookings.update', $booking) }}" method="POST"
            class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            @csrf
            @method('PUT')

            <!-- Main Form (2 cols) -->
            <div class="space-y-6 lg:col-span-2">

                <!-- 1. Data Tamu Section (Select2 Server-Side > 20 options) -->
                <x-ui.card class="p-6 space-y-4">
                    <h2
                        class="text-base font-bold text-gray-800 dark:text-white/90 border-b border-gray-100 pb-3 dark:border-gray-800">
                        1. Data Tamu / Pemesan
                    </h2>

                    @php
                        $editCustId = old('customer_id', $booking->customer_id);
                        $editCustomerObj =
                            $booking->customer && $booking->customer_id == $editCustId
                                ? $booking->customer
                                : \App\Models\Customer::find($editCustId);
                    @endphp
                    <x-ui.field>
                        <x-ui.field.label for="edit-booking-customer-id">
                            Tamu Pemesan <span class="text-error-500">*</span>
                        </x-ui.field.label>
                        <x-common.select2 id="edit-booking-customer-id" name="customer_id"
                            placeholder="-- Cari Tamu: Ketik Nama, HP/WhatsApp, atau NIK --" :ajax-url="route('customers.search', [], false)"
                            :value="$editCustId" :value-label="$editCustomerObj
                                ? $editCustomerObj->full_name . ' (' . $editCustomerObj->phone_number . ')'
                                : null" required />
                        <p class="mt-1 text-xs text-gray-400">
                            Pencarian live server-side terintegrasi ke seluruh master tamu.
                        </p>
                    </x-ui.field>
                </x-ui.card>

                <!-- 2. Periode & Alokasi Kamar Section -->
                <x-ui.card class="p-6 space-y-4">
                    <h2
                        class="text-base font-bold text-gray-800 dark:text-white/90 border-b border-gray-100 pb-3 dark:border-gray-800">
                        2. Periode &amp; Pilihan Kamar
                    </h2>

                    <!-- Dates & Duration with Flatpickr (Date + Time) -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-ui.field>
                            <x-ui.field.label for="edit-check-in-date">
                                Tanggal &amp; Jam Check-In <span class="text-error-500">*</span>
                            </x-ui.field.label>
                            <div class="relative">
                                <x-ui.input id="edit-check-in-date" type="text" name="check_in_date"
                                    value="{{ old('check_in_date', $booking->check_in_date->format('H:i') !== '00:00' ? $booking->check_in_date->format('Y-m-d H:i') : $booking->check_in_date->format('Y-m-d') . ' 14:00') }}"
                                    placeholder="YYYY-MM-DD 14:00" required class="pe-10" />
                                <span class="pointer-events-none absolute end-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                                    <x-svg.calendar class="size-5" />
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Standar Check-in mulai jam 14:00.</p>
                        </x-ui.field>

                        <x-ui.field>
                            <x-ui.field.label for="edit-check-out-date">
                                Tanggal &amp; Jam Check-Out <span class="text-error-500">*</span>
                            </x-ui.field.label>
                            <div class="relative">
                                <x-ui.input id="edit-check-out-date" type="text" name="check_out_date"
                                    value="{{ old('check_out_date', $booking->check_out_date->format('H:i') !== '00:00' ? $booking->check_out_date->format('Y-m-d H:i') : $booking->check_out_date->format('Y-m-d') . ' 12:00') }}"
                                    placeholder="YYYY-MM-DD 12:00" required class="pe-10" />
                                <span class="pointer-events-none absolute end-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                                    <x-svg.calendar class="size-5" />
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Standar Check-out maksimal jam 12:00.</p>
                        </x-ui.field>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-3 text-xs text-gray-600 dark:bg-gray-800/60 dark:text-gray-300">
                        Durasi Menginap Terhitung: <strong id="edit-nights-badge"
                            class="text-brand-600 dark:text-brand-400">{{ $booking->total_nights }} Malam</strong>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Room Type Filter (< 10 opsi -> ui.select) -->
                        <x-ui.field>
                            <x-ui.field.label for="edit-room-type-trigger">
                                Filter Tipe Kamar
                            </x-ui.field.label>
                            <x-ui.select id="edit-room-type-select" :default-value="old('room_type_id', $booking->room->room_type_id ?? '')"
                                @change="window.onEditRoomTypeFilterChange($event.detail?.value ?? $event.target?.value)">
                                <x-ui.select.trigger id="edit-room-type-trigger">
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

                        <!-- Room Allocation (10-20 opsi -> ui.combobox) -->
                        <x-ui.field>
                            <x-ui.field.label for="edit-room-id">
                                Unit Kamar Fisik <span class="text-error-500">*</span>
                            </x-ui.field.label>
                            <input type="hidden" name="room_id" id="edit-booking-room-id-input"
                                value="{{ old('room_id', $booking->room_id) }}" required>
                            <x-ui.combobox id="edit-room-combobox" :default-value="old('room_id', $booking->room_id)"
                                @change="window.onEditRoomSelected($event.detail?.value ?? $event.target?.value)">
                                <x-ui.combobox.input id="edit-room-id" placeholder="-- Pilih Unit Kamar Tersedia --"
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
                            <div id="edit-room-loading-msg" class="hidden mt-1 text-xs text-brand-500">Memeriksa
                                ketersediaan kamar...</div>
                            <div id="edit-room-availability-msg" class="hidden mt-1 text-xs text-amber-500"></div>
                        </x-ui.field>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-ui.field>
                            <x-ui.field.label for="edit-room-price">
                                Tarif per Malam (Rp) <span class="text-error-500">*</span>
                            </x-ui.field.label>
                            <x-ui.input id="edit-room-price" type="number" step="1000" min="0"
                                name="room_price_snapshot"
                                value="{{ old('room_price_snapshot', $booking->room_price_snapshot) }}" required />
                        </x-ui.field>

                        <!-- Status Selection (< 10 opsi -> ui.select) -->
                        <x-ui.field>
                            <x-ui.field.label for="edit-booking-status-trigger">
                                Status Reservasi <span class="text-error-500">*</span>
                            </x-ui.field.label>
                            <input type="hidden" name="status" id="edit-booking-status-input"
                                value="{{ old('status', $booking->status) }}" required>
                            <x-ui.select :default-value="old('status', $booking->status)"
                                @change="document.getElementById('edit-booking-status-input').value = $event.detail.value">
                                <x-ui.select.trigger id="edit-booking-status-trigger">
                                    <x-ui.select.value placeholder="Pilih Status" />
                                </x-ui.select.trigger>
                                <x-ui.select.content>
                                    <x-ui.select.item value="pending">Menunggu (Pending)</x-ui.select.item>
                                    <x-ui.select.item value="confirmed">Terkonfirmasi (Confirmed)</x-ui.select.item>
                                    <x-ui.select.item value="checked_in">In-House (Check-In)</x-ui.select.item>
                                    <x-ui.select.item value="checked_out">Selesai (Check-Out)</x-ui.select.item>
                                    <x-ui.select.item value="cancelled">Dibatalkan (Cancelled)</x-ui.select.item>
                                </x-ui.select.content>
                            </x-ui.select>
                        </x-ui.field>
                    </div>

                    <x-ui.field>
                        <x-ui.field.label for="edit-booking-notes">
                            Catatan Khusus
                        </x-ui.field.label>
                        <x-ui.textarea id="edit-booking-notes" name="notes" rows="3"
                            placeholder="Permintaan khusus tamu, jam perkiraan kedatangan, dll.">{{ old('notes', $booking->notes) }}</x-ui.textarea>
                    </x-ui.field>
                </x-ui.card>
            </div>

            <!-- Right Sidebar: Billing (1 col) -->
            <div class="space-y-6">
                <x-ui.card class="p-6 space-y-4">
                    <h2
                        class="text-base font-bold text-gray-800 dark:text-white/90 border-b border-gray-100 pb-3 dark:border-gray-800">
                        Ringkasan Biaya
                    </h2>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between text-gray-500 dark:text-gray-400">
                            <span>Durasi Menginap:</span>
                            <span id="edit-summary-nights"
                                class="font-medium text-gray-800 dark:text-white">{{ $booking->total_nights }}
                                Malam</span>
                        </div>
                        <div class="flex justify-between text-gray-500 dark:text-gray-400">
                            <span>Tarif per Malam:</span>
                            <span id="edit-summary-price" class="font-medium text-gray-800 dark:text-white">Rp
                                {{ number_format($booking->room_price_snapshot, 0, ',', '.') }}</span>
                        </div>
                        <div
                            class="border-t border-gray-100 pt-2 dark:border-gray-800 flex justify-between font-bold text-sm text-gray-800 dark:text-white">
                            <span>Total Biaya Kamar:</span>
                            <span id="edit-summary-total" class="text-brand-600 dark:text-brand-400">Rp
                                {{ number_format($booking->total_room_charge, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                        <x-ui.field>
                            <x-ui.field.label for="edit-deposit-amount">
                                Pembayaran (Rp)
                                {{-- Uang Muka / Deposit Diterima (Rp) --}}
                            </x-ui.field.label>
                            <x-ui.input id="edit-deposit-amount" type="number" step="50000" min="0"
                                name="deposit_amount" value="{{ old('deposit_amount', $booking->deposit_amount) }}" />
                        </x-ui.field>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-3">
                        <x-ui.button type="submit" id="handleSubmit"
                            class="w-full justify-center py-3 text-sm font-semibold">
                            Simpan Perubahan
                        </x-ui.button>
                        <x-ui.button as="a" href="{{ route('bookings.show', $booking) }}" variant="outline"
                            class="w-full justify-center">
                            Batal
                        </x-ui.button>
                    </div>
                </x-ui.card>
            </div>

        </form>
    </div>

    <!-- Reactive Cascading Selects, Date Sync & Realtime Pricing for Edit -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const allRooms = @js($rooms);
            const roomTypes = @js($roomTypes);
            const bookingId = {{ $booking->id }};
            var grandTotal = 0;
            var uangMuka = '{{ $booking->total_room_charge }}' || 0;
            $("#handleSubmit").prop('disabled', true)

            const store = window.Alpine ? Alpine.store('booking') : null;
            if (store) {
                store.roomTypeId = '{{ old('room_type_id', $booking->room->room_type_id ?? '') }}';
                store.roomId = '{{ old('room_id', $booking->room_id) }}';
                store.roomPrice = {{ (float) old('room_price_snapshot', $booking->room_price_snapshot) }};
            }

            function formatRupiah(num) {
                return 'Rp ' + (Number(num) || 0).toLocaleString('id-ID');
            }

            function calculateNights() {
                const inEl = document.getElementById('edit-check-in-date');
                const outEl = document.getElementById('edit-check-out-date');
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
                const priceEl = document.getElementById('edit-room-price');
                const price = priceEl ? (parseFloat(priceEl.value) || 0) : 0;
                const total = nights * price;

                const nightsBadge = document.getElementById('edit-nights-badge');
                const summaryNights = document.getElementById('edit-summary-nights');
                const summaryPrice = document.getElementById('edit-summary-price');
                const summaryTotal = document.getElementById('edit-summary-total');

                if (nightsBadge) nightsBadge.textContent = nights + ' Malam';
                if (summaryNights) summaryNights.textContent = nights + ' Malam';
                if (summaryPrice) summaryPrice.textContent = formatRupiah(price);
                if (summaryTotal) summaryTotal.textContent = formatRupiah(total);
                grandTotal = total;
            }

            $("#edit-deposit-amount").keyup(function() {
                var value = $(this).val();

                if (parseInt(value) >= parseInt(grandTotal)) {
                    $("#handleSubmit").prop('disabled', false)
                } else {
                    $("#handleSubmit").prop('disabled', true)
                }
            })

            function fetchAvailableRooms() {
                const inEl = document.getElementById('edit-check-in-date');
                const outEl = document.getElementById('edit-check-out-date');
                const inStr = inEl ? inEl.value : '';
                const outStr = outEl ? outEl.value : '';
                if (!inStr || !outStr) return;

                const dIn = inStr.split(' ')[0];
                const dOut = outStr.split(' ')[0];
                if (!dIn || !dOut || dOut <= dIn) return;

                const loadingMsg = document.getElementById('edit-room-loading-msg');
                const availMsg = document.getElementById('edit-room-availability-msg');
                if (loadingMsg) loadingMsg.classList.remove('hidden');

                const store = window.Alpine ? Alpine.store('booking') : null;
                const typeId = store ? store.roomTypeId : '';
                let url =
                    `/bookings/available-rooms?check_in=${dIn}&check_out=${dOut}&exclude_booking_id=${bookingId}`;
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
                            'edit-booking-room-id-input')?.value;
                        if (currentRoomId && !data.some(r => String(r.id) === String(currentRoomId))) {
                            if (availMsg) {
                                availMsg.textContent =
                                    'Perhatian: Unit kamar terpilih tidak tersedia pada tanggal yang diubah.';
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

            window.onEditRoomTypeFilterChange = function(typeId) {
                const store = window.Alpine ? Alpine.store('booking') : null;
                if (store) store.roomTypeId = typeId || '';

                if (typeId) {
                    const rt = roomTypes.find(t => String(t.id) === String(typeId));
                    if (rt) {
                        const price = parseFloat(rt.base_price) || 0;
                        const priceInput = document.getElementById('edit-room-price');
                        if (priceInput) priceInput.value = price;
                        if (store) store.roomPrice = price;
                    }
                }

                if (store && store.roomId) {
                    const room = allRooms.find(r => String(r.id) === String(store.roomId));
                    if (room && typeId && String(room.room_type_id) !== String(typeId)) {
                        store.roomId = '';
                        const input = document.getElementById('edit-booking-room-id-input');
                        if (input) input.value = '';
                        const cb = document.getElementById('edit-room-combobox');
                        if (cb && window.Alpine) {
                            Alpine.$data(cb).clear();
                        }
                    }
                }

                fetchAvailableRooms();
                updateBillingSummary();
            };

            window.onEditRoomSelected = function(roomId) {
                const store = window.Alpine ? Alpine.store('booking') : null;
                if (store) store.roomId = roomId || '';
                const input = document.getElementById('edit-booking-room-id-input');
                if (input) input.value = roomId || '';

                if (roomId) {
                    const room = allRooms.find(r => String(r.id) === String(roomId));
                    if (room) {
                        if (room.room_type_id && (!store || String(store.roomTypeId) !== String(room
                                .room_type_id))) {
                            if (store) store.roomTypeId = String(room.room_type_id);
                            const selectEl = document.getElementById('edit-room-type-select');
                            if (selectEl && window.Alpine) {
                                Alpine.$data(selectEl).setValue(room.room_type_id);
                            }
                        }

                        const price = room.room_type?.base_price ?? (roomTypes.find(t => String(t.id) ===
                            String(room.room_type_id))?.base_price ?? 0);
                        if (price) {
                            const priceInput = document.getElementById('edit-room-price');
                            if (priceInput) priceInput.value = parseFloat(price) || 0;
                            if (store) store.roomPrice = parseFloat(price) || 0;
                        }
                    }
                }

                updateBillingSummary();
            };

            if (window.flatpickr) {
                window.flatpickr('#edit-check-in-date', {
                    enableTime: true,
                    time_24hr: true,
                    dateFormat: 'Y-m-d H:i',
                    defaultDate: '{{ old('check_in_date', $booking->check_in_date->format('H:i') !== '00:00' ? $booking->check_in_date->format('Y-m-d H:i') : $booking->check_in_date->format('Y-m-d') . ' 14:00') }}',
                    onChange: function(selectedDates, dateStr) {
                        const el = document.getElementById('edit-check-in-date');
                        if (el) el.value = dateStr;
                        fetchAvailableRooms();
                        updateBillingSummary();
                    }
                });

                window.flatpickr('#edit-check-out-date', {
                    enableTime: true,
                    time_24hr: true,
                    dateFormat: 'Y-m-d H:i',
                    defaultDate: '{{ old('check_out_date', $booking->check_out_date->format('H:i') !== '00:00' ? $booking->check_out_date->format('Y-m-d H:i') : $booking->check_out_date->format('Y-m-d') . ' 12:00') }}',
                    onChange: function(selectedDates, dateStr) {
                        const el = document.getElementById('edit-check-out-date');
                        if (el) el.value = dateStr;
                        fetchAvailableRooms();
                        updateBillingSummary();
                    }
                });
            }

            const priceInput = document.getElementById('edit-room-price');
            if (priceInput) {
                priceInput.addEventListener('input', updateBillingSummary);
            }
            const depositInput = document.getElementById('edit-deposit-amount');
            if (depositInput) {
                depositInput.addEventListener('input', updateBillingSummary);
            }

            fetchAvailableRooms();
            updateBillingSummary();
        });
    </script>
@endsection
