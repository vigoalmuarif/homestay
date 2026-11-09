@extends('layouts.app')

@section('content')
    <div x-data="{
        checkInBookingId: null,
        guestName: '',
        roomNumber: '',
        depositAmount: 0,
        checkInActionUrl: '',

        checkOutBookingId: null,
        checkOutActionUrl: '',
        isLoadingFolio: false,
        folioData: null,
        allowUnsettled: false,
        paymentMethod: 'cash',
        amountPaid: 0,
        paymentNotes: '',
        unsettledReason: '',
        refundConfirmed: false,

        formatRupiah(num) {
            return new Intl.NumberFormat('id-ID').format(num || 0);
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
        openCheckIn(bookingId, guestName, roomNumber, currentDeposit) {
            this.checkInBookingId = bookingId;
            this.guestName = guestName;
            this.roomNumber = roomNumber;
            this.depositAmount = currentDeposit || 0;
            this.checkInActionUrl = `/bookings/${bookingId}/check-in`;
            this.showDialog('checkInDialog');
        },
        openCheckOut(bookingId, guestName, roomNumber) {
            this.checkOutBookingId = bookingId;
            this.guestName = guestName;
            this.roomNumber = roomNumber;
            this.checkOutActionUrl = `/bookings/${bookingId}/check-out`;
            this.isLoadingFolio = true;
            this.folioData = null;
            this.allowUnsettled = false;
            this.paymentMethod = 'cash';
            this.amountPaid = 0;
            this.paymentNotes = '';
            this.unsettledReason = '';
            this.refundConfirmed = false;
            this.showDialog('checkOutDialog');

            fetch(`/bookings/${bookingId}/folio-summary`)
                .then(res => res.json())
                .then(data => {
                    this.folioData = data;
                    this.amountPaid = data.balance_due || 0;
                    this.isLoadingFolio = false;
                })
                .catch(err => {
                    console.error('Gagal mengambil ringkasan folio:', err);
                    this.isLoadingFolio = false;
                });
        }
    }" class="space-y-6">

        <x-common.page-breadcrumb pageTitle="Reservasi & Front Desk (Tamu Aktif)" />

        <!-- Top Header & Action -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Kelola alur reservasi kamar, penerimaan deposit, proses check-in, dan serah terima kamar tamu homestay.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <x-ui.button as="a" href="{{ route('bookings.create') }}">
                    <x-svg.plus class="size-4" />
                    <span>Reservasi Baru</span>
                </x-ui.button>
            </div>
        </div>

        @if (session('success'))
            <x-ui.alert
                class="border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-ui.alert.title>Sukses</x-ui.alert.title>
                <x-ui.alert.description>{{ session('success') }}</x-ui.alert.description>
            </x-ui.alert>
        @endif

        @if (session('info'))
            <x-ui.alert>
                <x-ui.alert.title>Informasi</x-ui.alert.title>
                <x-ui.alert.description>{{ session('info') }}</x-ui.alert.description>
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert variant="destructive">
                <x-ui.alert.title>Terjadi Kesalahan</x-ui.alert.title>
                <x-ui.alert.description>
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert.description>
            </x-ui.alert>
        @endif

        <!-- 4 Metric Counters -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 md:gap-4">
            <x-ui.card class="p-4">
                <div class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase">Tamu In-House (Menginap)</div>
                <div class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['in_house'] }} Kamar</div>
                <div class="mt-1 text-xs text-gray-400">Kunci sedang dipegang tamu</div>
            </x-ui.card>
            <x-ui.card class="p-4">
                <div class="text-xs font-medium text-brand-600 dark:text-brand-400 uppercase">Kedatangan Hari Ini</div>
                <div class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['arriving_today'] }} Tamu
                </div>
                <div class="mt-1 text-xs text-gray-400">Check-in terjadwal hari ini</div>
            </x-ui.card>
            <x-ui.card class="p-4">
                <div class="text-xs font-medium text-amber-600 dark:text-amber-400 uppercase">Kepulangan Hari Ini</div>
                <div class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['departing_today'] }} Kamar
                </div>
                <div class="mt-1 text-xs text-gray-400">Check-out terjadwal hari ini</div>
            </x-ui.card>
            <x-ui.card class="p-4">
                <div class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase">Reservasi Terkonfirmasi
                </div>
                <div class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['confirmed'] }} Booking
                </div>
                <div class="mt-1 text-xs text-gray-400">Booking siap huni mendatang</div>
            </x-ui.card>
        </div>

        <!-- Filter & Search Toolbar -->
        <x-ui.card class="p-4">
            <form method="GET" action="{{ route('bookings.index') }}"
                class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                    <!-- Search Input -->
                    <div class="relative w-full sm:max-w-xs">
                        <x-ui.input type="text" name="search" value="{{ $search }}"
                            placeholder="Cari kode, nama tamu, no kamar..." class="ps-10" />
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none text-gray-400">
                            <x-svg.search class="size-4" />
                        </div>
                    </div>

                    <!-- Status Filter (< 10 opsi -> x-ui.select) -->
                    <x-ui.select :default-value="$status"
                        @change="$refs.filterBookingStatus.value = $event.detail.value; $refs.filterBookingStatus.form.submit()">
                        <input type="hidden" name="status" x-ref="filterBookingStatus" value="{{ $status }}" />
                        <x-ui.select.trigger id="booking-filter-status" class="w-48">
                            <x-ui.select.value placeholder="Semua Status" />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <x-ui.select.item value="all">Semua Status</x-ui.select.item>
                            <x-ui.select.item value="checked_in">In-House (Check-In)</x-ui.select.item>
                            <x-ui.select.item value="confirmed">Terkonfirmasi (Confirmed)</x-ui.select.item>
                            <x-ui.select.item value="pending">Menunggu (Pending)</x-ui.select.item>
                            <x-ui.select.item value="checked_out">Check-Out (Selesai)</x-ui.select.item>
                            <x-ui.select.item value="cancelled">Dibatalkan</x-ui.select.item>
                        </x-ui.select.content>
                    </x-ui.select>

                    <!-- Date Schedule Filter (< 10 opsi -> x-ui.select) -->
                    <x-ui.select :default-value="$dateFilter"
                        @change="$refs.filterBookingDate.value = $event.detail.value; $refs.filterBookingDate.form.submit()">
                        <input type="hidden" name="date_filter" x-ref="filterBookingDate" value="{{ $dateFilter }}" />
                        <x-ui.select.trigger id="booking-filter-date" class="w-56">
                            <x-ui.select.value placeholder="Semua Jadwal Tanggal" />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <x-ui.select.item value="all">Semua Jadwal Tanggal</x-ui.select.item>
                            <x-ui.select.item value="today_arrivals">Jadwal Datang Hari Ini</x-ui.select.item>
                            <x-ui.select.item value="today_departures">Jadwal Pulang Hari Ini</x-ui.select.item>
                        </x-ui.select.content>
                    </x-ui.select>
                </div>

                <div class="flex items-center gap-2">
                    <x-ui.button type="submit" variant="outline">
                        Terapkan Filter
                    </x-ui.button>
                    @if ($search || $status !== 'all' || $dateFilter !== 'all')
                        <x-ui.button as="a" href="{{ route('bookings.index') }}" variant="ghost">
                            Reset
                        </x-ui.button>
                    @endif
                </div>
            </form>
        </x-ui.card>

        <!-- Bookings Table -->
        <x-ui.card class="p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/50 text-xs font-semibold uppercase text-gray-500 dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-400">
                            <th class="px-5 py-3.5 text-start">Kode &amp; Tamu</th>
                            <th class="px-5 py-3.5 text-start">Kamar</th>
                            <th class="px-5 py-3.5 text-start">Periode Menginap</th>
                            <th class="px-5 py-3.5 text-start">Sewa &amp; Deposit</th>
                            <th class="px-5 py-3.5 text-start">Status</th>
                            <th class="px-5 py-3.5 text-end">Aksi Operasional</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($bookings as $booking)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/2">
                                <td class="px-5 py-4">
                                    <div class="font-mono text-xs font-bold text-brand-600 dark:text-brand-400">
                                        <a href="{{ route('bookings.show', $booking) }}" class="hover:underline">
                                            {{ $booking->booking_code }}
                                        </a>
                                    </div>
                                    <div class="mt-0.5 font-medium text-gray-800 dark:text-white/90">
                                        {{ $booking->customer->full_name }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ $booking->customer->phone_number }}
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div
                                        class="inline-flex items-center gap-1.5 font-semibold text-gray-800 dark:text-white/90">
                                        <x-ui.badge variant="light" color="gray" size="sm" class="font-mono">
                                            Kamar {{ $booking->room->room_number }}
                                        </x-ui.badge>
                                    </div>
                                    <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $booking->room->roomType?->name }}
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-xs">
                                    <div class="font-medium text-gray-700 dark:text-gray-300">
                                        {{ $booking->check_in_date->format('d M Y') }} &rarr;
                                        {{ $booking->check_out_date->format('d M Y') }}
                                    </div>
                                    <div class="mt-0.5 text-gray-400">
                                        Durasi: <strong>{{ $booking->total_nights }} malam</strong>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-xs">
                                    <div class="font-semibold text-gray-800 dark:text-white/90">
                                        Rp {{ number_format($booking->total_room_charge, 0, ',', '.') }}
                                    </div>
                                    <div class="text-gray-500 dark:text-gray-400">
                                        Deposit: <span class="text-emerald-600 dark:text-emerald-400 font-medium">Rp
                                            {{ number_format($booking->deposit_amount, 0, ',', '.') }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @php
                                        $color = match ($booking->status) {
                                            'checked_in' => 'success',
                                            'confirmed' => 'primary',
                                            'checked_out' => 'gray',
                                            default => 'error',
                                        };
                                    @endphp
                                    <x-ui.badge variant="light" :color="$color" size="default" class="capitalize">
                                        {{ str_replace('_', ' ', $booking->status) }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-5 py-4 text-end">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Check-In Action -->
                                        @if (in_array($booking->status, ['confirmed', 'pending']))
                                            <x-ui.button type="button" size="sm"
                                                @click="openCheckIn({{ $booking->id }}, '{{ addslashes($booking->customer->full_name) }}', '{{ $booking->room->room_number }}', {{ $booking->deposit_amount }})">
                                                Check-In
                                            </x-ui.button>
                                        @endif

                                        <!-- Check-Out Action -->
                                        @if ($booking->status === 'checked_in')
                                            <x-ui.button type="button" size="sm"
                                                class="bg-emerald-600 hover:bg-emerald-700 text-white"
                                                @click="openCheckOut({{ $booking->id }}, '{{ addslashes($booking->customer->full_name) }}', '{{ $booking->room->room_number }}')">
                                                Check-Out
                                            </x-ui.button>
                                        @endif

                                        <!-- Detail Button -->
                                        <x-ui.button as="a" href="{{ route('bookings.show', $booking) }}"
                                            variant="outline" size="sm">
                                            Detail
                                        </x-ui.button>

                                        <!-- More Actions Dropdown -->
                                        <x-ui.dropdown-menu>
                                            <x-ui.dropdown-menu.trigger as-child>
                                                <x-ui.button variant="ghost" size="icon-sm">
                                                    <x-svg.vertical-dots class="size-4" />
                                                </x-ui.button>
                                            </x-ui.dropdown-menu.trigger>
                                            <x-ui.dropdown-menu.content align="end" class="w-44">
                                                {{-- @if ($booking->status === 'checked_in')
                                                    <x-ui.dropdown-menu.item as-child>
                                                        <a href="{{ route('bookings.show', $booking) }}" class="text-brand-600 font-semibold">
                                                            + Order Layanan / POS
                                                        </a>
                                                    </x-ui.dropdown-menu.item>
                                                @endif --}}
                                                <x-ui.dropdown-menu.item as-child>
                                                    <a href="{{ route('bookings.edit', $booking) }}">
                                                        Edit Reservasi
                                                    </a>
                                                </x-ui.dropdown-menu.item>
                                                @if ($booking->status !== 'cancelled' && $booking->status !== 'checked_out')
                                                    <x-ui.dropdown-menu.item as-child>
                                                        <form action="{{ route('bookings.cancel', $booking) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Yakin ingin membatalkan reservasi ini?')">
                                                            @csrf
                                                            <button type="submit"
                                                                class="w-full text-start text-rose-600">
                                                                Batalkan Booking
                                                            </button>
                                                        </form>
                                                    </x-ui.dropdown-menu.item>
                                                @endif
                                                @if ($booking->status !== 'checked_in')
                                                    <x-ui.dropdown-menu.separator />
                                                    <x-ui.dropdown-menu.item as-child>
                                                        <form action="{{ route('bookings.destroy', $booking) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Hapus data reservasi ini secara permanen?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="w-full text-start text-error-600">
                                                                Hapus Permanen
                                                            </button>
                                                        </form>
                                                    </x-ui.dropdown-menu.item>
                                                @endif
                                            </x-ui.dropdown-menu.content>
                                        </x-ui.dropdown-menu>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada data reservasi atau tamu aktif yang cocok dengan kriteria pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($bookings->hasPages())
                <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                    {{ $bookings->links() }}
                </div>
            @endif
        </x-ui.card>

        <!-- Fast Check-In Modal with Deposit -->
        <x-ui.dialog id="checkInDialog" x-ref="checkInDialog">
            <x-ui.dialog.content class="sm:max-w-md">
                <x-ui.dialog.header class="text-start pe-10">
                    <x-ui.dialog.title>
                        Proses Check-In Tamu
                    </x-ui.dialog.title>
                    <x-ui.dialog.description>
                        Serah terima kunci fisik kamar dan pencatatan uang jaminan / deposit awal.
                    </x-ui.dialog.description>
                </x-ui.dialog.header>

                <form :action="checkInActionUrl" method="POST" class="space-y-4">
                    @csrf
                    <div class="rounded-xl bg-gray-50 p-3.5 dark:bg-gray-800/60 text-xs space-y-1">
                        <div>Tamu: <strong class="text-gray-800 dark:text-white" x-text="guestName"></strong></div>
                        <div>Kamar: <strong class="text-brand-600 dark:text-brand-400"
                                x-text="'Kamar ' + roomNumber"></strong></div>
                        <div class="text-gray-400">Status kamar akan otomatis beralih menjadi <strong>Occupied</strong>.
                        </div>
                    </div>

                    <x-ui.field>
                        <x-ui.field.label for="check-in-deposit-amount">
                            Penerimaan Uang Jaminan / Deposit (Rp)
                        </x-ui.field.label>
                        <x-ui.input id="check-in-deposit-amount" type="number" step="50000" min="0"
                            name="deposit_amount" x-model="depositAmount" placeholder="0" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Masukkan nominal deposit kunci/fasilitas yang diterima resepsionis (bila ada).
                        </p>
                    </x-ui.field>

                    <x-ui.dialog.footer class="pt-2">
                        <x-ui.dialog.close as-child>
                            <x-ui.button variant="outline" type="button">Batal</x-ui.button>
                        </x-ui.dialog.close>
                        <x-ui.button type="submit">
                            Serah Terima Kunci &amp; Check-In
                        </x-ui.button>
                    </x-ui.dialog.footer>
                </form>
            </x-ui.dialog.content>
        </x-ui.dialog>

        <!-- Folio Verification & Check-Out Modal -->
        <x-main.bookings.checkout-modal />

    </div>
@endsection
