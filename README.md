# Manajemen Inventaris

Sistem informasi manajemen inventaris untuk perusahaan/instansi yang komprehensif dengan fitur tracking stok, laporan mutasi, dan periode pengelolaan inventaris.

## 📋 Daftar Isi

- [Tentang Aplikasi](#tentang-aplikasi)
- [Fitur Utama](#fitur-utama)
- [Teknologi yang Digunakan](#teknologi-yang-digunakan)
- [Requirement](#requirement)
- [Instalasi](#instalasi)
- [Struktur Database](#struktur-database)
- [Panduan Penggunaan](#panduan-penggunaan)

## 🎯 Tentang Aplikasi

**Manajemen Inventaris** adalah sistem web berbasis Laravel yang dirancang untuk membantu perusahaan atau instansi dalam mengelola dan melacak inventaris/aset mereka. Aplikasi ini menyediakan dashboard interaktif, laporan mutasi terperinci, dan tracking stok real-time.

### Target User
- **Staf Inventaris** - Menginput dan memperbarui data inventaris
- **Manager** - Melihat laporan dan analisis inventaris
- **Administrator** - Mengelola user dan konfigurasi sistem

---

## ✨ Fitur Utama

### 1. **Manajemen Item/Inventaris**
- ✅ Input data item baru dengan kategori dan satuan
- ✅ Edit/update informasi item
- ✅ Hapus item dari sistem
- ✅ Search dan filter item berdasarkan kategori, periode, dll

### 2. **Tracking Stok & Mutasi**
- ✅ Pencatatan stok awal per periode
- ✅ Tracking pembelian (Purchase)
- ✅ Tracking penggunaan/pemakaian (Usage)
- ✅ Perhitungan stok otomatis: `Stok = Stok Awal + Pembelian - Penggunaan`
- ✅ Laporan Mutasi berdasarkan periode

### 3. **Manajemen Pembelian**
- ✅ Input pembelian item dengan harga satuan
- ✅ Multi-item purchases dalam satu transaksi
- ✅ Tracking supplier dan tanggal pembelian
- ✅ Riwayat pembelian per item

### 4. **Manajemen Penggunaan/Pemakaian**
- ✅ Pencatatan penggunaan item
- ✅ Multi-item usage dalam satu transaksi
- ✅ Tracking departemen/bagian yang menggunakan
- ✅ Riwayat penggunaan per item

### 5. **Manajemen Periode**
- ✅ Buat periode pengelolaan baru (per tahun/bulan)
- ✅ Set periode aktif untuk operasional
- ✅ Laporan per periode spesifik
- ✅ Arsip periode lama

### 6. **Laporan & Export**
- ✅ Laporan Mutasi Stok (detail pembelian, penggunaan, stok)
- ✅ Export ke PDF
- ✅ Filter laporan berdasarkan periode & kategori

### 7. **Dashboard**
- ✅ Widget Item Terbaru
- ✅ Visualisasi data inventaris
- ✅ Summary stok per kategori
- ✅ Info pembelian & penggunaan terkini

### 9. **Manajemen Master Data**
- ✅ Kategori Item (Category)
- ✅ Satuan Item (Item Type)
- ✅ User & Role Management
- ✅ Audit Trail (siapa membuat/mengubah data)

---

## 🛠️ Teknologi yang Digunakan

| Teknologi | Versi | Fungsi |
|-----------|-------|--------|
| **PHP** | 8.2+ | Server-side language |
| **Laravel** | 11.0 | Web framework |
| **MySQL** | 5.7+ | Database management |
| **Filament** | 3.3 | Admin panel & UI components |
| **DomPDF** | 3.1 | Generate PDF reports |
| **Laravel Tinker** | 2.9 | Interactive shell |
| **Composer** | Latest | Package manager |

### Development Tools
- Laravel Debugbar - Debug toolbar
- PHPUnit - Unit testing
- Faker - Generate fake data
- Laravel Pint - Code formatting

---

## 📋 Requirement

### Sistem Operasi
- Windows / Linux / macOS

### Software yang Diperlukan
- **PHP** >= 8.2
- **Composer** (latest)
- **MySQL** 5.7+ atau MariaDB 10.3+
- **Node.js** & **npm** (untuk aset frontend)

### Web Server
- Apache (dengan mod_rewrite)
- Nginx
- Built-in PHP server (untuk development)

---

## 🚀 Instalasi

### 1. Clone Repository
```bash
cd d:\laragon\www
git clone <repository-url> inventaris2
cd inventaris2
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Konfigurasi Database
Edit file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_inventaris2
DB_USERNAME=root
DB_PASSWORD=
```

Buat database:
```bash
mysql -u root -e "CREATE DATABASE db_inventaris2;"
```

### 5. Migrasi Database
```bash
php artisan migrate
```

### 6. Seed Data (Opsional)
```bash
php artisan db:seed
```

### 7. Setup Filament
```bash
php artisan filament:install
```

### 8. Jalankan Aplikasi
```bash
php artisan serve
```

Akses di: `http://localhost:8000`

---

## 🗄️ Struktur Database

### Tabel Utama

#### **users**
- Menyimpan data pengguna sistem
- Fields: id, name, email, password, created_at, updated_at

#### **items**
- Data item/inventaris
- Fields: id, name, category_id, item_type_id, initial_stock, price, initial_period_id, created_by, created_at, updated_at

#### **categories**
- Kategori item (Elektronik, Furniture, ATK, dll)
- Fields: id, name, description, created_at, updated_at

#### **item_types**
- Satuan item (Pcs, Box, Set, Meter, dll)
- Fields: id, name, created_at, updated_at

#### **periods**
- Periode pengelolaan (Tahun 2024, 2025, dll)
- Fields: id, year, is_active, start_date, end_date, created_at, updated_at

#### **purchases**
- Transaksi pembelian
- Fields: id, period_id, purchase_date, notes, created_by, created_at, updated_at

#### **purchase_items**
- Detail item dalam pembelian
- Fields: id, purchase_id, item_id, quantity, unit_price, created_at, updated_at

#### **usages**
- Transaksi penggunaan/pemakaian
- Fields: id, period_id, usage_date, department, notes, created_by, created_at, updated_at

#### **usage_items**
- Detail item dalam penggunaan
- Fields: id, usage_id, item_id, quantity, created_at, updated_at

#### **period_stocks**
- Snapshot stok per periode (untuk tracking history)
- Fields: id, period_id, item_id, initial_stock, purchased_qty, used_qty, created_at, updated_at

---

## 📖 Panduan Penggunaan

### Alur Umum Penggunaan

#### 1. **Setup Awal**
```
Login → Buat Periode → Input Master Data (Kategori, Satuan) 
→ Input Item → Set Stok Awal
```

#### 2. **Operasional Harian**
```
Input Pembelian → Update Stok Otomatis
Input Penggunaan → Update Stok Otomatis
```

#### 3. **Laporan**
```
View Laporan Mutasi → Filter Periode & Kategori → Export PDF/Excel
```

### Contoh Workflow Lengkap

**Scenario:** Mengelola inventaris kantor untuk tahun 2025

1. **Buat Periode Baru**
   - Navigasi ke: Master Data → Periode
   - Klik "Tambah Periode"
   - Tahun: 2025, Set sebagai Active
   - Simpan

2. **Input Kategori & Satuan**
   - Masuk ke: Master Data → Kategori
   - Input: Elektronik, Furniture, ATK, dll
   - Masuk ke: Master Data → Satuan
   - Input: Pcs, Box, Set, Meter, dll

3. **Input Item**
   - Navigasi ke: Inventaris → Item
   - Klik "Tambah Item"
   - Isi: Nama, Kategori, Satuan, Stok Awal, Harga
   - Simpan

4. **Input Pembelian**
   - Navigasi ke: Transaksi → Pembelian
   - Klik "Tambah Pembelian"
   - Pilih item, qty, harga satuan
   - Simpan → Stok otomatis terupdate

5. **Input Penggunaan**
   - Navigasi ke: Transaksi → Penggunaan
   - Klik "Tambah Penggunaan"
   - Pilih item, qty, departemen
   - Simpan → Stok otomatis terupdate

6. **Buat Laporan**
   - Navigasi ke: Laporan → Mutasi Stok
   - Filter: Periode, Kategori, Tanggal
   - Klik "Export PDF" atau "Export Excel"

---

## 📊 Entitas & Relationship

```
User (1) ──────→ (Many) Items
User (1) ──────→ (Many) Purchases
User (1) ──────→ (Many) Usages

Category (1) ──────→ (Many) Items
ItemType (1) ──────→ (Many) Items
Period (1) ──────→ (Many) Items
Period (1) ──────→ (Many) Purchases
Period (1) ──────→ (Many) Usages

Purchase (1) ──────→ (Many) PurchaseItems
Usage (1) ──────→ (Many) UsageItems

Item (1) ──────→ (Many) PurchaseItems
Item (1) ──────→ (Many) UsageItems
Item (1) ──────→ (Many) PeriodStocks
```

---

## 🔑 Fitur Keamanan

- ✅ Authentication & Authorization
- ✅ Role-based Access Control (RBAC)
- ✅ Password hashing dengan bcrypt
- ✅ Audit trail (created_by, updated_at)
- ✅ Session management di database
- ✅ CSRF protection

---

## 🐛 Troubleshooting

### Error: "No active period found"
**Solusi:** Pastikan ada minimal satu periode yang aktif di Master Data → Periode

### Stok tidak terupdate
**Solusi:** Pastikan pembelian/penggunaan sudah di-submit dan periode-nya aktif

### Import file gagal
**Solusi:** Pastikan format file Excel sesuai dengan template, dan data tidak ada yang duplikat

---

## 📞 Support & Kontribusi

Untuk pertanyaan atau kontribusi, silakan hubungi tim development.

---

## 📄 Lisensi

MIT License - Bebas digunakan untuk keperluan komersial maupun non-komersial.

---

## 📅 Changelog

- **v1.0.0** (Februari 2026) - Initial Release
  - Manajemen Item, Pembelian, Penggunaan
  - Laporan Mutasi Stok
  - Dashboard & Analytics
  - Import/Export Data
