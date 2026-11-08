@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Top Bar with Welcome & Quick Action Buttons -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-title-md font-bold text-gray-800 dark:text-white/90">
                    Dashboard Operasional Homestay
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Ringkasan okupansi kamar, jadwal tamu, dan layanan hari ini &middot; {{ date('d M Y') }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                {{-- <a href="/calendar"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    <x-svg.calendar class="size-4" />
                    Kalender Okupansi
                </a> --}}
                <button type="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    + Reservasi Baru
                </button>
            </div>
        </div>

        <!-- 4 Top Metric Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-4">
            <!-- 1. Occupied Rooms -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-white/3">
                <div class="flex items-center justify-between">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                    <span
                        class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-500/15 dark:text-blue-400">
                        Occupied
                    </span>
                </div>
                <div class="mt-4">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ $metrics['occupied_rooms']['title'] }}
                    </span>
                    <h3 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ $metrics['occupied_rooms']['value'] }}
                    </h3>
                    <p class="mt-2 text-xs font-medium text-blue-600 dark:text-blue-400">
                        {{ $metrics['occupied_rooms']['change'] }}
                    </p>
                </div>
            </div>

            <!-- 2. Today Check-Ins -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-white/3">
                <div class="flex items-center justify-between">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    <span
                        class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">
                        Active
                    </span>
                </div>
                <div class="mt-4">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ $metrics['today_checkins']['title'] }}
                    </span>
                    <h3 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ $metrics['today_checkins']['value'] }}
                    </h3>
                    <p class="mt-2 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                        {{ $metrics['today_checkins']['change'] }}
                    </p>
                </div>
            </div>

            <!-- 3. Dirty / Housekeeping -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-white/3">
                <div class="flex items-center justify-between">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-500 dark:bg-amber-500/15 dark:text-amber-400">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <span
                        class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">
                        Housekeeping
                    </span>
                </div>
                <div class="mt-4">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ $metrics['dirty_rooms']['title'] }}
                    </span>
                    <h3 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ $metrics['dirty_rooms']['value'] }}
                    </h3>
                    <p class="mt-2 text-xs font-medium text-amber-600 dark:text-amber-400">
                        {{ $metrics['dirty_rooms']['change'] }}
                    </p>
                </div>
            </div>

            <!-- 4. Daily Revenue -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-white/3">
                <div class="flex items-center justify-between">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span
                        class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">
                        {{ $metrics['daily_revenue']['change'] }}
                    </span>
                </div>
                <div class="mt-4">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ $metrics['daily_revenue']['title'] }}
                    </span>
                    <h3 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                        Rp {{ number_format($metrics['daily_revenue']['value'], 0, ',', '.') }}
                    </h3>
                    <p class="mt-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                        Kamar &amp; Mini-POS Terakumulasi
                    </p>
                </div>
            </div>
        </div>

        <!-- Room Status Summary Row -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">
            <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-3">
                Distribusi Status Unit Kamar Saat Ini
            </h4>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ($roomStatuses as $item)
                    <div
                        class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50/50 p-3 dark:border-gray-800/80 dark:bg-white/5">
                        <div class="flex items-center gap-2">
                            <span class="size-2.5 rounded-full {{ $item['dot'] }}"></span>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $item['name'] }}</span>
                        </div>
                        <span class="rounded-md px-2 py-0.5 text-xs font-bold {{ $item['badge_class'] }}">
                            {{ $item['count'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Two Columns: Today Check-Ins & Today Check-Outs -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <!-- Left: Today's Expected Check-Ins (7 Cols) -->
            <div class="lg:col-span-7">
                <div
                    class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-emerald-500"></div>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                Tamu Check-In Hari Ini
                            </h3>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ count($todayArrivals) }} Tamu Terjadwal
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-start text-sm">
                            <thead
                                class="border-b border-gray-100 bg-gray-50 text-xs text-gray-500 uppercase dark:border-gray-800 dark:bg-white/5 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3 font-medium text-start">Tamu / Booking</th>
                                    <th class="px-4 py-3 font-medium text-start">Kamar</th>
                                    <th class="px-4 py-3 font-medium text-start">Status</th>
                                    <th class="px-4 py-3 font-medium text-start">Pembayaran</th>
                                    <th class="px-6 py-3 font-medium text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse ($todayArrivals as $arrival)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/2">
                                        <td class="px-6 py-4">
                                            <div class="font-semibold text-gray-800 dark:text-white/90">
                                                {{ $arrival['guest_name'] }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                                                {{ $arrival['booking_code'] }} &middot; {{ $arrival['phone'] }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="font-medium text-gray-800 dark:text-white/90">
                                                {{ $arrival['room_number'] }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $arrival['room_type'] }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @if ($arrival['status'] === 'checked_in')
                                                <span
                                                    class="inline-flex rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-500/15 dark:text-blue-400">
                                                    {{ $arrival['status_label'] }}
                                                </span>
                                            @elseif ($arrival['status'] === 'confirmed')
                                                <span
                                                    class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">
                                                    {{ $arrival['status_label'] }}
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">
                                                    {{ $arrival['status_label'] }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @if ($arrival['payment_status'] === 'paid')
                                                <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                                    {{ $arrival['payment_label'] }}
                                                </span>
                                            @elseif ($arrival['payment_status'] === 'partial')
                                                <span class="text-xs font-medium text-amber-600 dark:text-amber-400">
                                                    {{ $arrival['payment_label'] }}
                                                </span>
                                            @else
                                                <span class="text-xs font-medium text-rose-600 dark:text-rose-400">
                                                    {{ $arrival['payment_label'] }}
                                                </span>
                                            @endif
                                            <div class="text-xs text-gray-400">
                                                Rp {{ number_format($arrival['total_amount'], 0, ',', '.') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-end whitespace-nowrap">
                                            @if ($arrival['status'] !== 'checked_in')
                                                <button type="button"
                                                    class="inline-flex items-center rounded-md bg-emerald-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-emerald-700">
                                                    Check-In
                                                </button>
                                            @else
                                                <span class="text-xs text-gray-400">In-House</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                            Tidak ada jadwal kedatangan tamu hari ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Today's Check-Outs (5 Cols) -->
            <div class="lg:col-span-5">
                <div
                    class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-rose-500"></div>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                Tamu Check-Out Hari Ini
                            </h3>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ count($todayDepartures) }} Kamar
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-start text-sm">
                            <thead
                                class="border-b border-gray-100 bg-gray-50 text-xs text-gray-500 uppercase dark:border-gray-800 dark:bg-white/5 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3 font-medium text-start">Tamu / Kamar</th>
                                    <th class="px-4 py-3 font-medium text-start">Status</th>
                                    <th class="px-6 py-3 font-medium text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse ($todayDepartures as $departure)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/2">
                                        <td class="px-6 py-4">
                                            <div class="font-semibold text-gray-800 dark:text-white/90">
                                                {{ $departure['guest_name'] }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $departure['room_number'] }} &middot;
                                                {{ $departure['scheduled_time'] }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @if ($departure['status'] === 'checked_out')
                                                <span
                                                    class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                                    {{ $departure['status_label'] }}
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">
                                                    {{ $departure['status_label'] }}
                                                </span>
                                            @endif
                                            @if ($departure['room_status'] === 'dirty')
                                                <div
                                                    class="mt-1 text-[11px] font-medium text-amber-600 dark:text-amber-400">
                                                    Perlu Housekeeping
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-end whitespace-nowrap">
                                            @if ($departure['status'] !== 'checked_out')
                                                <button type="button"
                                                    class="inline-flex items-center rounded-md bg-rose-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-rose-700">
                                                    Check-Out
                                                </button>
                                            @else
                                                <span
                                                    class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Selesai</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                            Tidak ada jadwal check-out hari ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
