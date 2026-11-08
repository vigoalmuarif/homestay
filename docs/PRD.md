# Product Requirements Document (PRD)

## Homestay Operations & Mini-POS System

### 1. Ringkasan Eksekutif & Tujuan

Sistem manajemen homestay berbasis Laravel 12 dan TailAdmin (Free Edition) untuk mengelola reservasi kamar, hak inklusi paket/layanan (_complimentary benefits_), mini-POS/katalog makanan-minuman berbayar, serta kalkulasi tagihan kamar terintegrasi (_folio billing_).

### 2. User Roles & Hak Akses

1. **Owner / Manager:** Akses laporan laba-rugi, ringkasan okupansi, dan kontrol harga master.
2. **Receptionist / Front Desk:** Mengelola pemesanan kamar, check-in/out, input order makanan/layanan, dan menerima pembayaran.
3. **Housekeeping:** Melihat daftar kamar yang berstatus kotor (_dirty_) dan mengubahnya menjadi bersih (_clean_).

---

### 3. Modul & Spesifikasi Fungsional

#### A. Kamar & Penetapan Harga (Room & Tier Inventory)

- Mengelola tipe kamar (misal: _VIP_, _Regular_) dan unit fisik (misal: _Kamar 101_, _Kamar 102_).
- Status kamar: `Available`, `Occupied`, `Dirty`, `Maintenance`.
- Pencegahan _double-booking_: Validasi bentrok rentang tanggal (`check_in_date` s.d. `check_out_date`) pada unit fisik yang sama.

#### B. Aturan Hak Paket Layanan (Complimentary Service Inclusions)

- Setiap tipe kamar atau paket reservasi dapat memiliki jatah gratis:
    - **Sesi Makan:** Sarapan pagi, makan siang, atau makan malam.
    - **Layanan Tambahan:** Penjemputan, extra bed, sewa kendaraan, laundry.
- Admin memiliki hak _manual override_ saat input reservasi untuk menambah/menghapus jatah inklusi tamu tertentu.
- **Batas Subsidi (Allowance Limit):** Jika menu makanan melebihi batas subsidi gratis kamar, selisih nominalnya otomatis dialihkan sebagai tagihan ekstra (_extra charge_).

#### C. Katalog Penjualan Tambahan & Mini-POS (Non-Package Sales)

- Menjual makanan, minuman, minibar, dan layanan ekstra di luar paket kamar.
- Pilihan pembayaran pesanan:
    1. `charged_to_room`: Ditambahkan ke tagihan akhir kamar saat check-out.
    2. `paid_instantly`: Langsung dibayar saat pesanan diantar (tunai/QRIS) tanpa menambah sisa tagihan check-out.
- Item fisik yang memiliki stok (`has_stock = true`) wajib dipotong otomatis secara atomik di database.

#### D. Folio Billing & Settlement

- Menghitung total tagihan bersih:
  $$\text{Total Bayar} = (\text{Sewa Kamar}) + (\text{Tagihan Ekstra Layanan}) + (\text{Pesanan Beban Kamar}) - (\text{Deposit/DP}) - (\text{Pembayaran Langsung})$$
- Status pembayaran reservasi: `Unpaid`, `Partial/DP`, `Paid`.
- Cetak invoice dan kwitansi berformat PDF menggunakan `barryvdh/laravel-dompdf`.

---

### 4. Batasan Teknis (Constraints)

- Menggunakan **TailAdmin Free Version**. Dilarang menggunakan aset atau library berbayar milik TailAdmin Pro.
- Interaktivitas UI menggunakan **Alpine.js**.
- Input moneter menggunakan tipe data database `DECIMAL(12, 2)` (tidak boleh `FLOAT` atau `DOUBLE`).
