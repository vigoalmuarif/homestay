# System Architecture & Technical Specifications

---

## 1. Skema Database DDL Lengkap (MySQL / MariaDB / PostgreSQL)

```sql
-- 1. Master Customer / Tamu
CREATE TABLE customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    identity_type ENUM('ktp', 'paspor', 'sim', 'other') DEFAULT 'ktp',
    identity_number VARCHAR(50) NULL UNIQUE,
    phone_number VARCHAR(30) NOT NULL INDEX,
    email VARCHAR(100) NULL,
    address TEXT NULL,
    country VARCHAR(100) DEFAULT 'Indonesia',
    special_notes TEXT NULL,
    is_blacklisted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- 2. Master Tipe & Unit Kamar
CREATE TABLE room_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    base_price DECIMAL(12, 2) NOT NULL,
    capacity INT UNSIGNED DEFAULT 2,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE rooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_type_id BIGINT UNSIGNED NOT NULL,
    room_number VARCHAR(20) NOT NULL UNIQUE,
    status ENUM('available', 'occupied', 'dirty', 'maintenance') DEFAULT 'available',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE CASCADE
);

-- 3. Master Katalog Layanan, Makanan & Minuman
CREATE TABLE services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category ENUM('meal', 'beverage', 'amenity', 'rental', 'laundry', 'other') NOT NULL,
    sub_category VARCHAR(50) NULL, -- 'breakfast', 'lunch', 'dinner', 'daily', etc.
    price DECIMAL(12, 2) NOT NULL,
    has_stock BOOLEAN DEFAULT FALSE,
    stock_quantity INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- 4. Inklusi / Jatah Fasilitas Bawaan Tipe Kamar
CREATE TABLE room_type_service_inclusions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_type_id BIGINT UNSIGNED NOT NULL,
    service_id BIGINT UNSIGNED NULL,
    category ENUM('meal', 'beverage', 'amenity', 'rental', 'laundry', 'any') NOT NULL,
    session_time VARCHAR(50) NULL, -- 'breakfast', 'lunch', 'dinner'
    allowance_limit DECIMAL(12, 2) DEFAULT 0.00, -- 0.00 = full cover tanpa plafon harga
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
);

-- 5. Transaksi Reservasi
CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_code VARCHAR(30) NOT NULL UNIQUE,
    customer_id BIGINT UNSIGNED NOT NULL,
    room_id BIGINT UNSIGNED NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    room_price_snapshot DECIMAL(12, 2) NOT NULL,
    deposit_amount DECIMAL(12, 2) DEFAULT 0.00,
    status ENUM('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE RESTRICT,
    INDEX idx_dates_room (room_id, check_in_date, check_out_date)
);

-- 6. Detail Item Tagihan (Layanan Paket & Mini-POS)
CREATE TABLE booking_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    service_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED DEFAULT 1,
    unit_price DECIMAL(12, 2) NOT NULL,
    is_complimentary BOOLEAN DEFAULT FALSE,
    extra_charge DECIMAL(12, 2) DEFAULT 0.00,
    source_type ENUM('package_benefit', 'room_order', 'minibar') DEFAULT 'room_order',
    payment_status ENUM('charged_to_room', 'paid_instantly') DEFAULT 'charged_to_room',
    payment_method VARCHAR(50) NULL,
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
);
```
