<!-- Check-Out Modal with Folio Settlement & Outstanding Balance Check -->
<x-ui.dialog id="checkOutDialog" x-ref="checkOutDialog">
    <x-ui.dialog.content class="sm:max-w-xl">
        <x-ui.dialog.header class="text-start pe-10">
            <x-ui.dialog.title class="flex items-center gap-2">
                <span class="flex items-center justify-center size-8 rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400">
                    <x-svg.check class="size-4" />
                </span>
                <span>Proses Check-Out Kamar <span x-text="roomNumber"></span></span>
            </x-ui.dialog.title>
            <x-ui.dialog.description>
                Pemeriksaan tagihan folio, serah terima kunci kamar, dan pengalihan status ke Dirty.
            </x-ui.dialog.description>
        </x-ui.dialog.header>

        <form :action="checkOutActionUrl" method="POST" class="space-y-4">
            @csrf

            <!-- Hidden Inputs depending on state -->
            <template x-if="folioData && folioData.balance_due > 0 && !allowUnsettled">
                <input type="hidden" name="settle_payment" value="1">
            </template>
            <template x-if="folioData && folioData.balance_due > 0 && allowUnsettled">
                <input type="hidden" name="allow_unsettled" value="1">
            </template>
            <template x-if="folioData && folioData.refund_due > 0 && refundConfirmed">
                <input type="hidden" name="refund_confirmed" value="1">
            </template>

            <!-- Scrollable Body Container -->
            <div class="max-h-[62vh] overflow-y-auto custom-scrollbar space-y-4 px-1 py-0.5">

                <!-- Guest & Room Summary Banner -->
                <div class="flex items-center justify-between rounded-xl bg-gray-50 dark:bg-gray-800/60 p-3.5 border border-gray-100 dark:border-gray-700/60 text-xs">
                    <div class="space-y-0.5">
                        <div class="text-gray-500 dark:text-gray-400">Tamu Menginap:</div>
                        <div class="font-semibold text-gray-800 dark:text-white text-sm" x-text="guestName"></div>
                    </div>
                    <div class="text-end space-y-0.5">
                        <div class="text-gray-500 dark:text-gray-400" x-text="folioData ? folioData.room_type : 'Kamar'"></div>
                        <div class="font-semibold text-brand-600 dark:text-brand-400 text-sm" x-text="'Kamar ' + roomNumber"></div>
                    </div>
                </div>

                <!-- Loading State Skeleton -->
                <div x-show="isLoadingFolio" class="py-8 text-center space-y-3">
                    <div class="inline-block size-7 animate-spin rounded-full border-3 border-brand-500 border-t-transparent"></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Memeriksa rincian tagihan folio kamar...</div>
                </div>

                <!-- Loaded Content -->
                <div x-show="!isLoadingFolio && folioData" class="space-y-4">

                    <!-- ============================================== -->
                    <!-- SKENARIO 1: TAGIHAN SUDAH LUNAS BERSIH          -->
                    <!-- ============================================== -->
                    <template x-if="folioData && folioData.balance_due <= 0 && folioData.refund_due <= 0">
                        <div class="rounded-xl border border-success-200 bg-success-50/70 p-4 dark:border-success-900/50 dark:bg-success-950/20">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-success-100 text-success-600 dark:bg-success-900/50 dark:text-success-400">
                                    <x-svg.check class="size-4" />
                                </div>
                                <div class="space-y-1">
                                    <h4 class="text-sm font-semibold text-success-900 dark:text-success-200">
                                        Semua Tagihan Sudah Lunas (Rp 0)
                                    </h4>
                                    <p class="text-xs text-success-700 dark:text-success-300/90 leading-relaxed">
                                        Tamu tidak memiliki sisa tagihan maupun deposit tertahan. Kunci kamar dapat diterima dan kamar akan otomatis dialihkan ke status <strong>Dirty (Perlu Pembersihan)</strong>.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- ============================================== -->
                    <!-- SKENARIO 3: ADA PENGEMBALIAN DEPOSIT (REFUND)   -->
                    <!-- ============================================== -->
                    <template x-if="folioData && folioData.refund_due > 0">
                        <div class="rounded-xl border border-blue-200 bg-blue-50/70 p-4 dark:border-blue-900/50 dark:bg-blue-950/20 space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-200">
                                        Pengembalian Uang Jaminan / Deposit
                                    </h4>
                                    <div class="text-xl font-bold text-blue-700 dark:text-blue-300"
                                        x-text="'Rp ' + formatRupiah(folioData.refund_due)"></div>
                                    <p class="text-xs text-blue-700 dark:text-blue-300/90 leading-relaxed">
                                        Tamu menitipkan deposit yang melebihi tagihan. Pastikan uang fisik/transfer telah diserahkan kembali kepada tamu sebelum menyelesaikan check-out.
                                    </p>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-blue-200/60 dark:border-blue-800/50 space-y-2">
                                <label class="flex items-center gap-2.5 text-xs font-medium text-blue-950 dark:text-blue-100 cursor-pointer">
                                    <input type="checkbox" x-model="refundConfirmed"
                                        class="rounded border-blue-300 text-blue-600 focus:ring-blue-500 size-4">
                                    <span>Saya mengonfirmasi uang deposit sebesar <strong x-text="'Rp ' + formatRupiah(folioData.refund_due)"></strong> telah diserahkan ke tamu.</span>
                                </label>
                                <x-ui.input type="text" name="refund_notes" placeholder="Catatan pengembalian (opsional, misal: tunai kasir)" class="text-xs py-1.5" />
                            </div>
                        </div>
                    </template>

                    <!-- ============================================== -->
                    <!-- SKENARIO 2: TAMU MASIH MEMILIKI SISA TAGIHAN     -->
                    <!-- ============================================== -->
                    <template x-if="folioData && folioData.balance_due > 0">
                        <div class="space-y-4">
                            <!-- Alert Sisa Tagihan -->
                            <div class="rounded-xl border border-error-200 bg-error-50/80 p-4 dark:border-error-900/50 dark:bg-error-950/20">
                                <div class="flex items-start justify-between">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-error-700 dark:text-error-400">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            <span>Sisa Tagihan Belum Lunas</span>
                                        </div>
                                        <div class="text-2xl font-black text-error-600 dark:text-error-400"
                                            x-text="'Rp ' + formatRupiah(folioData.balance_due)"></div>
                                    </div>
                                    <a :href="'/billing/' + checkOutBookingId + '/statement'" target="_blank"
                                        class="inline-flex items-center gap-1 rounded-lg border border-error-300 bg-white px-2.5 py-1 text-xs font-medium text-error-700 hover:bg-error-50 dark:border-error-800 dark:bg-gray-800 dark:text-error-300 dark:hover:bg-gray-700 shadow-xs">
                                        <span>Rincian Folio</span>
                                        <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                    </a>
                                </div>

                                <!-- Breakdown Tagihan -->
                                <div class="mt-3 divide-y divide-error-200/50 dark:divide-error-800/40 rounded-lg bg-white/70 dark:bg-gray-900/50 p-3 text-xs">
                                    <div class="flex justify-between py-1 text-gray-600 dark:text-gray-300">
                                        <span>Sewa Kamar (<span x-text="folioData.total_nights"></span> Malam)</span>
                                        <span class="font-medium" x-text="'Rp ' + formatRupiah(folioData.total_room_charge)"></span>
                                    </div>
                                    <div class="flex justify-between py-1 text-gray-600 dark:text-gray-300">
                                        <span>Layanan Tambahan (Resto, Laundry, Sewa)</span>
                                        <span class="font-medium" x-text="'Rp ' + formatRupiah(folioData.total_charged_to_room)"></span>
                                    </div>
                                    <div class="flex justify-between py-1 text-gray-600 dark:text-gray-300">
                                        <span>Deposit / Pembayaran Masuk</span>
                                        <span class="font-medium text-success-600 dark:text-success-400" x-text="'- Rp ' + formatRupiah(folioData.deposit_amount)"></span>
                                    </div>
                                </div>

                                <!-- List Item Layanan (Jika Ada) -->
                                <template x-if="folioData.unpaid_items && folioData.unpaid_items.length > 0">
                                    <div class="mt-2.5 pt-2 border-t border-error-200/60 dark:border-error-800/40">
                                        <div class="text-[11px] font-semibold text-error-800 dark:text-error-300 mb-1">Daftar Tagihan Tambahan:</div>
                                        <div class="space-y-1 max-h-24 overflow-y-auto custom-scrollbar text-[11px] text-gray-600 dark:text-gray-400">
                                            <template x-for="item in folioData.unpaid_items" :key="item.name">
                                                <div class="flex justify-between">
                                                    <span x-text="item.name + ' (x' + item.quantity + ')'"></span>
                                                    <span class="font-medium" x-text="'Rp ' + formatRupiah(item.subtotal)"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Opsi Aksi: Pelunasan Kasir vs Piutang -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-800 dark:text-white">Pilih Alur Penyelesaian Tagihan:</span>
                                    <div class="inline-flex rounded-lg bg-gray-100 p-0.5 dark:bg-gray-800 text-xs">
                                        <button type="button" @click="allowUnsettled = false"
                                            :class="!allowUnsettled ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs font-semibold' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400'"
                                            class="rounded-md px-3 py-1.5 transition">
                                            💳 Lunasi Sekarang
                                        </button>
                                        <button type="button" @click="allowUnsettled = true"
                                            :class="allowUnsettled ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs font-semibold' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400'"
                                            class="rounded-md px-3 py-1.5 transition">
                                            📑 Check-Out Piutang
                                        </button>
                                    </div>
                                </div>

                                <!-- FORM PELUNASAN SEKARANG -->
                                <div x-show="!allowUnsettled" class="space-y-3 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-850 p-3.5">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Metode Pembayaran <span class="text-error-500">*</span>
                                            </label>
                                            <select name="payment_method" x-model="paymentMethod"
                                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                                <option value="cash">Tunai / Cash</option>
                                                <option value="qris">QRIS</option>
                                                <option value="transfer">Transfer Bank</option>
                                                <option value="card">Kartu Debit / Kredit</option>
                                                <template x-if="folioData.deposit_amount > 0">
                                                    <option value="deposit_offset">Potong Uang Jaminan / Deposit</option>
                                                </template>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Nominal Pelunasan (Rp) <span class="text-error-500">*</span>
                                            </label>
                                            <x-ui.input type="number" step="1000" min="0" name="amount_paid" x-model="amountPaid"
                                                class="text-xs py-2" />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Catatan Pelunasan (Opsional)
                                        </label>
                                        <x-ui.input type="text" name="payment_notes" x-model="paymentNotes"
                                            placeholder="Contoh: Diterima oleh resepsionis shift pagi"
                                            class="text-xs py-1.5" />
                                    </div>
                                </div>

                                <!-- FORM CHECK-OUT PIUTANG -->
                                <div x-show="allowUnsettled" class="space-y-3 rounded-xl border border-warning-300 dark:border-warning-800 bg-warning-50/60 dark:bg-warning-950/20 p-3.5">
                                    <div class="text-xs text-warning-800 dark:text-warning-300 leading-relaxed">
                                        Tamu akan diizinkan check-out dan kamar berubah menjadi <strong>Dirty</strong>. Sisa tagihan sebesar <strong x-text="'Rp ' + formatRupiah(folioData.balance_due)"></strong> akan dicatat di folio sebagai piutang.
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-warning-900 dark:text-warning-200 mb-1">
                                            Alasan / Penjamin / Instansi Piutang <span class="text-error-500">*</span>
                                        </label>
                                        <x-ui.input type="text" name="unsettled_reason" x-model="unsettledReason"
                                            x-bind:required="allowUnsettled"
                                            placeholder="Contoh: Ditagihkan ke PT Graha Mandiri via invoice tempo 14 hari (PIC: Bpk. Dani)"
                                            class="text-xs py-2 bg-white dark:bg-gray-800" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Pinned Symmetric Modal Footer -->
            <div class="mx-0 mb-0 px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end gap-3 rounded-b-2xl bg-gray-50/50 dark:bg-gray-800/50">
                <x-ui.dialog.close as-child>
                    <x-ui.button variant="outline" type="button">Batal</x-ui.button>
                </x-ui.dialog.close>

                <x-ui.button type="submit"
                    x-bind:disabled="isLoadingFolio || (folioData && folioData.balance_due > 0 && allowUnsettled && !unsettledReason) || (folioData && folioData.refund_due > 0 && !refundConfirmed)"
                    x-bind:class="folioData && folioData.balance_due > 0 && allowUnsettled ? 'bg-warning-600 hover:bg-warning-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white'">
                    <span x-show="isLoadingFolio">Memeriksa...</span>
                    <span x-show="!isLoadingFolio && (!folioData || (folioData.balance_due <= 0 && folioData.refund_due <= 0))">Selesaikan Check-Out</span>
                    <span x-show="!isLoadingFolio && folioData && folioData.balance_due > 0 && !allowUnsettled">Lunasi &amp; Check-Out</span>
                    <span x-show="!isLoadingFolio && folioData && folioData.balance_due > 0 && allowUnsettled">Check-Out sebagai Piutang</span>
                    <span x-show="!isLoadingFolio && folioData && folioData.refund_due > 0">Konfirmasi Refund &amp; Check-Out</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.dialog.content>
</x-ui.dialog>
