<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $booking->booking_code }} - TailAdmin Homestay</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>

<body class="bg-gray-100 font-outfit text-gray-800 antialiased">

    <!-- Top Action Bar for screen view -->
    <div class="no-print bg-white border-b border-gray-200 py-3 px-6 shadow-sm sticky top-0 z-50">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('billing.show', $booking) }}"
                    class="text-xs font-semibold text-gray-600 hover:text-gray-900 flex items-center gap-1">
                    &larr; Kembali ke Folio
                </a>
                <span class="text-gray-300">|</span>
                <span class="text-xs text-gray-500">Invoice Reservasi #{{ $booking->booking_code }}</span>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.print()"
                    class="rounded-lg bg-brand-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-brand-700 shadow-sm flex items-center gap-1.5">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak / Simpan PDF (Ctrl+P)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Invoice Paper Container -->
    <div
        class="max-w-4xl mx-auto my-8 p-8 sm:p-12 bg-white rounded-2xl shadow-md border border-gray-100 print:border-none print:shadow-none print:my-0 print:p-0">

        <!-- Invoice Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 border-b border-gray-200 pb-8">
            <div>
                <div class="flex items-center gap-2">
                    <div
                        class="flex size-9 items-center justify-center rounded-xl bg-brand-600 text-white font-black text-lg">
                        T
                    </div>
                    <span class="text-xl font-black tracking-tight text-gray-900">Prima Homestay</span>
                </div>
                <p class="mt-2 text-xs text-gray-500 leading-relaxed">
                    Jl. Pariwisata Utama No. 88, Kawasan Homestay &amp; Resort<br>
                    Telepon: +62 812-3456-7890 &bull; Email: stay@prima-homestay.test<br>
                    Website: https://prima-homestay.test
                </p>
            </div>
            <div class="sm:text-right">
                <div class="text-xs uppercase font-bold tracking-wider text-gray-400">FAKTUR / INVOICE RESMI</div>
                <div class="text-2xl font-black text-brand-600 mt-1">#{{ $booking->booking_code }}</div>
                <div class="text-xs text-gray-500 mt-1">
                    Tanggal: {{ now()->format('d F Y') }}
                </div>
                <div class="mt-2 inline-block">
                    @if ($folio['balance_due'] <= 0 && ($booking->payment_status === 'paid' || $folio['payment_status'] === 'paid'))
                        <span
                            class="rounded-md border border-emerald-300 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 tracking-wider">
                            LUNAS / PAID
                        </span>
                    @elseif ($folio['payment_status'] === 'partial' || $booking->payment_status === 'partial')
                        <span
                            class="rounded-md border border-amber-300 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 tracking-wider">
                            PARTIAL (DP)
                        </span>
                    @else
                        <span
                            class="rounded-md border border-rose-300 bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 tracking-wider">
                            BELUM LUNAS / UNPAID
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Guest & Booking Info Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-gray-200 text-xs">
            <div>
                <div class="font-bold text-gray-400 uppercase tracking-wider text-[10px]">TAGIHAN KEPADA (TAMU)</div>
                <div class="text-sm font-bold text-gray-900 mt-1">{{ $booking->customer?->full_name ?? '-' }}</div>
                <div class="text-gray-600 mt-0.5">No. Telepon: {{ $booking->customer?->phone_number ?? '-' }}</div>
                <div class="text-gray-600">
                    Identitas ({{ strtoupper($booking->customer?->identity_type ?? 'KTP') }}):
                    {{ $booking->customer?->identity_number ?? '-' }}
                </div>
                @if ($booking->customer?->address)
                    <div class="text-gray-500 mt-0.5">{{ $booking->customer->address }}</div>
                @endif
            </div>

            <div class="sm:text-right">
                <div class="font-bold text-gray-400 uppercase tracking-wider text-[10px]">RINCIAN RESERVASI</div>
                <div class="text-sm font-bold text-gray-900 mt-1">
                    Kamar {{ $booking->room?->room_number ?? '-' }} &bull;
                    {{ $booking->room?->roomType?->name ?? '-' }}
                </div>
                <div class="text-gray-600 mt-0.5">
                    Check-In: {{ $booking->check_in_date->format('d M Y, H:i') }} WIB
                </div>
                <div class="text-gray-600">
                    Check-Out: {{ $booking->check_out_date->format('d M Y, H:i') }} WIB
                </div>
                <div class="font-semibold text-brand-600 mt-0.5">
                    Total Menginap: {{ $folio['total_nights'] }} Malam
                </div>
            </div>
        </div>

        <!-- Itemized Table -->
        <div class="py-6">
            <table class="w-full text-start text-xs">
                <thead>
                    <tr class="border-b border-gray-300 text-gray-600">
                        <th class="py-2.5 text-start font-bold">DESKRIPSI ITEM / LAYANAN</th>
                        <th class="py-2.5 text-center font-bold">QTY</th>
                        <th class="py-2.5 text-end font-bold">TARIF SATUAN</th>
                        <th class="py-2.5 text-end font-bold">DISKON / INKLUSI</th>
                        <th class="py-2.5 text-end font-bold">TOTAL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <!-- Room Charge -->
                    <tr>
                        <td class="py-3">
                            <div class="font-bold text-gray-900">
                                Sewa Kamar {{ $booking->room?->room_number }} ({{ $booking->room?->roomType?->name }})
                            </div>
                            <div class="text-[11px] text-gray-500">
                                Tarif @ Rp {{ number_format($folio['room_price_snapshot'], 0, ',', '.') }} x
                                {{ $folio['total_nights'] }} malam
                            </div>
                        </td>
                        <td class="py-3 text-center">{{ $folio['total_nights'] }} Malam</td>
                        <td class="py-3 text-end">Rp {{ number_format($folio['room_price_snapshot'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 text-end text-gray-400">-</td>
                        <td class="py-3 text-end font-bold text-gray-900">
                            Rp {{ number_format($folio['total_room_charge'], 0, ',', '.') }}
                        </td>
                    </tr>

                    <!-- Additional Orders / Inclusions -->
                    @foreach ($folio['all_items'] as $item)
                        @if ($item->status !== 'cancelled')
                            <tr>
                                <td class="py-3">
                                    <div class="font-medium text-gray-800">
                                        {{ $item->item_name }}
                                        @if ($item->is_free_inclusion)
                                            <span class="text-[10px] text-emerald-600 font-semibold">(Inklusi
                                                Gratis)</span>
                                        @endif
                                        @if ($item->billing_type === 'paid_instantly')
                                            <span class="text-[10px] text-gray-500 font-medium">(Lunas Ditempat)</span>
                                        @endif
                                    </div>
                                    @if ($item->notes)
                                        <div class="text-[10px] text-gray-400">{{ $item->notes }}</div>
                                    @endif
                                </td>
                                <td class="py-3 text-center">{{ $item->quantity }}</td>
                                <td class="py-3 text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="py-3 text-end text-emerald-600">
                                    @if ($item->discount_or_allowance > 0)
                                        - Rp {{ number_format($item->discount_or_allowance, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3 text-end font-semibold text-gray-900">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Calculation -->
        <div class="border-t border-gray-200 pt-4 flex flex-col sm:flex-row justify-between items-start gap-6 text-xs">
            <div class="space-y-1 text-gray-500 max-w-sm">
                <p><strong>Catatan Pembayaran:</strong></p>
                <p>Terima kasih telah menginap di TailAdmin Homestay. Simpan invoice ini sebagai bukti sah pembayaran.
                </p>
                @if ($booking->notes)
                    <div class="mt-2 rounded bg-gray-50 p-2 text-[11px] text-gray-600 border border-gray-200">
                        {{ $booking->notes }}
                    </div>
                @endif
            </div>

            <div class="w-full sm:w-72 space-y-2">
                <div class="flex justify-between text-gray-600">
                    <span>Total Sewa Kamar:</span>
                    <span>Rp {{ number_format($folio['total_room_charge'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Layanan Tambahan (Beban Kamar):</span>
                    <span>Rp {{ number_format($folio['total_charged_to_room'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between font-bold text-sm text-gray-900 border-t border-gray-200 pt-2">
                    <span>Grand Total:</span>
                    <span class="text-brand-600">Rp {{ number_format($folio['gross_total'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-emerald-700 font-medium">
                    <span>Uang Jaminan / Deposit:</span>
                    <span>- Rp {{ number_format($folio['deposit_amount'], 0, ',', '.') }}</span>
                </div>
                <div
                    class="flex justify-between font-black text-base {{ $folio['balance_due'] > 0 ? 'text-rose-600' : 'text-emerald-600' }} border-t-2 border-gray-300 pt-2">
                    <span>{{ $folio['balance_due'] > 0 ? 'SISA TAGIHAN:' : 'TAGIHAN LUNAS:' }}</span>
                    <span>Rp {{ number_format($folio['balance_due'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Signature Footer -->
        <div class="mt-12 pt-8 border-t border-gray-200 grid grid-cols-2 text-center text-xs">
            <div>
                <p class="text-gray-400">Tanda Tangan Tamu,</p>
                <div class="h-16"></div>
                <p class="font-bold text-gray-800">{{ $booking->customer?->full_name ?? 'Tamu' }}</p>
            </div>
            <div>
                <p class="text-gray-400">Front Desk / Kasir,</p>
                <div class="h-16"></div>
                <p class="font-bold text-gray-800">{{ auth()->user()?->name ?? 'Front Desk Staff' }}</p>
            </div>
        </div>

    </div>

</body>

</html>
