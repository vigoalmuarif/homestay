# Implementation Roadmap & Sprint Plan

# Project: Homestay Operations & Mini-POS (TailAdmin Free + Laravel 11)

---

## Sprint 1: Foundation, Auth & Dashboard Setup (Langkah Awal)

_Tujuan: Membangun layout dasar TailAdmin Free, sistem login yang bekerja, dan halaman ringkasan dashboard._

- [x] **1.1 Asset & Template Integration**
    - [x] Integrasikan CSS, font, dan script Alpine.js bawaan TailAdmin Free ke `resources/` Laravel via Vite.
    - [x] Pastikan fitur Dark/Light mode toggle berfungsi tanpa error di console browser.
    - [x] Buat layout master Blade: `resources/views/layouts/app.blade.php` (memuat Sidebar dinamis, Header/Navbar, dan Breadcrumbs).

- [x] **1.2 Authentication & User Roles**
    - [x] Pasang `spatie/laravel-permission` (Role: `admin`, `receptionist`, `housekeeping`).
    - [x] Buat tampilan halaman login (`resources/views/auth/login.blade.php`) menggunakan styling form TailAdmin Free.
    - [x] Buat database seeder untuk user default: Superadmin dan Resepsionis.
    - [x] Setup middleware auth dan proteksi route dashboard.

- [x] **1.3 Dashboard Wireframe & Metrics**
    - [x] Buat `DashboardController` dan view `resources/views/pages/dashboard/index.blade.php`.
    - [x] Pasang 4 kartu metrik TailAdmin di baris atas (Kamar Terisi, Tamu Hari Ini, Kamar Kotor/Housekeeping, Pendapatan Harian).
    - [x] Pasang tabel ringkas statis di dashboard untuk preview tamu yang akan check-in / check-out hari ini.

---

## Sprint 2: Master Data Management

_Tujuan: Menyediakan seluruh data master yang menjadi prasyarat transaksi reservasi._

- [x] **2.1 Master Customer / Tamu**
    - [x] Migration & Model `Customer` (dukungan NIK, WhatsApp, preferensi, dan status blacklist).
    - [x] Form Request validation & CRUD Controller `CustomerController`.
    - [x] View TailAdmin: Tabel daftar customer (dengan search bar & badge status blacklist).
    - [x] Modal Alpine.js cepat: `+ Tamu Baru` (untuk dipakai ulang di halaman booking).

- [x] **2.2 Master Kamar & Tipe Kamar**
    - [x] Migration & Model `RoomType` dan unit fisik `Room`.
    - [x] CRUD Tipe Kamar (harga dasar, kapasitas, fasilitas bawaan).
    - [x] CRUD Unit Kamar Fisik beserta status switcher (Available, Occupied, Dirty, Maintenance).

- [x] **2.3 Master Layanan, F&B & Inklusi Paket**
    - [x] Migration & Model `Service` (makanan, minuman, laundry, rental) dengan status pelacakan stok fisik.
    - [x] Migration `room_type_service_inclusions` (pengaturan jatah sarapan/layanan gratis per tipe kamar).
    - [x] Antarmuka pengaturan paket: Form toggle jatah makan (Pagi/Siang/Malam) dan plafon subsidi per tipe kamar.

- [x] **2.4 Migration, Model, dan CRUD Master Customer**
    - [x] Validasi nomor HP unik atau NIK unik.
    - [x] Tabel list customer dengan pencarian nama/no HP dan filter `is_blacklisted`.
    - [x] Halaman profil customer (melihat riwayat menginap & histori pesanan makanan).

---

## Sprint 3: Booking Engine & Front Desk Operations

_Tujuan: Mengelola alur reservasi, pencegahan overbooking, dan check-in/out._

- [x] **3.1 Booking Service & Validation**
    - [x] Migration `Booking`.
    - [x] `BookingConflictService`: Validasi anti-bentrok tanggal kamar fisik.
    - [x] Form pembuatan reservasi baru:
        - [x] Input pencarian autocomplete customer (pilih tamu lama atau klik tambah tamu baru).
        - [x] Pilih tipe kamar dan unit kamar tersedia.
        - [x] Opsi override manual tarif kamar dan uang muka/deposit.

- [x] **3.2 Operasional Front Desk (Active Guests & Calendar)**
    - [x] Halaman tabel Tamu Aktif (_In-House Guests_) dengan filter status booking.
    - [x] Tombol aksi cepat: Check-In (serah terima kunci & catat deposit) dan Check-Out.
    - [x] Integrasi FullCalendar via live endpoint `/bookings/calendar-events` untuk visualisasi jadwal okupansi kamar.

---

## Sprint 4: Mini-POS, Room Orders & Atomic Inventory

_Tujuan: Mengakomodasi pesanan makanan, minuman, dan layanan tambahan di luar paket._

- [x] **4.1 Pesanan Tambahan (Room Order)**
    - [x] Migration `BookingItem`.
    - [x] Modal Alpine.js "Order Room Service / Minibar" pada baris tabel tamu aktif dan halaman detail reservasi.
    - [x] Logika pembeda otomatis: item masuk kuota gratis paket kamar (Rp 0) vs pesanan berbayar ekstra.

- [x] **4.2 Atomic Inventory Deduction**
    - [x] Service `RoomOrderService`: Pengurangan stok menggunakan database transaction (`lockForUpdate`).
    - [x] Pilihan pembayaran: Bebankan ke kamar (`charged_to_room`) atau bayar langsung di tempat (`paid_instantly`).

---

## Sprint 5: Folio Billing, Invoice & Reporting

_Tujuan: Penyelesaian pembayaran saat check-out, penerbitan invoice, dan laporan keuangan._

- [x] **5.1 Folio Billing Engine**
    - [x] `FolioCalculationService`: Agregasi total sewa kamar + add-on ekstra - deposit - pembayaran langsung.
    - [x] Layar Checkout Settlement: Rincian item folio, input pelunasan sisa tagihan, dan update status kamar menjadi `dirty`.

- [x] **5.2 Cetak Invoice & Export**
    - [x] Pembuatan template invoice print-ready & simpan PDF rapi (`/billing/{booking}/invoice`).
    - [x] Tanda terima pembayaran / kwitansi catatan pelunasan folio kasir.
    - [x] Fitur export data reservasi dan pemasukan ke format Excel/CSV (`/billing/export-csv`).
