@extends('layouts.app')

@section('content')
    <div x-data="{
        amountPaid: {{ $folio['balance_due'] }},
        paymentMethod: 'cash',
        autoCheckout: {{ $booking->status === 'checked_in' ? 'true' : 'false' }},
    }" class="space-y-6">

        <x-common.page-breadcrumb pageTitle="Folio Statement & Settlement" />

        <!-- Top Header & Actions -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button as="a" href="{{ route('billing.index') }}" variant="outline" size="sm">
                    &larr; Daftar Folio
                </x-ui.button>
                <div class="text-sm font-bold text-gray-800 dark:text-white">
                    {{ $booking->booking_code }}
                </div>
                @php
                    $payBadge = match ($folio['payment_status']) {
                        'paid' => ['color' => 'success', 'label' => 'Lunas (Paid)'],
                        'partial' => ['color' => 'warning', 'label' => 'Partial (Sebagian)'],
                        default => ['color' => 'error', 'label' => 'Unpaid (Belum Lunas)'],
                    };
                @endphp
                <x-ui.badge variant="light" :color="$payBadge['color']" size="default">
                    {{ $payBadge['label'] }}
                </x-ui.badge>
                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $booking->status_badge['class'] }}">
                    {{ $booking->status_badge['label'] }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                <x-ui.button as="a" href="{{ route('billing.invoice', $booking) }}" target="_blank" variant="outline" size="sm">
                    <x-svg.printer class="size-4" />
                    <span>Cetak Invoice</span>
                </x-ui.button>
                <x-ui.button as="a" href="{{ route('bookings.show', $booking) }}" variant="outline" size="sm">
                    <span>Lihat Reservasi &rarr;</span>
                </x-ui.button>
            </div>
        </div>

        @if (session('success'))
            <x-ui.alert class="border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-ui.alert.title>Sukses</x-ui.alert.title>
                <x-ui.alert.description>{{ session('success') }}</x-ui.alert.description>
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert class="border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-800 dark:bg-rose-500/10 dark:text-rose-400">
                <x-ui.alert.title>Kesalahan</x-ui.alert.title>
                <x-ui.alert.description>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert.description>
            </x-ui.alert>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <!-- Left Col (2 spans): Folio Line Items -->
            <div class="space-y-6 lg:col-span-2">

                <!-- Guest & Stay Information -->
                <x-ui.card class="p-6">
                    <div class="border-b border-gray-100 pb-3 dark:border-gray-800">
                        <h2 class="text-base font-bold text-gray-800 dark:text-white">
                            Informasi Tamu &amp; Kamar
                        </h2>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 text-xs">
                        <div class="space-y-2">
                            <div>
                                <span class="text-gray-400">Nama Tamu:</span>
                                <div class="font-bold text-gray-800 dark:text-white text-sm">
                                    {{ $booking->customer?->full_name ?? '-' }}
                                </div>
                            </div>
                            <div>
                                <span class="text-gray-400">No. Kontak / WhatsApp:</span>
                                <div class="font-medium text-gray-700 dark:text-gray-300">
                                    {{ $booking->customer?->phone_number ?? '-' }}
                                </div>
                            </div>
                            <div>
                                <span class="text-gray-400">Identitas:</span>
                                <div class="font-medium text-gray-700 dark:text-gray-300">
                                    {{ strtoupper($booking->customer?->identity_type ?? 'KTP') }}: {{ $booking->customer?->identity_number ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div>
                                <span class="text-gray-400">Unit Kamar:</span>
                                <div class="font-bold text-brand-600 dark:text-brand-400 text-sm">
                                    Kamar {{ $booking->room?->room_number ?? '-' }} ({{ $booking->room?->roomType?->name ?? '-' }})
                                </div>
                            </div>
                            <div>
                                <span class="text-gray-400">Periode Menginap:</span>
                                <div class="font-medium text-gray-700 dark:text-gray-300">
                                    {{ $booking->check_in_date->format('d M Y') }} s.d. {{ $booking->check_out_date->format('d M Y') }}
                                    <span class="font-bold text-brand-600">({{ $folio['total_nights'] }} Malam)</span>
                                </div>
                            </div>
                            <div>
                                <span class="text-gray-400">Status Menginap:</span>
                                <div class="font-medium text-gray-700 dark:text-gray-300">
                                    {{ $booking->status_badge['label'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Itemized Breakdown Table -->
                <x-ui.card class="p-6">
                    <div class="border-b border-gray-100 pb-3 dark:border-gray-800">
                        <h2 class="text-base font-bold text-gray-800 dark:text-white">
                            Rincian Item Tagihan Folio
                        </h2>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-start text-xs">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                    <th class="pb-2 font-medium text-start">Deskripsi Item / Layanan</th>
                                    <th class="pb-2 font-medium text-center">Tipe Biaya</th>
                                    <th class="pb-2 font-medium text-center">Qty</th>
                                    <th class="pb-2 font-medium text-end">Tarif Satuan</th>
                                    <th class="pb-2 font-medium text-end">Diskon / Inklusi</th>
                                    <th class="pb-2 font-medium text-end">Subtotal Tagihan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <!-- 1. Room Charges -->
                                <tr class="bg-gray-50/50 dark:bg-gray-800/20">
                                    <td class="py-3">
                                        <div class="font-bold text-gray-800 dark:text-white">
                                            Sewa Kamar {{ $booking->room?->room_number }} ({{ $booking->room?->roomType?->name }})
                                        </div>
                                        <div class="text-[11px] text-gray-400">
                                            Tarif dasar per malam: Rp {{ number_format($folio['room_price_snapshot'], 0, ',', '.') }}
                                        </div>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="rounded bg-brand-50 px-2 py-0.5 text-[10px] font-semibold text-brand-700 dark:bg-brand-900/30 dark:text-brand-400">
                                            Room Charge
                                        </span>
                                    </td>
                                    <td class="py-3 text-center font-medium">{{ $folio['total_nights'] }} Malam</td>
                                    <td class="py-3 text-end text-gray-600 dark:text-gray-300">
                                        Rp {{ number_format($folio['room_price_snapshot'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 text-end text-gray-400">-</td>
                                    <td class="py-3 text-end font-bold text-gray-800 dark:text-white">
                                        Rp {{ number_format($folio['total_room_charge'], 0, ',', '.') }}
                                    </td>
                                </tr>

                                <!-- 2. Booking Items (Mini-POS, Inclusions, Services) -->
                                @foreach ($folio['all_items'] as $item)
                                    @php
                                        $isCancelled = $item->status === 'cancelled';
                                    @endphp
                                    <tr class="{{ $isCancelled ? 'opacity-40 line-through' : '' }}">
                                        <td class="py-3">
                                            <div class="font-medium text-gray-800 dark:text-white">
                                                {{ $item->item_name }}
                                                @if ($item->is_free_inclusion)
                                                    <span class="ms-1 rounded bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                                        Paket Gratis
                                                    </span>
                                                @endif
                                            </div>
                                            @if ($item->notes)
                                                <div class="text-[11px] text-gray-400">{{ $item->notes }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3 text-center">
                                            @if ($item->billing_type === 'charged_to_room')
                                                <span class="rounded bg-blue-50 px-1.5 py-0.5 text-[10px] font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                                    Beban Kamar
                                                </span>
                                            @else
                                                <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                    Bayar Langsung
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-center">{{ $item->quantity }}</td>
                                        <td class="py-3 text-end text-gray-600 dark:text-gray-300">
                                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3 text-end text-emerald-600 dark:text-emerald-400">
                                            @if ($item->discount_or_allowance > 0)
                                                - Rp {{ number_format($item->discount_or_allowance, 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-3 text-end font-semibold text-gray-800 dark:text-white">
                                            @if ($isCancelled)
                                                <span class="text-rose-500 font-normal">[Dibatalkan]</span>
                                            @else
                                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>

                <!-- Settlement & Payment History Log -->
                @if ($booking->notes)
                    <x-ui.card class="p-6">
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white border-b border-gray-100 pb-2 dark:border-gray-800">
                            Riwayat Catatan &amp; Log Pembayaran
                        </h3>
                        <pre class="mt-3 whitespace-pre-wrap font-sans text-xs text-gray-600 dark:text-gray-300 leading-relaxed">{{ $booking->notes }}</pre>
                    </x-ui.card>
                @endif

            </div>

            <!-- Right Col (1 span): Settlement Action & Financial Summary -->
            <div class="space-y-6">

                <!-- Financial Calculation Card -->
                <x-ui.card class="p-6 space-y-4">
                    <h2 class="text-base font-bold text-gray-800 dark:text-white border-b border-gray-100 pb-3 dark:border-gray-800">
                        Kalkulasi Tagihan Folio
                    </h2>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between text-gray-600 dark:text-gray-300">
                            <span>Sewa Kamar ({{ $folio['total_nights'] }} Malam):</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                Rp {{ number_format($folio['total_room_charge'], 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-300">
                            <span>Pesanan Extra (Beban Kamar):</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                Rp {{ number_format($folio['total_charged_to_room'], 0, ',', '.') }}
                            </span>
                        </div>
                        @if ($folio['total_paid_instantly'] > 0)
                            <div class="flex justify-between text-gray-500 dark:text-gray-400">
                                <span>Pesanan Bayar Langsung:</span>
                                <span>Rp {{ number_format($folio['total_paid_instantly'], 0, ',', '.') }} (Lunas)</span>
                            </div>
                        @endif
                        @if ($folio['total_discounts_or_allowances'] > 0)
                            <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                                <span>Nilai Subsidi / Gratis Paket:</span>
                                <span>Rp {{ number_format($folio['total_discounts_or_allowances'], 0, ',', '.') }}</span>
                            </div>
                        @endif

                        <div class="border-t border-gray-100 pt-2.5 dark:border-gray-800 flex justify-between font-bold text-sm text-gray-800 dark:text-white">
                            <span>Grand Total Tagihan:</span>
                            <span class="text-brand-600 dark:text-brand-400">
                                Rp {{ number_format($folio['gross_total'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-3 dark:border-gray-800 space-y-2 text-xs">
                        <div class="flex justify-between text-gray-600 dark:text-gray-300">
                            <span>Uang Jaminan / Deposit Kunci:</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                - Rp {{ number_format($folio['deposit_amount'], 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-300">
                            <span>Status Folio Pembayaran:</span>
                            <span class="capitalize font-bold text-gray-800 dark:text-white">
                                {{ $folio['payment_status'] }}
                            </span>
                        </div>
                    </div>

                    <!-- Balance Due Highlight -->
                    <div class="rounded-xl {{ $folio['balance_due'] > 0 ? 'bg-rose-50/80 border-rose-200 dark:bg-rose-950/20 dark:border-rose-900/40 text-rose-700 dark:text-rose-400' : 'bg-emerald-50/80 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-400' }} border p-4">
                        <div class="text-[11px] font-semibold uppercase">
                            {{ $folio['balance_due'] > 0 ? 'Sisa Tagihan Pelunasan' : 'Status Tagihan' }}
                        </div>
                        <div class="mt-1 text-2xl font-black">
                            @if ($folio['balance_due'] > 0)
                                Rp {{ number_format($folio['balance_due'], 0, ',', '.') }}
                            @else
                                Rp 0 (LUNAS)
                            @endif
                        </div>
                        @if ($folio['refund_due'] > 0)
                            <div class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                                Lebih bayar / Deposit dikembalikan: <strong>Rp {{ number_format($folio['refund_due'], 0, ',', '.') }}</strong>
                            </div>
                        @endif
                    </div>
                </x-ui.card>

                <!-- Settlement Form Card -->
                <x-ui.card class="p-6 space-y-4">
                    <h2 class="text-base font-bold text-gray-800 dark:text-white border-b border-gray-100 pb-3 dark:border-gray-800">
                        Proses Kasir / Settlement
                    </h2>

                    @if ($folio['balance_due'] <= 0 && $booking->status === 'checked_out')
                        <div class="rounded-xl bg-emerald-50 p-4 text-xs text-emerald-800 dark:bg-emerald-950/20 dark:text-emerald-400 space-y-1">
                            <div class="font-bold flex items-center gap-1.5">
                                <x-svg.check class="size-4" />
                                <span>Tagihan Telah Selesai &amp; Lunas</span>
                            </div>
                            <p>Tamu telah menyelesaikan seluruh tagihan dan telah Check-Out dari homestay.</p>
                        </div>
                    @elseif ($folio['balance_due'] <= 0)
                        <div class="rounded-xl bg-emerald-50 p-4 text-xs text-emerald-800 dark:bg-emerald-950/20 dark:text-emerald-400 space-y-2">
                            <div class="font-bold flex items-center gap-1.5">
                                <x-svg.check class="size-4" />
                                <span>Tagihan Sudah Lunas Penuh</span>
                            </div>
                            <p>Uang muka / pembayaran telah menutup 100% biaya tagihan. Tidak ada sisa tagihan yang perlu ditagih ke tamu saat ini.</p>
                            @if ($booking->status === 'checked_in')
                                <div class="pt-2">
                                    <form action="{{ route('bookings.check-out', $booking) }}" method="POST">
                                        @csrf
                                        <x-ui.button type="submit" size="sm" class="bg-emerald-600 hover:bg-emerald-700 text-white">
                                            Selesaikan Check-Out Tamu Sekarang
                                        </x-ui.button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @else
                        <form action="{{ route('billing.settle', $booking) }}" method="POST" class="space-y-4 text-xs">
                            @csrf

                            <x-ui.field>
                                <x-ui.field.label for="settle-amount">
                                    Nominal Pembayaran (Rp)
                                </x-ui.field.label>
                                <x-ui.input id="settle-amount" type="number" step="1000" min="0" name="amount_paid" x-model="amountPaid" required />
                            </x-ui.field>

                            <x-ui.field>
                                <x-ui.field.label for="settle-method">
                                    Metode Pembayaran
                                </x-ui.field.label>
                                <select id="settle-method" name="payment_method" x-model="paymentMethod" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white" required>
                                    <option value="cash">Tunai (Cash)</option>
                                    <option value="qris">QRIS / E-Wallet</option>
                                    <option value="transfer">Transfer Bank</option>
                                    <option value="card">Kartu Debit / Kredit</option>
                                    <option value="deposit_offset">Potong Deposit</option>
                                </select>
                            </x-ui.field>

                            @if ($booking->status === 'checked_in')
                                <div class="rounded-xl border border-brand-200 bg-brand-50/60 p-3 dark:border-brand-900/40 dark:bg-brand-950/20">
                                    <label class="flex items-start gap-2.5 cursor-pointer">
                                        <input type="checkbox" name="auto_checkout" value="1" x-model="autoCheckout" class="mt-0.5 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                        <div>
                                            <span class="font-semibold text-gray-800 dark:text-white">Check-Out Tamu Sekaligus</span>
                                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                                Status kamar {{ $booking->room?->room_number }} akan otomatis berubah menjadi <strong>Dirty</strong> untuk dibersihkan.
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            @endif

                            <x-ui.field>
                                <x-ui.field.label for="settle-notes">
                                    Keterangan / Memo Kasir (Opsional)
                                </x-ui.field.label>
                                <x-ui.textarea id="settle-notes" name="notes" rows="2" placeholder="Catatan bukti transfer atau nama kasir..." />
                            </x-ui.field>

                            <x-ui.button type="submit" variant="primary" class="w-full justify-center py-2.5">
                                Catat Pelunasan Tagihan
                            </x-ui.button>
                        </form>
                    @endif
                </x-ui.card>

            </div>

        </div>

    </div>
@endsection

