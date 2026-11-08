@extends('layouts.app')

@section('content')
    <div x-data="{
        preset: '{{ $preset }}',
        startDate: '{{ $startDate }}',
        endDate: '{{ $endDate }}',
        setPreset(p) {
            const rt = document.querySelector('select[name=room_type_id]')?.value || '';
            const param = rt ? `&room_type_id=${encodeURIComponent(rt)}` : '';
            window.location.href = `{{ route('reports.index') }}?preset=${p}${param}`;
        }
    }" class="space-y-6">

        <x-common.page-breadcrumb pageTitle="Laporan Rekapitulasi Finansial & Operasional" />

        <!-- Top Header & Export/Print Actions -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between no-print">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Rekap pendapatan kamar, penjualan F&amp;B/layanan, tingkat okupansi, dan arus kas reservasi berdasarkan rentang tanggal.
                </p>
                <div class="mt-1 flex items-center gap-2 text-xs font-semibold text-brand-600 dark:text-brand-400">
                    <x-svg.calendar class="size-3.5" />
                    <span>Periode Aktif: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s.d. {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }} ({{ $metrics['days_count'] }} Hari)</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <x-ui.button as="a" href="{{ route('reports.export-csv', request()->query()) }}" variant="outline" size="sm">
                    <x-svg.download class="size-4" />
                    <span>Export CSV</span>
                </x-ui.button>
                <x-ui.button type="button" onclick="window.print()" variant="primary" size="sm">
                    <x-svg.printer class="size-4" />
                    <span>Cetak / PDF</span>
                </x-ui.button>
            </div>
        </div>

        <!-- Filter Bar Card -->
        <x-ui.card class="p-5 no-print">
            <form x-ref="filterForm" method="GET" action="{{ route('reports.index') }}" class="space-y-4">
                <!-- Preset Quick Buttons -->
                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-400">Pilihan Cepat Rentang Waktu:</label>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <button type="button" @click="setPreset('today')"
                            :class="preset === 'today' ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition">
                            Hari Ini
                        </button>
                        <button type="button" @click="setPreset('last_7_days')"
                            :class="preset === 'last_7_days' ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition">
                            7 Hari Terakhir
                        </button>
                        <button type="button" @click="setPreset('this_month')"
                            :class="preset === 'this_month' ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition">
                            Bulan Ini
                        </button>
                        <button type="button" @click="setPreset('last_30_days')"
                            :class="preset === 'last_30_days' ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition">
                            30 Hari Terakhir
                        </button>
                        <button type="button" @click="setPreset('last_month')"
                            :class="preset === 'last_month' ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition">
                            Bulan Lalu
                        </button>
                        <button type="button" @click="setPreset('this_year')"
                            :class="preset === 'this_year' ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition">
                            Tahun Ini
                        </button>
                    </div>
                </div>

                <input type="hidden" name="preset" value="custom">

                <!-- Custom Range Datepicker and Tipe Kamar Inputs -->
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 pt-2 border-t border-gray-100 dark:border-gray-800">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Dari Tanggal (Start Date):</label>
                        <input type="date" name="start_date" x-model="startDate"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white" required />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Sampai Tanggal (End Date):</label>
                        <input type="date" name="end_date" x-model="endDate"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white" required />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Filter Tipe Kamar:</label>
                        <select name="room_type_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Semua Tipe Kamar</option>
                            @foreach ($roomTypes as $rt)
                                <option value="{{ $rt->id }}" {{ (string)$selectedRoomTypeId === (string)$rt->id ? 'selected' : '' }}>
                                    {{ $rt->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <x-ui.button type="submit" variant="primary" class="flex-1 justify-center py-2 text-xs font-semibold whitespace-nowrap" size="sm">
                            <x-svg.search class="size-3.5 me-1" />
                            <span>Terapkan</span>
                        </x-ui.button>
                        <x-ui.button as="a" href="{{ route('reports.index') }}" variant="outline" size="sm" class="shrink-0 justify-center py-2 px-3 text-xs font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                            Reset
                        </x-ui.button>
                    </div>
                </div>
            </form>
        </x-ui.card>

        <!-- KPI Metrics Grid (6 Cards) -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <!-- 1. Total Gross Revenue -->
            <x-ui.card class="p-4 border-l-4 border-l-brand-500">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Total Omzet Bruto</p>
                <h3 class="mt-1.5 text-lg font-black text-gray-800 dark:text-white">
                    Rp {{ number_format($metrics['gross_revenue'], 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-[11px] text-gray-500">Kamar + F&amp;B + Layanan</p>
            </x-ui.card>

            <!-- 2. Room Revenue -->
            <x-ui.card class="p-4 border-l-4 border-l-blue-500">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Sewa Kamar</p>
                <h3 class="mt-1.5 text-lg font-black text-blue-600 dark:text-blue-400">
                    Rp {{ number_format($metrics['total_room_revenue'], 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-[11px] text-gray-500">{{ $metrics['total_nights_sold'] }} Malam Terjual</p>
            </x-ui.card>

            <!-- 3. POS & Services Revenue -->
            <x-ui.card class="p-4 border-l-4 border-l-purple-500">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Layanan &amp; Mini-POS</p>
                <h3 class="mt-1.5 text-lg font-black text-purple-600 dark:text-purple-400">
                    Rp {{ number_format($metrics['total_service_revenue'] + $metrics['total_paid_instantly'], 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-[11px] text-gray-500">Beban Kamar &amp; Langsung</p>
            </x-ui.card>

            <!-- 4. Deposits Held -->
            <x-ui.card class="p-4 border-l-4 border-l-emerald-500">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Deposit / Uang Jaminan</p>
                <h3 class="mt-1.5 text-lg font-black text-emerald-600 dark:text-emerald-400">
                    Rp {{ number_format($metrics['total_deposits'], 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-[11px] text-gray-500">Disetor Tamu Saat Check-in</p>
            </x-ui.card>

            <!-- 5. Outstanding Receivables -->
            <x-ui.card class="p-4 border-l-4 border-l-rose-500">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Sisa Piutang Tagihan</p>
                <h3 class="mt-1.5 text-lg font-black text-rose-600 dark:text-rose-400">
                    Rp {{ number_format($metrics['total_balance_due'], 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-[11px] text-gray-500">Belum Diselesaikan Kasir</p>
            </x-ui.card>

            <!-- 6. Occupancy Rate & ADR -->
            <x-ui.card class="p-4 border-l-4 border-l-amber-500">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Tingkat Okupansi</p>
                <h3 class="mt-1.5 text-lg font-black text-amber-600 dark:text-amber-400">
                    {{ $metrics['occupancy_rate'] }}%
                </h3>
                <p class="mt-1 text-[11px] text-gray-500">ADR: Rp {{ number_format($metrics['adr'], 0, ',', '.') }}</p>
            </x-ui.card>
        </div>

        <!-- Service Category Breakdown Grid -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($categoryBreakdown as $catKey => $cat)
                <x-ui.card class="p-3.5 bg-gray-50/60 dark:bg-gray-800/40">
                    <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 truncate">{{ $cat['label'] }}</p>
                    <div class="mt-1 text-sm font-bold text-gray-800 dark:text-white">
                        Rp {{ number_format($cat['total'], 0, ',', '.') }}
                    </div>
                    <div class="mt-0.5 text-[10px] text-gray-400">
                        {{ $cat['qty'] }} item terjual
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <!-- Left Col (1 span): Daily Aggregated Recap Table -->
            <div class="space-y-4 lg:col-span-1">
                <x-ui.card class="p-5">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white">
                            Rekapitulasi Omzet Harian
                        </h3>
                        <span class="text-xs text-gray-400">{{ count($dailySummary) }} Tanggal</span>
                    </div>

                    <div class="mt-3 max-h-[460px] overflow-y-auto overflow-x-auto text-xs">
                        <table class="w-full text-start">
                            <thead class="sticky top-0 bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 text-gray-400">
                                <tr>
                                    <th class="py-2 text-start font-medium">Tanggal</th>
                                    <th class="py-2 text-center font-medium">In/Out</th>
                                    <th class="py-2 text-end font-medium">Total Omzet</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse ($dailySummary as $ds)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                        <td class="py-2.5">
                                            <div class="font-medium text-gray-800 dark:text-white">
                                                {{ \Carbon\Carbon::parse($ds['date'])->format('d M Y') }}
                                            </div>
                                            <div class="text-[10px] text-gray-400">
                                                {{ \Carbon\Carbon::parse($ds['date'])->translatedFormat('l') }}
                                            </div>
                                        </td>
                                        <td class="py-2.5 text-center text-[11px]">
                                            <span class="text-emerald-600 font-semibold">{{ $ds['checkins_count'] }}</span>
                                            <span class="text-gray-300">/</span>
                                            <span class="text-amber-600 font-semibold">{{ $ds['checkouts_count'] }}</span>
                                        </td>
                                        <td class="py-2.5 text-end font-bold text-gray-800 dark:text-white">
                                            @if ($ds['total_daily'] > 0)
                                                Rp {{ number_format($ds['total_daily'], 0, ',', '.') }}
                                            @else
                                                <span class="text-gray-300 dark:text-gray-600">Rp 0</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-gray-400">Tidak ada tanggal dalam filter.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>
            </div>

            <!-- Right Col (2 spans): Filtered Reservations Table -->
            <div class="space-y-4 lg:col-span-2">
                <x-ui.card class="p-5">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">
                                Rincian Transaksi Reservasi dalam Periode
                            </h3>
                            <p class="text-xs text-gray-500">Daftar booking yang memiliki jadwal menginap pada rentang tanggal ini.</p>
                        </div>
                        <span class="rounded-lg bg-brand-50 px-2.5 py-1 text-xs font-bold text-brand-600 dark:bg-brand-900/30 dark:text-brand-400">
                            {{ $bookings->count() }} Reservasi
                        </span>
                    </div>

                    <div class="mt-4 overflow-x-auto text-xs">
                        <table class="w-full text-start">
                            <thead class="border-b border-gray-100 bg-gray-50/75 dark:border-gray-800 dark:bg-gray-800/40 text-gray-500 dark:text-gray-400">
                                <tr>
                                    <th class="py-3 ps-3 pe-2 font-medium text-start">Kode &amp; Tamu</th>
                                    <th class="px-2 py-3 font-medium text-start">Kamar</th>
                                    <th class="px-2 py-3 font-medium text-center">Durasi</th>
                                    <th class="px-2 py-3 font-medium text-end">Sewa Kamar</th>
                                    <th class="px-2 py-3 font-medium text-end">Layanan</th>
                                    <th class="px-2 py-3 font-medium text-end">Total Kotor</th>
                                    <th class="px-2 py-3 font-medium text-end">Sisa Tagihan</th>
                                    <th class="py-3 pe-3 ps-2 font-medium text-center">Status</th>
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
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                        <td class="py-3 ps-3 pe-2">
                                            <a href="{{ route('billing.show', $b) }}" class="font-bold text-brand-600 hover:underline">
                                                {{ $b->booking_code }}
                                            </a>
                                            <div class="font-medium text-gray-800 dark:text-white">
                                                {{ $b->customer?->full_name ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="px-2 py-3">
                                            <span class="font-semibold text-gray-800 dark:text-white">Rm {{ $b->room?->room_number }}</span>
                                            <div class="text-[10px] text-gray-400">{{ $b->room?->roomType?->name }}</div>
                                        </td>
                                        <td class="px-2 py-3 text-center">
                                            <span class="font-semibold text-brand-600">{{ $b->total_nights }} Mlm</span>
                                            <div class="text-[10px] text-gray-400">{{ $b->check_in_date->format('d/m') }} - {{ $b->check_out_date->format('d/m') }}</div>
                                        </td>
                                        <td class="px-2 py-3 text-end text-gray-600 dark:text-gray-300">
                                            Rp {{ number_format($roomCharge, 0, ',', '.') }}
                                        </td>
                                        <td class="px-2 py-3 text-end text-gray-600 dark:text-gray-300">
                                            @if ($posCharge > 0)
                                                Rp {{ number_format($posCharge, 0, ',', '.') }}
                                            @else
                                                <span class="text-gray-300">-</span>
                                            @endif
                                        </td>
                                        <td class="px-2 py-3 text-end font-bold text-gray-800 dark:text-white">
                                            Rp {{ number_format($grossTotal, 0, ',', '.') }}
                                        </td>
                                        <td class="px-2 py-3 text-end">
                                            @if ($balanceDue > 0)
                                                <span class="font-bold text-rose-600">Rp {{ number_format($balanceDue, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-emerald-600 font-medium">Lunas</span>
                                            @endif
                                        </td>
                                        <td class="py-3 pe-3 ps-2 text-center">
                                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-medium {{ $b->status_badge['class'] }}">
                                                {{ $b->status_badge['label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-10 text-center text-gray-400">
                                            Tidak ada transaksi reservasi yang ditemukan dalam rentang tanggal ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>
            </div>

        </div>

    </div>
@endsection

