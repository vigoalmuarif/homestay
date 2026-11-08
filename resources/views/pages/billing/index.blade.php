@extends('layouts.app')

@section('content')
    <div x-data="{
        settleModalOpen: false,
        bookingId: null,
        bookingCode: '',
        guestName: '',
        roomNumber: '',
        balanceDue: 0,
        amountToPay: 0,
        settleActionUrl: '',
        isCheckedIn: false,
        autoCheckout: false,

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
        openQuickSettle(id, code, guest, room, due, isCheckedIn) {
            this.bookingId = id;
            this.bookingCode = code;
            this.guestName = guest;
            this.roomNumber = room;
            this.balanceDue = due;
            this.amountToPay = due;
            this.isCheckedIn = isCheckedIn;
            this.autoCheckout = isCheckedIn;
            this.settleActionUrl = `/billing/${id}/settle`;
            this.showDialog('quickSettleDialog');
        }
    }" class="space-y-6">

        <x-common.page-breadcrumb pageTitle="Folio Billing & Settlement" />

        <!-- Top Header & Export -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Pusat kalkulasi folio tagihan kamar, settlement pelunasan check-out kasir, dan ringkasan keuangan.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <x-ui.button as="a" href="{{ route('billing.export-csv', request()->query()) }}" variant="outline" size="sm">
                    <x-svg.download class="size-4" />
                    <span>Export CSV</span>
                </x-ui.button>
            </div>
        </div>

        @if (session('success'))
            <x-ui.alert class="border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-ui.alert.title>Berhasil</x-ui.alert.title>
                <x-ui.alert.description>{{ session('success') }}</x-ui.alert.description>
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert class="border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-800 dark:bg-rose-500/10 dark:text-rose-400">
                <x-ui.alert.title>Gagal Memproses</x-ui.alert.title>
                <x-ui.alert.description>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert.description>
            </x-ui.alert>
        @endif

        <!-- Financial Metrics Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- 1. Gross Revenue -->
            <x-ui.card class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Total Omzet (Bruto)</p>
                        <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white">
                            Rp {{ number_format($metrics['gross_revenue'], 0, ',', '.') }}
                        </h4>
                    </div>
                    <div class="flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400">
                        <x-svg.pie-chart class="size-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>Sewa: Rp {{ number_format($metrics['total_room_revenue'], 0, ',', '.') }}</span>
                    <span>POS: Rp {{ number_format($metrics['total_service_revenue'], 0, ',', '.') }}</span>
                </div>
            </x-ui.card>

            <!-- 2. Outstanding Balance Due (Piutang) -->
            <x-ui.card class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Piutang / Sisa Tagihan</p>
                        <h4 class="mt-2 text-xl font-bold text-amber-600 dark:text-amber-400">
                            Rp {{ number_format($metrics['total_outstanding_balance'], 0, ',', '.') }}
                        </h4>
                    </div>
                    <div class="flex size-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400">
                        <x-svg.info-circle class="size-5" />
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    <span>{{ $metrics['unpaid_count'] }} Unpaid &bull; {{ $metrics['partial_count'] }} Partial</span>
                </div>
            </x-ui.card>

            <!-- 3. Deposits Held -->
            <x-ui.card class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Uang Jaminan / Deposit</p>
                        <h4 class="mt-2 text-xl font-bold text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($metrics['total_deposits'], 0, ',', '.') }}
                        </h4>
                    </div>
                    <div class="flex size-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
                        <x-svg.check-circle class="size-5" />
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    <span>Tercatat dari penerimaan check-in</span>
                </div>
            </x-ui.card>

            <!-- 4. Paid Folios -->
            <x-ui.card class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Folio Lunas</p>
                        <h4 class="mt-2 text-xl font-bold text-blue-600 dark:text-blue-400">
                            {{ $metrics['paid_count'] }} / {{ $metrics['total_bookings'] }}
                        </h4>
                    </div>
                    <div class="flex size-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">
                        <x-svg.file class="size-5" />
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    <span>Total reservasi diproses</span>
                </div>
            </x-ui.card>
        </div>

        <!-- Filter & Search Section -->
        <x-ui.card class="p-4 sm:p-5">
            <form method="GET" action="{{ route('billing.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <!-- Search Input -->
                <div class="lg:col-span-2">
                    <x-ui.input name="search" value="{{ $search }}" placeholder="Cari kode booking, nama tamu, no hp, kamar..." />
                </div>

                <!-- Payment Status Filter -->
                <div>
                    <select name="payment_status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="all" {{ $paymentStatus === 'all' ? 'selected' : '' }}>Semua Status Bayar</option>
                        <option value="unpaid" {{ $paymentStatus === 'unpaid' ? 'selected' : '' }}>Unpaid (Belum Lunas)</option>
                        <option value="partial" {{ $paymentStatus === 'partial' ? 'selected' : '' }}>Partial (Sebagian)</option>
                        <option value="paid" {{ $paymentStatus === 'paid' ? 'selected' : '' }}>Paid (Lunas)</option>
                    </select>
                </div>

                <!-- Booking Status Filter -->
                <div>
                    <select name="status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status Reservasi</option>
                        <option value="checked_in" {{ $status === 'checked_in' ? 'selected' : '' }}>In-House (Check-In)</option>
                        <option value="checked_out" {{ $status === 'checked_out' ? 'selected' : '' }}>Checked-Out</option>
                        <option value="confirmed" {{ $status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>

                <!-- Submit & Reset Buttons -->
                <div class="flex items-center gap-2">
                    <x-ui.button type="submit" variant="primary" class="w-full justify-center" size="sm">
                        Filter
                    </x-ui.button>
                    @if (filled($search) || $paymentStatus !== 'all' || $status !== 'all' || filled($startDate) || filled($endDate))
                        <x-ui.button as="a" href="{{ route('billing.index') }}" variant="outline" size="sm" class="px-2">
                            Reset
                        </x-ui.button>
                    @endif
                </div>
            </form>
        </x-ui.card>

        <!-- Folio Table -->
        <x-ui.card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-start text-xs">
                    <thead class="border-b border-gray-100 bg-gray-50/75 dark:border-gray-800 dark:bg-gray-800/40">
                        <tr class="text-gray-500 dark:text-gray-400">
                            <th class="py-3.5 ps-5 pe-3 font-medium text-start">Kode &amp; Tamu</th>
                            <th class="px-3 py-3.5 font-medium text-start">Kamar &amp; Jadwal</th>
                            <th class="px-3 py-3.5 font-medium text-end">Sewa Kamar</th>
                            <th class="px-3 py-3.5 font-medium text-end">Layanan / POS</th>
                            <th class="px-3 py-3.5 font-medium text-end">Grand Total</th>
                            <th class="px-3 py-3.5 font-medium text-end">Deposit</th>
                            <th class="px-3 py-3.5 font-medium text-end">Sisa Tagihan</th>
                            <th class="px-3 py-3.5 font-medium text-center">Status Bayar</th>
                            <th class="py-3.5 pe-5 ps-3 font-medium text-end">Aksi Kasir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($bookings as $b)
                            @php
                                $roomCharge = (float) ($b->room_price_snapshot * max(1, $b->total_nights));
                                $posCharge = (float) $b->items->where('billing_type', 'charged_to_room')->where('status', '!=', 'cancelled')->sum('subtotal');
                                $grossTotal = $roomCharge + $posCharge;
                                $deposit = (float) ($b->deposit_amount ?? 0);
                                $balanceDue = max(0, $grossTotal - $deposit);

                                $payBadge = match ($b->payment_status) {
                                    'paid' => ['color' => 'success', 'label' => 'Lunas (Paid)'],
                                    'partial' => ['color' => 'warning', 'label' => 'Partial (DP)'],
                                    default => ['color' => 'error', 'label' => 'Belum Lunas'],
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                <!-- Kode & Tamu -->
                                <td class="py-3.5 ps-5 pe-3">
                                    <div class="font-bold text-brand-600 dark:text-brand-400">
                                        <a href="{{ route('billing.show', $b) }}" class="hover:underline">
                                            {{ $b->booking_code }}
                                        </a>
                                    </div>
                                    <div class="font-medium text-gray-800 dark:text-white">
                                        {{ $b->customer?->full_name ?? '-' }}
                                    </div>
                                    <div class="text-[11px] text-gray-400">
                                        {{ $b->customer?->phone_number ?? '-' }}
                                    </div>
                                </td>

                                <!-- Kamar & Jadwal -->
                                <td class="px-3 py-3.5">
                                    <div class="font-semibold text-gray-800 dark:text-white">
                                        Kamar {{ $b->room?->room_number ?? '-' }}
                                        <span class="text-[11px] font-normal text-gray-500">({{ $b->room?->roomType?->name ?? '-' }})</span>
                                    </div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                        {{ $b->check_in_date->format('d/m/y') }} &rarr; {{ $b->check_out_date->format('d/m/y') }}
                                        <span class="font-medium text-brand-600">({{ $b->total_nights }} mlm)</span>
                                    </div>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-medium {{ $b->status_badge['class'] }}">
                                            {{ $b->status_badge['label'] }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Sewa Kamar -->
                                <td class="px-3 py-3.5 text-end text-gray-600 dark:text-gray-300">
                                    Rp {{ number_format($roomCharge, 0, ',', '.') }}
                                </td>

                                <!-- Layanan / POS -->
                                <td class="px-3 py-3.5 text-end text-gray-600 dark:text-gray-300">
                                    Rp {{ number_format($posCharge, 0, ',', '.') }}
                                </td>

                                <!-- Grand Total -->
                                <td class="px-3 py-3.5 text-end font-semibold text-gray-800 dark:text-white">
                                    Rp {{ number_format($grossTotal, 0, ',', '.') }}
                                </td>

                                <!-- Deposit -->
                                <td class="px-3 py-3.5 text-end text-emerald-600 dark:text-emerald-400">
                                    @if ($deposit > 0)
                                        - Rp {{ number_format($deposit, 0, ',', '.') }}
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600">Rp 0</span>
                                    @endif
                                </td>

                                <!-- Sisa Tagihan -->
                                <td class="px-3 py-3.5 text-end">
                                    @if ($balanceDue > 0)
                                        <span class="font-bold text-rose-600 dark:text-rose-400">
                                            Rp {{ number_format($balanceDue, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="font-medium text-emerald-600 dark:text-emerald-400">Rp 0 (Lunas)</span>
                                    @endif
                                </td>

                                <!-- Status Bayar -->
                                <td class="px-3 py-3.5 text-center">
                                    <x-ui.badge variant="light" :color="$payBadge['color']" size="sm">
                                        {{ $payBadge['label'] }}
                                    </x-ui.badge>
                                </td>

                                <!-- Aksi Kasir -->
                                <td class="py-3.5 pe-5 ps-3 text-end">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if ($balanceDue > 0)
                                            <button type="button"
                                                @click="openQuickSettle({{ $b->id }}, '{{ $b->booking_code }}', '{{ addslashes($b->customer?->full_name ?? '') }}', '{{ $b->room?->room_number ?? '' }}', {{ $balanceDue }}, {{ $b->status === 'checked_in' ? 'true' : 'false' }})"
                                                class="rounded-lg bg-brand-600 px-2.5 py-1 text-[11px] font-semibold text-white shadow-theme-xs hover:bg-brand-700">
                                                Pelunasan
                                            </button>
                                        @endif
                                        <a href="{{ route('billing.show', $b) }}"
                                            class="rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-[11px] font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                            Folio
                                        </a>
                                        <a href="{{ route('billing.invoice', $b) }}" target="_blank"
                                            class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-[11px] font-medium text-gray-600 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                            <x-svg.printer class="size-3.5" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada data folio transaksi reservasi yang sesuai dengan kriteria filter.
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

        <!-- Quick Settlement Dialog -->
        <x-ui.dialog id="quickSettleDialog">
            <x-ui.dialog.content class="sm:max-w-md">
                <x-ui.dialog.header class="text-start pe-10">
                    <x-ui.dialog.title>
                        Pelunasan Tagihan Kasir
                    </x-ui.dialog.title>
                    <x-ui.dialog.description>
                        Catat pembayaran pelunasan sisa tagihan untuk reservasi <strong x-text="bookingCode"></strong>.
                    </x-ui.dialog.description>
                </x-ui.dialog.header>

                <form :action="settleActionUrl" method="POST" class="mt-4 space-y-4">
                    @csrf

                    <div class="rounded-xl bg-gray-50 p-3.5 dark:bg-gray-800/60 text-xs space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Nama Tamu:</span>
                            <span class="font-semibold text-gray-800 dark:text-white" x-text="guestName"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Nomor Kamar:</span>
                            <span class="font-semibold text-brand-600 dark:text-brand-400" x-text="'Kamar ' + roomNumber"></span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200/60 pt-1.5 dark:border-gray-700/60">
                            <span class="font-bold text-gray-700 dark:text-gray-300">Sisa Tagihan Saat Ini:</span>
                            <span class="font-bold text-rose-600 dark:text-rose-400" x-text="'Rp ' + Number(balanceDue).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <x-ui.field>
                        <x-ui.field.label for="amount_paid">
                            Nominal Pembayaran Diterima (Rp)
                        </x-ui.field.label>
                        <x-ui.input id="amount_paid" type="number" step="1000" min="0" name="amount_paid" x-model="amountToPay" required />
                    </x-ui.field>

                    <x-ui.field>
                        <x-ui.field.label for="payment_method">
                            Metode Pembayaran
                        </x-ui.field.label>
                        <select id="payment_method" name="payment_method" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white" required>
                            <option value="cash">Tunai (Cash)</option>
                            <option value="qris">QRIS / E-Wallet</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="card">Kartu Debit / Kredit</option>
                            <option value="deposit_offset">Potong Deposit / Uang Jaminan</option>
                        </select>
                    </x-ui.field>

                    <template x-if="isCheckedIn">
                        <div class="rounded-xl border border-brand-200 bg-brand-50/60 p-3 text-xs dark:border-brand-900/40 dark:bg-brand-950/20">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="auto_checkout" value="1" x-model="autoCheckout" class="mt-0.5 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <div>
                                    <span class="font-semibold text-gray-800 dark:text-white">Selesaikan Check-Out Tamu Sekarang</span>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                        Status reservasi akan diubah menjadi <strong>Checked-Out</strong> dan status kamar dialihkan menjadi <strong>Dirty</strong> (Perlu Pembersihan Housekeeping).
                                    </p>
                                </div>
                            </label>
                        </div>
                    </template>

                    <x-ui.field>
                        <x-ui.field.label for="settle_notes">
                            Catatan Tambahan (Opsional)
                        </x-ui.field.label>
                        <x-ui.textarea id="settle_notes" name="notes" rows="2" placeholder="Nomor referensi bukti transfer atau memo kasir..." />
                    </x-ui.field>

                    <x-ui.dialog.footer class="pt-2">
                        <x-ui.dialog.close as-child>
                            <x-ui.button variant="outline" type="button">Batal</x-ui.button>
                        </x-ui.dialog.close>
                        <x-ui.button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white">
                            Simpan Pelunasan
                        </x-ui.button>
                    </x-ui.dialog.footer>
                </form>
            </x-ui.dialog.content>
        </x-ui.dialog>

    </div>
@endsection

