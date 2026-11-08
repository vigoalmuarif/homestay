@extends('layouts.app')

@section('content')
<div x-data="{
    activeTab: 'roster',
    selectedBooking: {
        id: '',
        room_number: '',
        guest_name: '',
        category: 'meal',
        inclusion_title: ''
    },
    fulfillmentFormData: {
        booking_id: '',
        service_id: '',
        quantity: 1,
        category_type: 'meal',
        detail_notes: ''
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
    openFulfillmentModal(booking, category, inclusionName) {
        this.selectedBooking = {
            id: booking.id,
            room_number: booking.room ? booking.room.room_number : '-',
            guest_name: booking.customer ? (booking.customer.full_name || booking.customer.name) : 'Tamu',
            category: category,
            inclusion_title: inclusionName
        };
        this.fulfillmentFormData.booking_id = booking.id;
        this.fulfillmentFormData.category_type = category;
        this.fulfillmentFormData.quantity = 1;
        this.fulfillmentFormData.detail_notes = '';
        this.fulfillmentFormData.service_id = '';

        const form = document.getElementById('fulfillment-form');
        if (form) {
            form.action = '/inclusions/' + booking.id + '/fulfillment-order';
        }

        this.showDialog('fulfillmentDialog');
    }
}" class="space-y-6">

    <!-- Header & Breadcrumb -->
    <x-common.page-breadcrumb pageTitle="Pusat Klaim Inklusi & Fulfillment" />

    <!-- Notice jika melihat tanggal selain hari ini -->
    @if (!$isToday)
        <div class="flex items-center justify-between rounded-xl border border-warning-200 bg-warning-50 px-4 py-2.5 text-xs text-warning-800 dark:border-warning-900/50 dark:bg-warning-950/30 dark:text-warning-300">
            <span class="flex items-center gap-2 font-medium">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Menampilkan data reservasi & klaim tanggal: <strong>{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}</strong>
            </span>
            <x-ui.button as="a" href="{{ route('inclusions.claim-desk') }}" variant="ghost" size="sm" class="text-xs font-semibold text-warning-800 hover:text-warning-900 dark:text-warning-300">
                Kembali ke Hari Ini &rarr;
            </x-ui.button>
        </div>
    @endif

    <!-- Flash Alerts -->
    @if (session('success'))
        <x-ui.alert variant="success" class="border-success-200 bg-success-50 text-success-800 dark:border-success-900/50 dark:bg-success-950/30 dark:text-success-300">
            <x-ui.alert.title class="flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Berhasil
            </x-ui.alert.title>
            <x-ui.alert.description>
                {{ session('success') }}
            </x-ui.alert.description>
        </x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert variant="destructive" class="border-error-200 bg-error-50 text-error-800 dark:border-error-900/50 dark:bg-error-950/30 dark:text-error-300">
            <x-ui.alert.title class="flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Perhatian
            </x-ui.alert.title>
            <x-ui.alert.description>
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert.description>
        </x-ui.alert>
    @endif

    <!-- Metric KPI Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- In-House Berhak -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tamu Berhak Inklusi</span>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['in_house_count'] }} <span class="text-sm font-normal text-gray-500">Kamar</span></h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Total <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $stats['total_entitled'] }}</span> hak fasilitas inklusi
            </p>
        </div>

        <!-- Sudah Klaim Hari Ini -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Sudah Diklaim</span>
                    <h4 class="mt-2 text-2xl font-bold text-success-600 dark:text-success-400">{{ $stats['total_claimed'] }} <span class="text-sm font-normal text-gray-500">Layanan</span></h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Tamu telah hadir / sarapan / mengambil fasilitas
            </p>
        </div>

        <!-- Belum Diklaim -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Belum Hadir / Klaim</span>
                    <h4 class="mt-2 text-2xl font-bold text-warning-500 dark:text-warning-400">{{ $stats['total_unclaimed'] }} <span class="text-sm font-normal text-gray-500">Sisa Kuota</span></h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Menunggu kedatangan tamu di restoran/outlet
            </p>
        </div>

        <!-- Antrean Proses (Dapur, Laundry, Rental) -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Antrean Pemenuhan</span>
                    <h4 class="mt-2 text-2xl font-bold text-blue-light-600 dark:text-blue-light-400">{{ $stats['active_fulfillment'] }} <span class="text-sm font-normal text-gray-500">Tiket Aktif</span></h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Sedang dicuci, dimasak, atau dipinjam
            </p>
        </div>
    </div>

    <!-- Navigation Tabs & Search Controls -->
    <x-common.component-card title="Manajemen Klaim & Antrean Pemenuhan" desc="Pantau kehadiran tamu dan kelola tiket pemenuhan layanan harian.">
        <div class="flex flex-col gap-4 border-b border-gray-200 pb-4 dark:border-gray-800 lg:flex-row lg:items-center lg:justify-between">
            <!-- Tabs Switcher -->
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="activeTab = 'roster'"
                    :class="activeTab === 'roster'
                        ? 'bg-brand-500 text-white shadow-theme-xs'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
                    class="rounded-xl px-4 py-2.5 text-sm font-medium transition-colors">
                    Daftar Tamu & Hak Inklusi ({{ count($guestInclusionList) }})
                </button>
                <button
                    type="button"
                    @click="activeTab = 'fulfillment'"
                    :class="activeTab === 'fulfillment'
                        ? 'bg-brand-500 text-white shadow-theme-xs'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
                    class="flex items-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-medium transition-colors">
                    Papan Pemenuhan Antrean
                    @if ($stats['active_fulfillment'] > 0)
                        <span class="inline-flex h-5 items-center justify-center rounded-full bg-error-500 px-1.5 text-xs font-bold text-white">
                            {{ $stats['active_fulfillment'] }}
                        </span>
                    @endif
                </button>
            </div>

            <!-- Search & Filters Toolbar -->
            <form method="GET" action="{{ route('inclusions.claim-desk') }}" id="filter-form" class="flex flex-wrap items-center gap-3">
                <!-- Filter Tanggal (Default: Hari Ini) -->
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">Tanggal:</span>
                    <div class="w-36 sm:w-40">
                        <x-ui.input
                            type="date"
                            name="date"
                            value="{{ $selectedDate }}"
                            class="h-10 text-xs"
                            onchange="document.getElementById('filter-form').submit()"
                        />
                    </div>
                </div>

                <!-- Input Pencarian Kamar / Tamu -->
                <div class="w-40 sm:w-48">
                    <x-ui.input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari Kamar / Tamu..."
                        class="h-10 text-xs"
                    />
                </div>

                <!-- Filter Kategori Inklusi -->
                <input type="hidden" name="category" id="filter-category-input" value="{{ $currentFilter }}" />
                <div class="w-36 sm:w-40" x-data="{
                    selectCategory(val) {
                        document.getElementById('filter-category-input').value = val;
                        document.getElementById('filter-form').submit();
                    }
                }">
                    <x-ui.select :default-value="$currentFilter" @change="selectCategory($event.detail.value)">
                        <x-ui.select.trigger class="h-10 text-xs">
                            <x-ui.select.value placeholder="Kategori" />
                        </x-ui.select.trigger>
                        <x-ui.select.content class="z-102">
                            <x-ui.select.item value="all">Semua Kategori</x-ui.select.item>
                            <x-ui.select.item value="meal">Sarapan & F&B</x-ui.select.item>
                            <x-ui.select.item value="beverage">Minuman</x-ui.select.item>
                            <x-ui.select.item value="laundry">Laundry</x-ui.select.item>
                            <x-ui.select.item value="rental">Sewa Kendaraan</x-ui.select.item>
                            <x-ui.select.item value="amenity">Amenitas</x-ui.select.item>
                        </x-ui.select.content>
                    </x-ui.select>
                </div>

                <x-ui.button type="submit" variant="default" size="sm" class="h-10 px-3 text-xs">
                    Terapkan
                </x-ui.button>

                @if (!empty($search) || $currentFilter !== 'all' || !$isToday)
                    <x-ui.button as="a" href="{{ route('inclusions.claim-desk') }}" variant="ghost" size="sm" class="text-xs">
                        Reset
                    </x-ui.button>
                @endif
            </form>
        </div>

        <!-- TAB 1: Roster Tamu & Hak Inklusi -->
        <div x-show="activeTab === 'roster'" class="pt-4">
            @if (count($guestInclusionList) === 0)
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h5 class="mt-4 text-base font-semibold text-gray-800 dark:text-white/90">Tidak Ada Tamu dengan Inklusi Terpilih</h5>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pastikan ada tamu yang menginap pada tanggal ini atau ubah filter pencarian.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-start text-sm">
                        <thead class="border-b border-gray-200 bg-gray-50/50 text-xs font-semibold uppercase text-gray-500 dark:border-gray-800 dark:bg-gray-900/20 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3 text-start">Unit Kamar</th>
                                <th class="px-4 py-3 text-start">Tamu Menginap</th>
                                <th class="px-4 py-3 text-start">Kategori & Fasilitas</th>
                                <th class="px-4 py-3 text-start">Aturan Allowance & Harga</th>
                                <th class="px-4 py-3 text-start">Status Hari Ini</th>
                                <th class="px-4 py-3 text-end">Aksi Cepat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach ($guestInclusionList as $entry)
                                @php
                                    $b = $entry['booking'];
                                    $inc = $entry['inclusion'];
                                    $isClaimed = $entry['is_claimed'];
                                    $claimedItem = $entry['claimed_item'];
                                    $category = $entry['category'];

                                    $catBadge = match($category) {
                                        'meal' => ['color' => 'success', 'label' => 'Sarapan & F&B', 'icon' => '🍲'],
                                        'beverage' => ['color' => 'warning', 'label' => 'Minuman', 'icon' => '☕'],
                                        'laundry' => ['color' => 'info', 'label' => 'Laundry', 'icon' => '🧺'],
                                        'rental' => ['color' => 'primary', 'label' => 'Rental Kendaraan', 'icon' => '🛵'],
                                        'amenity' => ['color' => 'gray', 'label' => 'Amenitas Kamar', 'icon' => '🛏️'],
                                        default => ['color' => 'light', 'label' => ucfirst($category), 'icon' => '✨'],
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                    <!-- Kamar -->
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-2.5">
                                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 font-bold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                                {{ $b->room?->room_number ?? '-' }}
                                            </span>
                                            <div>
                                                <p class="font-semibold text-gray-800 dark:text-white/90">Kamar {{ $b->room?->room_number ?? '-' }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $b->room?->roomType?->name ?? 'Standard' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Tamu -->
                                    <td class="px-4 py-3.5">
                                        <p class="font-medium text-gray-800 dark:text-white/90">{{ $b->customer?->full_name ?? $b->customer?->name ?? 'Guest' }}</p>
                                        <p class="text-xs text-gray-400">{{ $b->booking_code }}</p>
                                    </td>

                                    <!-- Kategori & Layanan -->
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-2">
                                            <span class="text-base">{{ $catBadge['icon'] }}</span>
                                            <div>
                                                <x-ui.badge variant="light" :color="$catBadge['color']" size="sm">
                                                    {{ $catBadge['label'] }}
                                                </x-ui.badge>
                                                <p class="mt-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                                    {{ $inc->service?->name ?? 'Hak ' . ucfirst($category) . ' (' . ($inc->session_time ?? 'Harian') . ')' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Aturan Allowance & Harga -->
                                    <td class="px-4 py-3.5">
                                        @if ($inc->allowance_limit <= 0)
                                            <div class="space-y-0.5">
                                                <div class="flex items-center gap-1.5 text-xs font-semibold text-success-600 dark:text-success-400">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    <span>Gratis Penuh (Rp 0)</span>
                                                </div>
                                                @if ($inc->service)
                                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                                        Harga Normal: <strong class="text-gray-700 dark:text-gray-300">Rp {{ number_format($inc->service->price, 0, ',', '.') }}</strong>
                                                    </p>
                                                @else
                                                    <p class="text-[11px] text-gray-400">
                                                        Cover 100% biaya paket
                                                    </p>
                                                @endif
                                            </div>
                                        @else
                                            <div class="space-y-0.5 text-xs">
                                                <p class="font-medium text-gray-700 dark:text-gray-300">
                                                    Subsidi: <strong class="text-brand-600 dark:text-brand-400">Rp {{ number_format($inc->allowance_limit, 0, ',', '.') }}</strong>
                                                </p>
                                                @if ($inc->service)
                                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                                        Harga Normal: Rp {{ number_format($inc->service->price, 0, ',', '.') }}
                                                        @if ($inc->service->price > $inc->allowance_limit)
                                                            &bull; <span class="font-medium text-error-500">Sisa: Rp {{ number_format($inc->service->price - $inc->allowance_limit, 0, ',', '.') }}</span>
                                                        @endif
                                                    </p>
                                                @endif
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Status Hari Ini -->
                                    <td class="px-4 py-3.5">
                                        @if ($isClaimed)
                                            <div class="flex items-center gap-1.5 text-xs font-medium text-success-600 dark:text-success-400">
                                                <span class="h-2 w-2 rounded-full bg-success-500"></span>
                                                Sudah Diklaim
                                                <span class="text-gray-400">({{ $claimedItem->created_at->format('H:i') }} WIB)</span>
                                            </div>
                                            <p class="text-[11px] text-gray-500">{{ Str::limit($claimedItem->notes ?? $claimedItem->item_name, 35) }}</p>
                                        @else
                                            <div class="flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                                                <span class="h-2 w-2 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                                Belum Hadir / Klaim
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Tombol Aksi -->
                                    <td class="px-4 py-3.5 text-end">
                                        @if ($isClaimed)
                                            <x-ui.badge variant="ghost" color="success" size="sm">
                                                ✓ Terlayani
                                            </x-ui.badge>
                                        @else
                                            <div class="flex items-center justify-end gap-2">
                                                @if (in_array($category, ['meal', 'amenity', 'beverage']))
                                                    <!-- 1-Click Quick Claim Form -->
                                                    <form method="POST" action="{{ route('inclusions.quick-claim', $b->id) }}">
                                                        @csrf
                                                        <input type="hidden" name="inclusion_id" value="{{ $inc->id }}" />
                                                        <input type="hidden" name="claim_date" value="{{ $selectedDate }}" />
                                                        <x-ui.button type="submit" variant="default" size="sm" class="bg-success-600 hover:bg-success-700 text-white">
                                                            Quick Claim (Hadir)
                                                        </x-ui.button>
                                                    </form>
                                                @endif

                                                <!-- Specific Order Modal Trigger -->
                                                <x-ui.button
                                                    type="button"
                                                    variant="outline"
                                                    size="sm"
                                                    @click="openFulfillmentModal({{ json_encode($b) }}, '{{ $category }}', '{{ addslashes($inc->service?->name ?? ucfirst($category)) }}')">
                                                    @if ($category === 'laundry')
                                                        Terima Cucian...
                                                    @elseif ($category === 'rental')
                                                        Pinjamkan Unit...
                                                    @else
                                                        Pesanan Khusus...
                                                    @endif
                                                </x-ui.button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- TAB 2: Papan Pemenuhan Antrean (Fulfillment Tracker) -->
        <div x-show="activeTab === 'fulfillment'" class="pt-4" x-cloak>
            @if ($activeOrders->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h5 class="mt-4 text-base font-semibold text-gray-800 dark:text-white/90">Tidak Ada Antrean Pemenuhan pada Tanggal Ini</h5>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Semua pesanan makanan dapur, cucian laundry, atau peminjaman telah diselesaikan.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-start text-sm">
                        <thead class="border-b border-gray-200 bg-gray-50/50 text-xs font-semibold uppercase text-gray-500 dark:border-gray-800 dark:bg-gray-900/20 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3 text-start">Waktu Order</th>
                                <th class="px-4 py-3 text-start">Tujuan Kamar & Tamu</th>
                                <th class="px-4 py-3 text-start">Item Layanan / Pemenuhan</th>
                                <th class="px-4 py-3 text-start">Instruksi & Catatan Khusus</th>
                                <th class="px-4 py-3 text-start">Rincian Biaya & Inklusi</th>
                                <th class="px-4 py-3 text-start">Status Pengerjaan</th>
                                <th class="px-4 py-3 text-end">Aksi Petugas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach ($activeOrders as $order)
                                @php
                                    $isPending = ($order->status === 'pending');
                                    $svcCat = $order->service?->category ?? 'other';
                                    $catBadge = match($svcCat) {
                                        'meal' => ['color' => 'success', 'label' => 'Dapur / F&B'],
                                        'laundry' => ['color' => 'info', 'label' => 'Laundry'],
                                        'rental' => ['color' => 'primary', 'label' => 'Rental'],
                                        default => ['color' => 'gray', 'label' => ucfirst($svcCat)],
                                    };
                                    $totalOriginal = (float) $order->unit_price * (int) $order->quantity;
                                    $discount = (float) $order->discount_or_allowance;
                                    $finalSubtotal = (float) $order->subtotal;
                                @endphp
                                <tr class="hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                    <td class="px-4 py-3.5 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $order->created_at->format('H:i') }} WIB
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <p class="font-semibold text-gray-800 dark:text-white/90">Kamar {{ $order->booking?->room?->room_number ?? '-' }}</p>
                                        <p class="text-xs text-gray-500">{{ $order->booking?->customer?->full_name ?? $order->booking?->customer?->name ?? 'Guest' }}</p>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-1.5">
                                            <x-ui.badge variant="light" :color="$catBadge['color']" size="sm">
                                                {{ $catBadge['label'] }}
                                            </x-ui.badge>
                                            <span class="font-medium text-gray-800 dark:text-white/90">{{ $order->item_name }} (x{{ $order->quantity }})</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-xs text-gray-600 dark:text-gray-300">
                                        {{ $order->notes ?? '-' }}
                                    </td>

                                    <!-- Kolom Rincian Biaya & Inklusi -->
                                    <td class="px-4 py-3.5">
                                        <div class="space-y-1">
                                            @if ($totalOriginal > 0)
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    Harga: Rp {{ number_format($order->unit_price, 0, ',', '.') }} x {{ $order->quantity }}
                                                    = <span class="font-medium text-gray-700 dark:text-gray-300">Rp {{ number_format($totalOriginal, 0, ',', '.') }}</span>
                                                </p>
                                            @endif

                                            @if ($discount > 0)
                                                <p class="text-[11px] font-medium text-success-600 dark:text-success-400">
                                                    Potongan Inklusi: -Rp {{ number_format($discount, 0, ',', '.') }}
                                                </p>
                                            @endif

                                            <div>
                                                @if ($finalSubtotal <= 0 || $order->is_free_inclusion)
                                                    <x-ui.badge variant="light" color="success" size="sm">
                                                        Tagihan: Rp 0 (Inklusi)
                                                    </x-ui.badge>
                                                @else
                                                    <x-ui.badge variant="light" color="error" size="sm">
                                                        Tagihan: Rp {{ number_format($finalSubtotal, 0, ',', '.') }} (Charge Kamar)
                                                    </x-ui.badge>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Status Pengerjaan -->
                                    <td class="px-4 py-3.5">
                                        @if ($isPending)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-warning-50 px-2.5 py-1 text-xs font-semibold text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">
                                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-warning-500"></span>
                                                Sedang Diproses
                                            </span>
                                        @elseif ($order->status === 'delivered')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-xs font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-400">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Selesai / Disajikan
                                            </span>
                                        @else
                                            <span class="text-xs text-error-500">Dibatalkan</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-end">
                                        @if ($isPending)
                                            <div class="flex items-center justify-end gap-1.5">
                                                <form method="POST" action="{{ route('inclusions.update-status', $order->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="delivered" />
                                                    <input type="hidden" name="action_note" value="Diselesaikan oleh {{ auth()->user()?->name ?? 'Staf' }}" />
                                                    <x-ui.button type="submit" variant="default" size="sm" class="bg-success-600 hover:bg-success-700 text-white">
                                                        Tandai Selesai
                                                    </x-ui.button>
                                                </form>

                                                <form method="POST" action="{{ route('inclusions.update-status', $order->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="cancelled" />
                                                    <input type="hidden" name="action_note" value="Dibatalkan" />
                                                    <x-ui.button type="submit" variant="ghost" size="sm" class="text-error-500 hover:text-error-600">
                                                        Batal
                                                    </x-ui.button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">Tertutup</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </x-common.component-card>

    <!-- Modal Pemenuhan Layanan Khusus (Fulfillment Dialog) -->
    <x-ui.dialog id="fulfillmentDialog" x-ref="fulfillmentDialog">
        <x-ui.dialog.content :close-on-overlay="false" class="sm:max-w-lg">
            <x-ui.dialog.header>
                <x-ui.dialog.title class="flex items-center gap-2">
                    <span class="text-lg">📋</span>
                    <span>Proses Layanan Pemenuhan Inklusi</span>
                </x-ui.dialog.title>
                <x-ui.dialog.description>
                    Kamar <strong class="text-gray-800 dark:text-white" x-text="selectedBooking.room_number"></strong> &bull;
                    Tamu: <span class="font-medium text-gray-700 dark:text-gray-300" x-text="selectedBooking.guest_name"></span>
                </x-ui.dialog.description>
            </x-ui.dialog.header>

            <form
                id="fulfillment-form"
                :action="'/inclusions/' + selectedBooking.id + '/fulfillment-order'"
                method="POST"
                class="space-y-4 py-2">
                @csrf
                <input type="hidden" name="category_type" :value="fulfillmentFormData.category_type" />
                <input type="hidden" name="claim_date" value="{{ $selectedDate }}" />

                <!-- Pilihan Item Layanan -->
                <x-ui.field>
                    <x-ui.field.label for="fulfillment-service-trigger" required>
                        Pilih Menu / Layanan
                    </x-ui.field.label>
                    <input type="hidden" name="service_id" id="fulfillment-service-id" x-model="fulfillmentFormData.service_id" required />
                    <x-ui.select @change="fulfillmentFormData.service_id = $event.detail.value">
                        <x-ui.select.trigger id="fulfillment-service-trigger">
                            <x-ui.select.value placeholder="-- Pilih Layanan yang Sesuai --" />
                        </x-ui.select.trigger>
                        <x-ui.select.content class="z-102 max-h-56 overflow-y-auto custom-scrollbar">
                            @foreach ($availableServices as $srv)
                                <x-ui.select.item value="{{ $srv->id }}">
                                    {{ $srv->name }} ({{ ucfirst($srv->category) }} - Rp {{ number_format($srv->price, 0, ',', '.') }})
                                </x-ui.select.item>
                            @endforeach
                        </x-ui.select.content>
                    </x-ui.select>
                </x-ui.field>

                <!-- Jumlah / Porsi / Durasi -->
                <x-ui.field>
                    <x-ui.field.label for="fulfillment-qty" required>
                        Jumlah (Porsi / Pcs / Durasi)
                    </x-ui.field.label>
                    <x-ui.input
                        type="number"
                        id="fulfillment-qty"
                        name="quantity"
                        min="1"
                        max="20"
                        x-model="fulfillmentFormData.quantity"
                        required
                    />
                </x-ui.field>

                <!-- Instruksi / Catatan Operasional -->
                <x-ui.field>
                    <x-ui.field.label for="fulfillment-notes">
                        <span x-show="fulfillmentFormData.category_type === 'laundry'">Detail Pakaian & No. Laundry Bag</span>
                        <span x-show="fulfillmentFormData.category_type === 'rental'">Nomor Unit Kendaraan & Estimasi Jam Kembali</span>
                        <span x-show="fulfillmentFormData.category_type === 'meal'">Catatan Masakan / Preferensi Tamu</span>
                        <span x-show="!['laundry', 'rental', 'meal'].includes(fulfillmentFormData.category_type)">Catatan Tambahan</span>
                    </x-ui.field.label>
                    <x-ui.textarea
                        id="fulfillment-notes"
                        name="detail_notes"
                        rows="3"
                        x-model="fulfillmentFormData.detail_notes"
                        placeholder="Contoh: 3 kemeja, no bag LB-02 / Unit Sepeda 02 s/d 11:30 / Omelet pedas tanpa bawang..."
                    ></x-ui.textarea>
                </x-ui.field>

                <div class="rounded-xl border border-blue-light-200 bg-blue-light-50/50 p-3 text-xs text-blue-light-700 dark:border-blue-light-900/40 dark:bg-blue-light-950/20 dark:text-blue-light-300">
                    💡 Sistem otomatis menghitung potongan 100% jika sesuai hak paket inklusi harian kamar. Kelebihan kuota akan otomatis ditagihkan ke folio kamar.
                </div>

                <x-ui.dialog.footer class="mt-4 flex justify-end gap-2">
                    <x-ui.button type="button" variant="outline" @click="closeDialog('fulfillmentDialog')">
                        Batal
                    </x-ui.button>
                    <x-ui.button type="submit" variant="default">
                        Kirim ke Antrean Layanan
                    </x-ui.button>
                </x-ui.dialog.footer>
            </form>
        </x-ui.dialog.content>
    </x-ui.dialog>
</div>
@endsection

