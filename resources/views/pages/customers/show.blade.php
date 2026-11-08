@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Detail Tamu: {{ $customer->full_name }}" />

        <!-- Top Bar with Back Button & Blacklist Toggle -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-ui.button as="a" href="{{ route('customers.index') }}" variant="outline" size="sm">
                    &larr; Kembali ke Daftar Tamu
                </x-ui.button>
            </div>
            <div class="flex items-center gap-3">
                <form method="POST" action="{{ route('customers.toggle-blacklist', $customer) }}" class="inline">
                    @csrf
                    <x-ui.button type="submit"
                        :variant="$customer->is_blacklisted ? 'default' : 'destructive'"
                        size="sm"
                        onclick="return confirm('Apakah Anda yakin ingin mengubah status blacklist tamu ini?')">
                        {{ $customer->is_blacklisted ? 'Aktifkan Kembali Tamu' : 'Tandai Blacklist' }}
                    </x-ui.button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <x-ui.alert class="border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-ui.alert.title>Sukses</x-ui.alert.title>
                <x-ui.alert.description>{{ session('success') }}</x-ui.alert.description>
            </x-ui.alert>
        @endif

        @if ($customer->is_blacklisted)
            <x-ui.alert variant="destructive">
                <x-ui.alert.title>PERHATIAN: Tamu Ini Berstatus BLACKLIST!</x-ui.alert.title>
                <x-ui.alert.description>
                    {{ $customer->special_notes ?: 'Tamu ini memiliki catatan pelanggaran atau ketidaksesuaian operasional sebelumnya. Pembuatan reservasi kamar dilarang untuk akun ini.' }}
                </x-ui.alert.description>
            </x-ui.alert>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <!-- Left Info Card -->
            <div class="lg:col-span-5 space-y-6">
                <x-ui.card class="p-6">
                    <div class="flex items-center gap-4 border-b border-gray-100 pb-5 dark:border-gray-800">
                        <x-ui.avatar size="xl">
                            <x-ui.avatar.fallback class="font-bold text-brand-600 bg-brand-50 dark:bg-brand-500/15 dark:text-brand-400 text-lg">
                                {{ strtoupper(substr($customer->full_name, 0, 2)) }}
                            </x-ui.avatar.fallback>
                        </x-ui.avatar>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-white/90">{{ $customer->full_name }}</h3>
                            <div class="flex items-center gap-2 mt-1">
                                <x-ui.badge variant="light" :color="$customer->is_blacklisted ? 'error' : 'success'" size="sm">
                                    {{ $customer->is_blacklisted ? 'Blacklist' : 'Aktif' }}
                                </x-ui.badge>
                                <span class="text-xs text-gray-400">ID #{{ $customer->id }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                        <div class="py-3 flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Tipe Identitas</span>
                            <span class="font-medium uppercase text-gray-800 dark:text-white/90">{{ $customer->identity_type }}</span>
                        </div>
                        <div class="py-3 flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Nomor Identitas (NIK)</span>
                            <span class="font-mono text-gray-800 dark:text-white/90">{{ $customer->identity_number ?: '-' }}</span>
                        </div>
                        <div class="py-3 flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">No. WhatsApp</span>
                            <span class="font-medium text-gray-800 dark:text-white/90">{{ $customer->phone_number }}</span>
                        </div>
                        <div class="py-3 flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Alamat Email</span>
                            <span class="text-gray-800 dark:text-white/90">{{ $customer->email ?: '-' }}</span>
                        </div>
                        <div class="py-3 flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Negara Asal</span>
                            <span class="text-gray-800 dark:text-white/90">{{ $customer->country }}</span>
                        </div>
                        <div class="py-3">
                            <span class="text-gray-500 dark:text-gray-400 block mb-1">Alamat Domisili</span>
                            <span class="text-gray-800 dark:text-white/90">{{ $customer->address ?: 'Belum ada data alamat' }}</span>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="p-6">
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-2">
                        Catatan Khusus &amp; Preferensi
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        {{ $customer->special_notes ?: 'Tidak ada preferensi khusus yang tercatat.' }}
                    </p>
                </x-ui.card>
            </div>

            <!-- Right: Histori Reservasi & Menginap -->
            <div class="lg:col-span-7">
                <x-ui.card class="p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                            Riwayat Reservasi &amp; Menginap
                        </h3>
                        <x-ui.badge variant="light" color="primary" size="sm">
                            {{ $customer->bookings->count() }} Reservasi
                        </x-ui.badge>
                    </div>

                    @if ($customer->bookings->isEmpty())
                        <div class="py-12 text-center text-gray-500 dark:text-gray-400">
                            <x-svg.calendar class="size-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" />
                            <p class="text-sm font-medium">Tamu ini belum memiliki riwayat reservasi menginap.</p>
                            <div class="mt-4">
                                <x-ui.button as="a" href="{{ route('bookings.create') }}" size="sm">
                                    + Buat Reservasi Baru
                                </x-ui.button>
                            </div>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($customer->bookings as $b)
                                <div class="py-3.5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between text-xs">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('bookings.show', $b) }}" class="font-mono font-bold text-brand-600 hover:underline dark:text-brand-400">
                                                {{ $b->booking_code }}
                                            </a>
                                            <span class="font-semibold text-gray-800 dark:text-white">
                                                Kamar {{ $b->room?->room_number }} ({{ $b->room?->roomType?->name }})
                                            </span>
                                        </div>
                                        <div class="mt-1 text-gray-500 dark:text-gray-400">
                                            {{ $b->check_in_date->format('d M Y') }} &rarr; {{ $b->check_out_date->format('d M Y') }}
                                            ({{ $b->total_nights }} malam)
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="text-end">
                                            <div class="font-bold text-gray-800 dark:text-white">
                                                Rp {{ number_format($b->grand_total, 0, ',', '.') }}
                                            </div>
                                            @php
                                                $bColor = match($b->status) {
                                                    'confirmed' => 'primary',
                                                    'checked_in' => 'success',
                                                    'checked_out' => 'gray',
                                                    default => 'error'
                                                };
                                            @endphp
                                            <x-ui.badge variant="light" :color="$bColor" size="sm" class="capitalize">
                                                {{ str_replace('_', ' ', $b->status) }}
                                            </x-ui.badge>
                                        </div>
                                        <x-ui.button as="a" href="{{ route('bookings.show', $b) }}" variant="outline" size="sm">
                                            Buka &rarr;
                                        </x-ui.button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-ui.card>
            </div>
        </div>
    </div>
@endsection
