@extends('layouts.app')

@section('content')
    <div x-data="{
        depositAmount: {{ (float) ($booking->deposit_amount ?? 0) }},
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
        }
    }" class="space-y-6">

        <!-- Breadcrumb Navigation -->
        <x-common.page-breadcrumb pageTitle="Reservasi {{ $booking->booking_code }}" />

        @php
            $statusMap = [
                'pending' => ['color' => 'warning', 'label' => 'Menunggu Check-In'],
                'confirmed' => ['color' => 'info', 'label' => 'Terkonfirmasi'],
                'checked_in' => ['color' => 'primary', 'label' => 'In-House (Check-In)'],
                'checked_out' => ['color' => 'success', 'label' => 'Selesai (Check-Out)'],
                'cancelled' => ['color' => 'error', 'label' => 'Dibatalkan'],
            ];
            $statusInfo = $statusMap[$booking->status] ?? ['color' => 'gray', 'label' => ucfirst($booking->status)];
        @endphp

        <!-- Top Header & Navigation -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button as="a" href="{{ route('bookings.index') }}" variant="outline" size="sm">
                    &larr; Daftar Reservasi
                </x-ui.button>
                <x-ui.badge variant="light" :color="$statusInfo['color']" size="default">
                    {{ $statusInfo['label'] }}
                </x-ui.badge>
            </div>

            <!-- Operational Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                {{-- @if ($booking->status === 'checked_in')
                    <x-ui.button type="button" @click="window.dispatchEvent(new CustomEvent('open-order-modal'))">
                        <x-svg.plus class="size-4" />
                        <span>Order Layanan / Mini-POS</span>
                    </x-ui.button>
                @endif --}}

                @if (in_array($booking->status, ['confirmed', 'pending']))
                    <x-ui.button type="button" @click="showDialog('bookingCheckInDialog')">
                        <x-svg.check class="size-4" />
                        <span>Serah Kunci &amp; Check-In</span>
                    </x-ui.button>
                @endif

                @if ($booking->status === 'checked_in')
                    <form action="{{ route('bookings.check-out', $booking) }}" method="POST"
                        onsubmit="return confirm('Selesaikan Check-Out untuk Kamar {{ $booking->room->room_number }}? Kamar akan berstatus Dirty untuk dibersihkan.')">
                        @csrf
                        <x-ui.button type="submit" variant="primary"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white">
                            <x-svg.check class="size-4" />
                            <span>Check-Out Tamu</span>
                        </x-ui.button>
                    </form>
                @endif

                <x-ui.button as="a" href="{{ route('bookings.edit', $booking) }}" variant="outline" size="default">
                    <x-svg.pencil class="size-4" />
                    <span>Edit</span>
                </x-ui.button>

                @if ($booking->status !== 'cancelled' && $booking->status !== 'checked_out')
                    <form action="{{ route('bookings.cancel', $booking) }}" method="POST"
                        onsubmit="return confirm('Batalkan reservasi {{ $booking->booking_code }}?')">
                        @csrf
                        <x-ui.button type="submit" variant="outline"
                            class="text-error-600 border-error-200 hover:bg-error-50 dark:border-error-800 dark:hover:bg-error-900/20">
                            Batalkan
                        </x-ui.button>
                    </form>
                @endif
            </div>
        </div>

        @if (session('success'))
            <x-ui.alert>
                <x-ui.alert.title>Operasi Berhasil</x-ui.alert.title>
                <x-ui.alert.description>{{ session('success') }}</x-ui.alert.description>
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert variant="destructive">
                <x-ui.alert.title>Terjadi kesalahan:</x-ui.alert.title>
                <x-ui.alert.description>
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert.description>
            </x-ui.alert>
        @endif

        <!-- Details Grid (2 Columns) -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <!-- Left Col (2 span): Guest & Room & Stay -->
            <div class="space-y-6 lg:col-span-2">

                <!-- Guest & Room Cards -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Guest Card -->
                    <x-ui.card class="p-5">
                        <div class="text-xs font-semibold uppercase text-gray-400">Profil Tamu</div>
                        <div class="mt-2 text-lg font-bold text-gray-800 dark:text-white/90">
                            {{ $booking->customer->full_name }}
                        </div>
                        <div class="mt-3 space-y-1.5 text-xs text-gray-600 dark:text-gray-300">
                            <div>Telepon / WA: <strong
                                    class="text-gray-800 dark:text-white">{{ $booking->customer->phone_number }}</strong>
                            </div>
                            <div>Identitas: {{ strtoupper($booking->customer->identity_type) }}
                                ({{ $booking->customer->identity_number ?? '-' }})</div>
                            <div>Email: {{ $booking->customer->email ?? '-' }}</div>
                            <div>Negara / Alamat: {{ $booking->customer->country ?? 'Indonesia' }}</div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800">
                            <x-ui.button as="a" href="{{ route('customers.show', $booking->customer) }}"
                                variant="ghost" size="sm" class="px-0 text-brand-600 dark:text-brand-400">
                                Buka Profil Tamu &rarr;
                            </x-ui.button>
                        </div>
                    </x-ui.card>

                    <!-- Room Card -->
                    <x-ui.card class="p-5">
                        <div class="text-xs font-semibold uppercase text-gray-400">Alokasi Kamar</div>
                        <div class="mt-2 flex items-center gap-2">
                            <span
                                class="rounded-lg bg-gray-100 px-3 py-1 font-mono text-base font-bold text-gray-800 dark:bg-gray-800 dark:text-white">
                                Kamar {{ $booking->room->room_number }}
                            </span>
                        </div>
                        <div class="mt-3 space-y-1.5 text-xs text-gray-600 dark:text-gray-300">
                            <div>Tipe: <strong
                                    class="text-gray-800 dark:text-white">{{ $booking->room->roomType?->name }}</strong>
                            </div>
                            <div>Kapasitas: {{ $booking->room->roomType?->capacity ?? 2 }} Orang</div>
                            <div>Lantai: {{ $booking->room->floor ?? '1' }}</div>
                            <div>Status Fisik Kamar:
                                <span
                                    class="capitalize font-semibold text-brand-600 dark:text-brand-400">{{ $booking->room->status }}</span>
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <!-- Stay Duration & Timestamps Card -->
                <x-ui.card class="p-6 space-y-4">
                    <h2
                        class="text-base font-bold text-gray-800 dark:text-white/90 border-b border-gray-100 pb-3 dark:border-gray-800">
                        Jadwal Menginap &amp; Waktu Kedatangan
                    </h2>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 text-xs">
                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/60">
                            <div class="text-gray-400 font-medium uppercase">Tanggal &amp; Waktu Check-In</div>
                            <div class="mt-1 text-base font-bold text-gray-800 dark:text-white">
                                {{ $booking->check_in_date->format('d M Y') }}
                                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400 ms-1">
                                    {{ $booking->check_in_date->format('H:i') !== '00:00' ? $booking->check_in_date->format('H:i') : '14:00' }}
                                    WIB
                                </span>
                            </div>
                            <div class="mt-1 text-gray-500 dark:text-gray-400">
                                Aktual:
                                {{ $booking->actual_check_in_at ? $booking->actual_check_in_at->format('d M Y, H:i \W\I\B') : 'Belum Check-In' }}
                            </div>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/60">
                            <div class="text-gray-400 font-medium uppercase">Tanggal &amp; Waktu Check-Out</div>
                            <div class="mt-1 text-base font-bold text-gray-800 dark:text-white">
                                {{ $booking->check_out_date->format('d M Y') }}
                                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400 ms-1">
                                    {{ $booking->check_out_date->format('H:i') !== '00:00' ? $booking->check_out_date->format('H:i') : '12:00' }}
                                    WIB
                                </span>
                            </div>
                            <div class="mt-1 text-gray-500 dark:text-gray-400">
                                Aktual:
                                {{ $booking->actual_check_out_at ? $booking->actual_check_out_at->format('d M Y, H:i \W\I\B') : 'Belum Check-Out' }}
                            </div>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/60">
                            <div class="text-gray-400 font-medium uppercase">Durasi Menginap</div>
                            <div class="mt-1 text-base font-bold text-brand-600 dark:text-brand-400">
                                {{ $booking->total_nights }} Malam
                            </div>
                            <div class="mt-1 text-gray-500 dark:text-gray-400">
                                Dibuat: {{ $booking->created_at->format('d M Y, H:i \W\I\B') }}
                            </div>
                        </div>
                    </div>

                    @if ($booking->notes)
                        <div
                            class="rounded-xl border border-gray-100 bg-amber-50/50 p-3.5 text-xs text-amber-800 dark:border-gray-800 dark:bg-amber-900/10 dark:text-amber-300">
                            <strong>Catatan Tambahan:</strong> {{ $booking->notes }}
                        </div>
                    @endif
                </x-ui.card>

                <!-- Room Orders & Mini-POS Section -->
                <x-ui.card class="p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div>
                            <h2 class="text-base font-bold text-gray-800 dark:text-white/90">
                                Pesanan Layanan &amp; Mini-POS
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Rincian makanan, minuman, laundry &amp; layanan tambahan kamar ini.
                            </p>
                        </div>
                        {{-- @if ($booking->status === 'checked_in')
                            <x-ui.button type="button" size="sm" variant="outline"
                                @click="window.dispatchEvent(new CustomEvent('open-order-modal'))">
                                <x-svg.plus class="size-3.5" />
                                <span>Tambah Pesanan</span>
                            </x-ui.button>
                        @endif --}}
                    </div>

                    @if ($booking->items->isEmpty())
                        <div class="rounded-xl border border-dashed border-gray-200 py-8 text-center dark:border-gray-800">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Belum ada pesanan layanan atau F&amp;B untuk
                                kamar ini.</p>
                            {{-- @if ($booking->status === 'checked_in')
                                <x-ui.button type="button" size="sm" class="mt-3"
                                    @click="window.dispatchEvent(new CustomEvent('open-order-modal'))">
                                    + Buka Menu Order Layanan
                                </x-ui.button>
                            @endif --}}
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-start text-xs">
                                <thead>
                                    <tr
                                        class="border-b border-gray-100 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                        <th class="pb-2 font-medium text-start">Layanan / Item</th>
                                        <th class="pb-2 font-medium text-center">Qty</th>
                                        <th class="pb-2 font-medium text-end">Tarif Satuan</th>
                                        <th class="pb-2 font-medium text-end">Diskon / Inklusi</th>
                                        <th class="pb-2 font-medium text-end">Subtotal</th>
                                        <th class="pb-2 font-medium text-center">Penagihan</th>
                                        <th class="pb-2 font-medium text-center">Status</th>
                                        <th class="pb-2 font-medium text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach ($booking->items as $item)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                            <td class="py-3 font-medium text-gray-800 dark:text-white">
                                                <div>{{ $item->item_name }}</div>
                                                @if ($item->notes)
                                                    <div class="text-[11px] text-gray-400 italic">"{{ $item->notes }}"
                                                    </div>
                                                @endif
                                                @if ($item->is_free_inclusion)
                                                    <x-ui.badge color="success" size="sm" class="mt-1">
                                                        Gratis Paket Kamar
                                                    </x-ui.badge>
                                                @elseif ((float) $item->discount_or_allowance > 0)
                                                    <x-ui.badge color="info" size="sm" class="mt-1">
                                                        Subsidi Paket Kamar
                                                    </x-ui.badge>
                                                @endif
                                            </td>
                                            <td class="py-3 text-center text-gray-700 dark:text-gray-300 font-semibold">
                                                {{ $item->quantity }}
                                            </td>
                                            <td class="py-3 text-end text-gray-600 dark:text-gray-400">
                                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                            </td>
                                            <td class="py-3 text-end text-emerald-600 dark:text-emerald-400">
                                                @if ($item->discount_or_allowance > 0)
                                                    - Rp {{ number_format($item->discount_or_allowance, 0, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="py-3 text-end font-bold text-gray-800 dark:text-white">
                                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                            </td>
                                            <td class="py-3 text-center">
                                                @if ($item->billing_type === 'charged_to_room')
                                                    <x-ui.badge color="primary" size="sm">
                                                        Beban Kamar
                                                    </x-ui.badge>
                                                @else
                                                    <x-ui.badge color="success" size="sm">
                                                        Lunas Langsung
                                                    </x-ui.badge>
                                                @endif
                                            </td>
                                            <td class="py-3 text-center">
                                                @if ($item->status === 'cancelled')
                                                    <x-ui.badge color="error" size="sm">
                                                        Dibatalkan
                                                    </x-ui.badge>
                                                @else
                                                    <x-ui.badge color="gray" size="sm">
                                                        {{ ucfirst($item->status) }}
                                                    </x-ui.badge>
                                                @endif
                                            </td>
                                            <td class="py-3 text-end">
                                                @if ($item->status !== 'cancelled' && $booking->status !== 'checked_out')
                                                    <form
                                                        action="{{ route('bookings.orders.destroy', [$booking, $item]) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Batalkan pesanan {{ $item->item_name }}? Stok fisik akan dikembalikan.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="text-[11px] font-medium text-rose-600 hover:text-rose-700 dark:text-rose-400">
                                                            Batal
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-gray-300 dark:text-gray-600">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </x-ui.card>

            </div>

            <!-- Right Col (1 span): Financial & Deposit Card -->
            <div class="space-y-6">
                <x-ui.card class="p-6 space-y-4">
                    <h2
                        class="text-base font-bold text-gray-800 dark:text-white/90 border-b border-gray-100 pb-3 dark:border-gray-800">
                        Folio Tagihan &amp; Settlement
                    </h2>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between text-gray-600 dark:text-gray-300">
                            <span>Sewa Kamar ({{ $booking->total_nights }} Malam):</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                Rp {{ number_format($booking->total_room_charge, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-300">
                            <span>Pesanan Layanan (Beban Kamar):</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                Rp {{ number_format($booking->total_extra_charges, 0, ',', '.') }}
                            </span>
                        </div>
                        <div
                            class="border-t border-gray-100 pt-2.5 dark:border-gray-800 flex justify-between text-xs font-bold text-gray-800 dark:text-white">
                            <span>Grand Total Tagihan:</span>
                            <span class="text-brand-600 dark:text-brand-400">
                                Rp {{ number_format($booking->grand_total, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-100 dark:border-gray-800 space-y-2">
                        <div class="flex justify-between text-xs text-gray-600 dark:text-gray-300">
                            <span>Uang Jaminan / Deposit Kunci:</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                - Rp {{ number_format($booking->deposit_amount, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-600 dark:text-gray-300">
                            <span>Status Folio:</span>
                            <span
                                class="capitalize font-semibold text-gray-800 dark:text-white">{{ $booking->payment_status }}</span>
                        </div>
                    </div>

                    <div
                        class="rounded-xl bg-brand-50/60 p-3.5 dark:bg-brand-950/20 border border-brand-100 dark:border-brand-900/30">
                        <div class="text-[11px] font-medium text-gray-500 uppercase">Sisa Tagihan Pelunasan</div>
                        <div class="text-lg font-bold text-brand-600 dark:text-brand-400">
                            Rp {{ number_format($booking->balance_due, 0, ',', '.') }}
                        </div>
                    </div>

                    {{-- <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                        <x-ui.button as="a" href="{{ route('billing.show', $booking) }}" variant="outline"
                            class="w-full justify-center">
                            Buka Folio Billing &amp; Settlement &rarr;
                        </x-ui.button>
                    </div> --}}
                </x-ui.card>
            </div>

        </div>

        <!-- Order Layanan & Mini-POS Modal Component -->
        <x-main.bookings.order-modal :booking="$booking" :services="$services" />

        <!-- Fast Check-In Modal with Deposit using UI Dialog -->
        <x-ui.dialog id="bookingCheckInDialog" x-ref="checkInDialog">
            <x-ui.dialog.content class="sm:max-w-md">
                <x-ui.dialog.header class="text-start pe-10">
                    <x-ui.dialog.title>
                        Serah Terima Kunci &amp; Check-In
                    </x-ui.dialog.title>
                    <x-ui.dialog.description>
                        Konfirmasi kedatangan tamu dan serah kunci fisik kamar.
                    </x-ui.dialog.description>
                </x-ui.dialog.header>

                <form action="{{ route('bookings.check-in', $booking) }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div class="rounded-xl bg-gray-50 p-3.5 dark:bg-gray-800/60 text-xs space-y-1">
                        <div>Tamu: <strong
                                class="text-gray-800 dark:text-white">{{ $booking->customer->full_name }}</strong></div>
                        <div>Kamar: <strong class="text-brand-600 dark:text-brand-400">Kamar
                                {{ $booking->room->room_number }}</strong></div>
                        <div class="text-gray-400">Status kamar fisik akan otomatis beralih menjadi
                            <strong>Occupied</strong>.
                        </div>
                    </div>

                    <x-ui.field>
                        <x-ui.field.label for="show-checkin-deposit">
                            Penerimaan Uang Jaminan / Deposit (Rp)
                        </x-ui.field.label>
                        <x-ui.input id="show-checkin-deposit" type="number" step="50000" min="0"
                            name="deposit_amount" x-model="depositAmount" placeholder="0" />
                    </x-ui.field>

                    <x-ui.dialog.footer class="pt-3">
                        <x-ui.dialog.close as-child>
                            <x-ui.button variant="outline" type="button">Batal</x-ui.button>
                        </x-ui.dialog.close>
                        <x-ui.button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white">
                            Serah Kunci &amp; Check-In
                        </x-ui.button>
                    </x-ui.dialog.footer>
                </form>
            </x-ui.dialog.content>
        </x-ui.dialog>

    </div>
@endsection
