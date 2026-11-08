# Antigravity IDE Development Rules

# Project: Homestay Operations & Mini-POS System

## 1. Core Stack & Environment

- **Framework:** Laravel 12.x (PHP 8.2+)
- **Template:** TailAdmin Free Edition (Tailwind CSS v3, Blade Templates, Alpine.js)
- **Asset Bundler:** Vite
- **Font & Theme:** Inter / Satoshi (bawaan TailAdmin), Dark Mode support wajib aktif via class `dark:` Tailwind.

---

## 2. Frontend Constraints (TailAdmin Free Strict Rules)

1. **Zero Pro Components:** Dilarang mengasumsikan atau memanggil class/komponen dari TailAdmin Pro (hindari table filter pro, advanced date-range picker komersial, atau chart berbayar).

1. **UI Component:** selalu Gunakan UI Component pada `resources/views/components/ui`. jika component tersebut tidak ada maka buatkan componentnnya dengan mengikuti tema atau firmware tailadmin. Selalu gunakan atribut `for="..."` pada `<x-ui.field.label>` yang cocok dengan atribut `id="..."` pada input/select/textarea terkait.

1. **Select Component Hierarchy:**
   - **Opsi < 10:** Gunakan `<x-ui.select>` bawaan TailAdmin (trigger, value, content, item).
   - **Opsi 10 - 20 (atau > 10):** Gunakan `<x-ui.combobox>` bawaan TailAdmin (searchable input, empty, content, item).
   - **Opsi > 20 / Dinamis Server-Side:** Gunakan `select2` basis server-side (`<x-common.select2>` dengan AJAX URL), seperti pencarian data customer/tamu dan item katalog service yang dijual.

1. **Jquery First:** Semua interaktivitas antarmuka (Modal, Dropdown, Tabs, Accordion, Dynamic Input Repeater) wajib menggunakan jquery.
1. **Currency Display:** Semua nilai uang wajib diformat Rupiah: `Rp {{ number_format($val, 0, ',', '.') }}`.
1. **Color Tokens (Tailwind Standard):**
    - Available / Clean / Paid: `emerald-600` / `bg-emerald-50 text-emerald-700`
    - Occupied / Active: `blue-600` / `bg-blue-50 text-blue-700`
    - Dirty / Housekeeping / Partial DP: `amber-500` / `bg-amber-50 text-amber-700`
    - Maintenance / Cancelled / Blacklist: `rose-600` / `bg-rose-50 text-rose-700`
    - Complimentary Tag: `bg-green-100 text-green-800 border border-green-200`
1. **Form UX Customer:**
    - Input reservasi harus mendukung autocomplete customer berdasarkan nama atau nomor WhatsApp.
    - Sediakan tombol modal Alpine.js `+ Tamu Baru` di form booking agar staf tidak perlu berpindah halaman.
    - Jika customer terpilih berstatus `is_blacklisted = true`, tampilkan alert merah dan nonaktifkan tombol submit booking.

---

## 3. Backend & Database Integrity Rules

1. **Financial Calculations:**
    - Dilarang menggunakan tipe `float` atau `double` di migration maupun operasi PHP.
    - Kolom moneter wajib `DECIMAL(12, 2)`.
    - Seluruh agregasi tagihan kamar harus dipusatkan di `App\Services\FolioCalculationService`.
2. **Concurrency & Atomic Transactions:**
    - Pengurangan stok item fisik (`has_stock = true`) wajib dibungkus dalam `DB::transaction()` dan menggunakan `lockForUpdate()`.
3. **Overlap Booking Guard:**
    - Pengecekan bentrok kamar tidak boleh hanya di frontend, wajib divalidasi via Rule/Service di backend:
      `WHERE check_in_date < $newCheckOut AND check_out_date > $newCheckIn AND status != 'cancelled'`.
4. **Data Isolation (Master Customer):**
    - Reservasi tidak boleh menyimpan data identitas mentah; wajib berelasi via `customer_id`.
    - Gunakan `ON DELETE RESTRICT` pada `customer_id` di tabel `bookings` agar histori tamu tidak terhapus sengaja.

---

## 4. Code Architecture & Conventions

1. **Fat Models / Skinny Controllers:**
    - Controller hanya bertugas menerima request, memanggil Service/Action, dan me-return View atau JsonResponse.
    - Logika kalkulasi jatah paket, POS order, dan invoice wajib berada di `App\Services\*`.
2. **Form Requests:**
    - Validasi data POST/PUT wajib menggunakan dedicated Form Request class (`app/Http/Requests/*`), bukan inline `$request->validate()` di Controller.
3. **Naming Conventions:**
    - Models: PascalCase singular (`Customer`, `BookingItem`).
    - Tables: snake_case plural (`customers`, `booking_items`).
    - Services: PascalCase dengan akhiran Service (`FolioCalculationService`, `RoomOrderService`).
    - Blade Views: kebab-case di bawah folder modul (`resources/views/admin/room-orders/index.blade.php`).

---

## 5. Execution Protocol for AI Agent

1. Sebelum membuat/mengedit file, baca referensi di `docs/PRD.md` dan `docs/system_architecture.md`.
2. Eksekusi tugas langkah demi langkah sesuai checklist di `docs/implementation_roadmap.md`.
3. Dilarang merestrukturisasi layout master TailAdmin (`layouts/app.blade.php`) tanpa instruksi eksplisit.
4. Buat kode yang lengkap (_production-ready_), hindari placeholder komentar seperti `// tambahkan kode di sini`.
