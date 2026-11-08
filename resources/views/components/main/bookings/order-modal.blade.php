@props(['booking', 'services' => collect()])

@php
    $roomType = $booking->room?->roomType;
    $inclusions = $roomType ? $roomType->inclusions : collect();

    // Check if an inclusion has been claimed today (or in this stay)
    $checkInclusionClaimed = function ($inc) use ($booking) {
        $isDaily = in_array($inc->session_time, ['breakfast', 'daily', 'lunch', 'dinner'], true);

        return $booking->items
            ->where('status', '!=', 'cancelled')
            ->filter(function ($item) {
                return (bool) $item->is_free_inclusion || (float) $item->discount_or_allowance > 0;
            })
            ->first(function ($item) use ($inc, $isDaily) {
                if ($isDaily && !$item->created_at?->isToday()) {
                    return false;
                }

                // 1. If inclusion specifically targets a service, it MUST match that exact service
                if ($inc->service_id) {
                    return $item->service_id === $inc->service_id;
                }

                // 2. Category match
                $itemCat = $item->service?->category;
                if ($inc->category !== 'any' && $itemCat !== $inc->category) {
                    return false;
                }

                // 3. Session time match (e.g. breakfast vs lunch vs dinner)
                if ($inc->session_time) {
                    // Check if notes indicate the session (e.g. "Klaim Inklusi: Sarapan Pagi")
                    $notes = strtolower($item->notes ?? '');
                    if (str_contains($notes, strtolower($inc->session_time))) {
                        return true;
                    }

                    // Check service sub_category
                    $itemSubCat = $item->service?->sub_category;
                    if ($itemSubCat) {
                        return $itemSubCat === $inc->session_time;
                    }
                }

                return true;
            });
    };

    $inclusionsPayload = $inclusions->map(function ($inc) use ($services, $checkInclusionClaimed) {
        $service = null;
        if ($inc->service_id) {
            $service = $services->firstWhere('id', $inc->service_id);
        }
        if (!$service) {
            $service = $services->first(function ($s) use ($inc) {
                $catMatch = $inc->category === 'any' || $s->category === $inc->category;
                if (!$catMatch) return false;
                if ($inc->session_time && $s->sub_category) {
                    return $s->sub_category === $inc->session_time;
                }
                return true;
            });
        }

        $claimedItem = $checkInclusionClaimed($inc);
        $allowanceLimit = (float) $inc->allowance_limit;
        $isFullFree = $allowanceLimit <= 0.0;

        $title = match ($inc->session_time) {
            'breakfast' => 'Sarapan Pagi (Breakfast)',
            'lunch' => 'Makan Siang (Lunch)',
            'dinner' => 'Makan Malam (Dinner)',
            'daily' => 'Complimentary Harian (' . ucfirst($inc->category) . ')',
            default => 'Paket Inklusi ' . ucfirst($inc->category),
        };

        return [
            'id' => $inc->id,
            'title' => $title,
            'category' => $inc->category,
            'session_time' => $inc->session_time ?? 'daily',
            'is_daily' => in_array($inc->session_time, ['breakfast', 'daily', 'lunch', 'dinner'], true),
            'service_id' => $service?->id,
            'service_name' => $service?->name ?? 'Layanan Umum ' . ucfirst($inc->category),
            'service_price' => $service ? (float) $service->price : 0,
            'is_full_free' => $isFullFree,
            'allowance_limit' => $allowanceLimit,
            'is_claimed_today' => $claimedItem !== null,
            'claimed_at' => $claimedItem ? $claimedItem->created_at?->format('H:i') : null,
        ];
    });

    $servicesPayload = $services->map(function ($s) use ($inclusions, $checkInclusionClaimed) {
        $matchingInc = $inclusions->sortByDesc(function ($inc) use ($s) {
            if ($inc->service_id === $s->id) return 100;
            if ($inc->session_time && $s->sub_category && $inc->session_time === $s->sub_category) return 50;
            if ($inc->category === $s->category) return 10;
            return 1;
        })->first(function ($inc) use ($s) {
            if ($inc->service_id) {
                return $inc->service_id === $s->id;
            }
            if ($inc->category !== 'any' && $inc->category !== $s->category) {
                return false;
            }
            if ($inc->session_time && $s->sub_category) {
                return $inc->session_time === $s->sub_category;
            }
            return true;
        });

        $allowanceLimit = $matchingInc ? (float) $matchingInc->allowance_limit : null;
        $isFullFree = $matchingInc && $allowanceLimit <= 0.0;
        $claimedItem = $matchingInc ? $checkInclusionClaimed($matchingInc) : null;
        $isQuotaExhausted = $claimedItem !== null;

        return [
            'id' => $s->id,
            'name' => $s->name,
            'category' => $s->category,
            'price' => (float) $s->price,
            'has_stock' => (bool) $s->has_stock,
            'stock_quantity' => (int) $s->stock_quantity,
            'is_included' => $matchingInc !== null,
            'is_full_free' => $isFullFree,
            'allowance_limit' => $allowanceLimit,
            'is_quota_exhausted' => $isQuotaExhausted,
            'claimed_at' => $claimedItem ? $claimedItem->created_at?->format('H:i') : null,
        ];
    });
@endphp

<div x-data="bookingOrderModal(@js($servicesPayload), @js($inclusionsPayload), {
    service_id: '{{ old('service_id', '') }}',
    quantity: {{ (int) old('quantity', 1) }},
    billing_type: '{{ old('billing_type', 'charged_to_room') }}',
    notes: '{{ addslashes(old('notes', '')) }}'
})">
    <x-ui.dialog id="bookingOrderDialog">
        <x-ui.dialog.content class="sm:max-w-xl max-h-[90vh] overflow-y-auto custom-scrollbar" :close-on-overlay="false">
            <x-ui.dialog.header class="text-start pe-10">
                <x-ui.dialog.title>
                    Order Layanan &amp; Mini-POS
                </x-ui.dialog.title>
                <x-ui.dialog.description>
                    Kamar {{ $booking->room->room_number }} &bull; {{ $booking->customer->full_name }}
                </x-ui.dialog.description>
            </x-ui.dialog.header>

            <!-- Quick Claim Notice Banner -->
            <div x-show="quickClaimNotice" x-cloak class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3.5 text-xs text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-start gap-2">
                        <x-svg.check-circle class="size-4 shrink-0 text-emerald-600 dark:text-emerald-400 mt-0.5" />
                        <span x-html="quickClaimNotice"></span>
                    </div>
                    <button type="button" @click="quickClaimNotice = ''" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400">
                        <x-svg.x class="size-3.5" />
                    </button>
                </div>
            </div>

            <!-- Error alert inside modal dialog -->
            <div x-show="orderErrorMsg" x-cloak class="mt-3 rounded-xl border border-error-200 bg-error-50 p-3.5 text-xs text-error-700 dark:border-error-800 dark:bg-error-900/30 dark:text-error-400">
                <div class="flex items-start gap-2">
                    <x-svg.info-circle class="size-4 shrink-0 text-error-600 dark:text-error-400 mt-0.5" />
                    <span x-html="orderErrorMsg"></span>
                </div>
            </div>

            @if ($errors->any())
                <div class="mt-3 rounded-xl border border-error-200 bg-error-50 p-3.5 text-xs text-error-700 dark:border-error-800 dark:bg-error-900/30 dark:text-error-400">
                    <div class="flex items-start gap-2">
                        <x-svg.info-circle class="size-4 shrink-0 text-error-600 dark:text-error-400 mt-0.5" />
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Quick Claim Inclusions Widget (if room has package inclusions) -->
            <template x-if="inclusions.length > 0">
                <div class="mt-4 rounded-xl border border-brand-100 bg-brand-50/50 p-3.5 dark:border-brand-900/40 dark:bg-brand-950/20 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5 font-semibold text-xs text-brand-700 dark:text-brand-300">
                            <x-svg.check-circle class="size-4 text-brand-600 dark:text-brand-400" />
                            <span>Paket Inklusi Kamar (Complimentary Benefits)</span>
                        </div>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400">Hak Tamu</span>
                    </div>

                    <div class="space-y-2">
                        <template x-for="inc in inclusions" :key="inc.id">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 p-3 rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 shadow-theme-xs">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs font-bold text-gray-800 dark:text-white" x-text="inc.title"></span>
                                        <template x-if="inc.is_full_free">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                                                Gratis Penuh
                                            </span>
                                        </template>
                                        <template x-if="!inc.is_full_free">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400"
                                                x-text="'Subsidi ' + formatRupiah(inc.allowance_limit)">
                                            </span>
                                        </template>
                                    </div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-2">
                                        <span x-text="'Menu: ' + inc.service_name"></span>
                                        <span>&bull;</span>
                                        <span x-text="inc.is_daily ? '1 porsi/hari' : '1x selama menginap'"></span>
                                    </div>
                                </div>

                                <div class="shrink-0 flex items-center">
                                    <template x-if="!inc.is_claimed_today">
                                        <button type="button"
                                            @click="quickClaim(inc)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand-500 text-white hover:bg-brand-600 active:scale-95 transition-all shadow-theme-xs">
                                            <x-svg.plus class="size-3.5" />
                                            <span>Klaim Inklusi</span>
                                        </button>
                                    </template>
                                    <template x-if="inc.is_claimed_today">
                                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 text-xs">
                                            <x-svg.check class="size-3.5 text-emerald-500" />
                                            <span class="text-[11px] font-medium" x-text="'Terklaim ' + (inc.claimed_at ? inc.claimed_at + ' WIB' : 'Hari Ini')"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <form action="{{ route('bookings.orders.store', $booking) }}" method="POST" @submit.prevent="submitOrder" class="mt-4 space-y-4">
                @csrf

                <!-- Service Selection via Select2 (> 20 options) -->
                <x-ui.field>
                    <x-ui.field.label for="order-service-id">
                        Pilih Menu / Layanan <span class="text-error-500">*</span>
                    </x-ui.field.label>
                    <x-common.select2
                        id="order-service-id"
                        name="service_id"
                        placeholder="-- Cari Menu F&B / Layanan Tambahan --"
                        :ajax-url="route('services.search', [], false)"
                        :value="old('service_id')"
                        required
                    />
                </x-ui.field>

                <!-- Service Info Banner (when selected) -->
                <template x-if="selectedService">
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-3.5 dark:border-gray-800 dark:bg-gray-800/60 text-xs space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Kategori &amp; Tarif Dasar:</span>
                            <span class="font-bold text-gray-800 dark:text-white" x-text="formatRupiah(selectedService.price) + ' / porsi/unit'"></span>
                        </div>

                        <!-- Stock Status -->
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Status Stok Fisik:</span>
                            <template x-if="selectedService.has_stock">
                                <span>
                                    <span class="font-semibold" :class="selectedService.stock_quantity > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                                        x-text="selectedService.stock_quantity > 0 ? 'Tersedia ' + selectedService.stock_quantity + ' unit' : 'Stok Habis'">
                                    </span>
                                </span>
                            </template>
                            <template x-if="!selectedService.has_stock">
                                <span class="text-gray-500 italic">Tanpa pembatasan stok</span>
                            </template>
                        </div>

                        <!-- Inclusion Badge & Quota Status -->
                        <template x-if="selectedService.is_included && !selectedService.is_quota_exhausted">
                            <div class="mt-1 flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1.5 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                <x-svg.check class="size-4 shrink-0" />
                                <span x-text="selectedService.is_full_free ? 'Benefit Paket Kamar: Termasuk Gratis (Rp 0)' : 'Benefit Paket: Disubsidi s/d ' + formatRupiah(selectedService.allowance_limit)"></span>
                            </div>
                        </template>

                        <!-- Quota Exhausted Warning -->
                        <template x-if="selectedService.is_included && selectedService.is_quota_exhausted">
                            <div class="mt-1 flex items-center gap-1.5 rounded-lg bg-amber-50 px-2.5 py-1.5 text-amber-800 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                <x-svg.info-circle class="size-4 shrink-0 text-amber-600 dark:text-amber-400" />
                                <span x-text="'Kuota gratis benefit hari ini sudah diklaim pukul ' + (selectedService.claimed_at || 'sebelumnya') + ' WIB. Pesanan tambahan ini dikenakan tarif reguler.'"></span>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Quantity & Billing Type Grid -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-ui.field>
                        <x-ui.field.label for="order-quantity">
                            Jumlah (Qty) <span class="text-error-500">*</span>
                        </x-ui.field.label>
                        <x-ui.input id="order-quantity" type="number" name="quantity" min="1" x-bind:max="maxQuantity" x-model="quantity" required />
                    </x-ui.field>

                    <!-- Billing Type (<10 options -> x-ui.select) -->
                    <x-ui.field>
                        <x-ui.field.label for="order-billing-type">
                            Metode Penagihan <span class="text-error-500">*</span>
                        </x-ui.field.label>
                        <input type="hidden" name="billing_type" x-bind:value="billingType" />
                        <x-ui.select :default-value="old('billing_type', 'charged_to_room')" @change="billingType = $event.detail.value">
                            <x-ui.select.trigger id="order-billing-type">
                                <x-ui.select.value placeholder="Pilih metode penagihan" />
                            </x-ui.select.trigger>
                            <x-ui.select.content>
                                <x-ui.select.item value="charged_to_room">Bebankan ke Tagihan Kamar</x-ui.select.item>
                                <x-ui.select.item value="paid_instantly">Bayar Langsung (Tunai / QRIS)</x-ui.select.item>
                            </x-ui.select.content>
                        </x-ui.select>
                    </x-ui.field>
                </div>

                <!-- Notes -->
                <x-ui.field>
                    <x-ui.field.label for="order-notes">
                        Catatan Pesanan (Opsional)
                    </x-ui.field.label>
                    <x-ui.input id="order-notes" type="text" name="notes" x-model="notes" placeholder="Contoh: Tanpa es, antar ke kamar jam 19.30" />
                </x-ui.field>

                <!-- Realtime Subtotal Summary -->
                <template x-if="selectedService">
                    <div class="rounded-xl border border-brand-100 bg-brand-50/40 p-4 dark:border-brand-900/30 dark:bg-brand-950/20 text-xs space-y-1.5">
                        <div class="flex justify-between text-gray-600 dark:text-gray-300">
                            <span>Total Asli (<span x-text="quantity"></span>x):</span>
                            <span class="font-medium text-gray-800 dark:text-white" x-text="formatRupiah(selectedService.price * quantity)"></span>
                        </div>
                        <template x-if="discount > 0">
                            <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                                <span>Diskon / Inklusi Paket:</span>
                                <span class="font-medium" x-text="'- ' + formatRupiah(discount)"></span>
                            </div>
                        </template>
                        <div class="pt-2 border-t border-brand-100 dark:border-brand-900/40 flex justify-between text-sm font-bold text-gray-800 dark:text-white">
                            <span>Total Tagihan Pesanan:</span>
                            <span class="text-brand-600 dark:text-brand-400" x-text="formatRupiah(subtotal)"></span>
                        </div>
                    </div>
                </template>

                <!-- Actions -->
                <x-ui.dialog.footer class="pt-3">
                    <x-ui.dialog.close as-child>
                        <x-ui.button variant="outline" type="button" x-bind:disabled="isSubmitting">Batal</x-ui.button>
                    </x-ui.dialog.close>
                    <x-ui.button type="submit" x-bind:disabled="!selectedServiceId || isOutOfStock || isSubmitting">
                        <span x-text="isSubmitting ? 'Memproses Pesanan...' : 'Simpan Pesanan'"></span>
                    </x-ui.button>
                </x-ui.dialog.footer>
            </form>
        </x-ui.dialog.content>
    </x-ui.dialog>
</div>

<script>
    function bookingOrderModal(servicesPayload, inclusionsPayload, oldValues = {}) {
        return {
            services: servicesPayload || [],
            inclusions: inclusionsPayload || [],
            selectedServiceId: oldValues.service_id || '',
            quantity: oldValues.quantity || 1,
            billingType: oldValues.billing_type || 'charged_to_room',
            notes: oldValues.notes || '',
            isSubmitting: false,
            orderErrorMsg: '',
            quickClaimNotice: '',

            init() {
                window.addEventListener('open-order-modal', () => {
                    this.openModal();
                });

                @if ($errors->any())
                    this.$nextTick(() => {
                        this.openModal();
                    });
                @endif
            },

            openModal() {
                this.orderErrorMsg = '';
                this.quickClaimNotice = '';
                if (window.showUiDialog) {
                    window.showUiDialog('bookingOrderDialog');
                } else {
                    const el = document.getElementById('bookingOrderDialog');
                    if (el && window.Alpine) {
                        Alpine.$data(el).show();
                    }
                }
                this.$nextTick(() => {
                    this.initSelect2();
                });
            },

            initSelect2() {
                const $ = window.jQuery;
                const $s2 = $ ? $('#order-service-id') : null;
                if ($s2 && $s2.length && window.initTailAdminSelect2) {
                    const $parent = $s2.closest('[data-slot="dialog-content"]');
                    window.initTailAdminSelect2($s2, {
                        dropdownParent: ($parent.length && $parent[0]) ? $parent : $(document.body)
                    });
                    $s2.off('change.orderModal').on('change.orderModal', (e) => {
                        this.selectedServiceId = e.target.value;
                    });
                    if (this.selectedServiceId && !$s2.val()) {
                        $s2.val(this.selectedServiceId).trigger('change');
                    }
                }
            },

            quickClaim(inc) {
                if (inc.is_claimed_today) {
                    this.orderErrorMsg = 'Inklusi ' + inc.title + ' sudah diklaim untuk hari ini.';
                    return;
                }
                if (!inc.service_id) {
                    this.orderErrorMsg = 'Menu untuk inklusi ini belum tersedia dalam katalog layanan.';
                    return;
                }

                this.orderErrorMsg = '';
                this.selectedServiceId = inc.service_id;
                this.quantity = 1;
                this.billingType = 'charged_to_room';
                this.notes = 'Klaim Inklusi: ' + inc.title;

                const $ = window.jQuery;
                const $s2 = $ ? $('#order-service-id') : null;
                if ($s2 && $s2.length) {
                    $s2.val(inc.service_id).trigger('change');
                }

                this.quickClaimNotice = 'Inklusi <strong>' + inc.title + '</strong> terpilih (' + (inc.is_full_free ? 'Gratis Rp 0' : 'Subsidi ' + this.formatRupiah(inc.allowance_limit)) + '). Periksa isian di bawah lalu klik <strong>Simpan Pesanan</strong> untuk menyelesaikan klaim.';
            },

            get selectedService() {
                return this.services.find(s => String(s.id) === String(this.selectedServiceId)) || null;
            },

            get isOutOfStock() {
                if (!this.selectedService) return false;
                return this.selectedService.has_stock && this.selectedService.stock_quantity <= 0;
            },

            get maxQuantity() {
                if (!this.selectedService || !this.selectedService.has_stock) return 99;
                return Math.max(1, this.selectedService.stock_quantity);
            },

            get subtotal() {
                if (!this.selectedService) return 0;
                const totalRaw = this.selectedService.price * this.quantity;

                if (!this.selectedService.is_included || this.selectedService.is_quota_exhausted) {
                    return totalRaw;
                }

                if (this.selectedService.is_full_free) {
                    return 0;
                }

                const discount = Math.min(this.selectedService.allowance_limit, totalRaw);
                return Math.max(0, totalRaw - discount);
            },

            get discount() {
                if (!this.selectedService) return 0;
                const totalRaw = this.selectedService.price * this.quantity;
                return Math.max(0, totalRaw - this.subtotal);
            },

            formatRupiah(num) {
                return 'Rp ' + (Number(num) || 0).toLocaleString('id-ID');
            },

            submitOrder() {
                if (!this.selectedServiceId) {
                    this.orderErrorMsg = 'Pilih menu atau layanan terlebih dahulu.';
                    return;
                }
                if (this.isOutOfStock) {
                    this.orderErrorMsg = 'Stok untuk layanan terpilih sudah habis.';
                    return;
                }

                this.orderErrorMsg = '';
                this.isSubmitting = true;

                fetch('{{ route('bookings.orders.store', $booking) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        service_id: this.selectedServiceId,
                        quantity: this.quantity,
                        billing_type: this.billingType,
                        notes: this.notes
                    })
                })
                .then(async (res) => {
                    const data = await res.json().catch(() => ({}));
                    return { status: res.status, body: data };
                })
                .then((result) => {
                    this.isSubmitting = false;
                    if (result.status === 201 || result.body.success) {
                        window.location.reload();
                    } else {
                        let msg = result.body.message || 'Gagal menyimpan pesanan layanan.';
                        if (result.body.errors) {
                            msg = Object.values(result.body.errors).flat().join('<br>');
                        }
                        this.orderErrorMsg = msg;
                    }
                })
                .catch(() => {
                    this.isSubmitting = false;
                    this.orderErrorMsg = 'Terjadi kesalahan sistem saat memproses pesanan.';
                });
            }
        };
    }
</script>
