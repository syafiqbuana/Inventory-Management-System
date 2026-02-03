# Manajemen Inventaris

Sistem informasi manajemen inventaris untuk perusahaan/instansi yang komprehensif dengan fitur tracking stok real-time, laporan mutasi terperinci, manajemen pembelian & penggunaan, serta periode pengelolaan inventaris.

## 📋 Daftar Isi

- [Tentang Aplikasi](#tentang-aplikasi)
- [Fitur Utama](#fitur-utama)
- [Teknologi yang Digunakan](#teknologi-yang-digunakan)
- [Requirement](#requirement)
- [Instalasi](#instalasi)
- [Struktur Database](#struktur-database)
- [Panduan Penggunaan](#panduan-penggunaan)
- [Entitas & Relationship](#entitas--relationship)
- [Fitur Keamanan](#fitur-keamanan)
- [Troubleshooting](#troubleshooting)

---

## 🎯 Tentang Aplikasi

**Manajemen Inventaris** adalah sistem web berbasis Laravel yang dirancang untuk membantu perusahaan atau instansi dalam mengelola dan melacak inventaris/aset mereka secara real-time. Aplikasi ini menyediakan dashboard interaktif, laporan mutasi terperinci, tracking stok otomatis, dan manajemen periode pengelolaan inventaris.

### Target User
- **Staf Inventaris** - Menginput data item, pembelian, dan penggunaan
- **Manager/Pimpinan** - Melihat laporan dan analisis inventaris
- **Administrator** - Mengelola user, master data, dan konfigurasi sistem

---

## ✨ Fitur Utama

### 1. **Manajemen Item/Inventaris**
- ✅ Input data item baru dengan kategori dan satuan
- ✅ Input multiple item secara bersamaan (bulk create)
- ✅ Edit/update informasi item
- ✅ Hapus item dari sistem
- ✅ Search dan filter item berdasarkan kategori
- ✅ Tracking harga item dari pembelian terbaru
- ✅ View detail stok item per periode

### 2. **Tracking Stok & Mutasi Real-Time**
- ✅ Pencatatan stok awal per periode
- ✅ Perhitungan stok otomatis: `Total Stok = Stok Awal + Pembelian - Penggunaan`
- ✅ Tracking pembelian (Purchase) dengan detail supplier
- ✅ Tracking penggunaan/pemakaian (Usage) dengan keterangan
- ✅ Riwayat mutasi stok per item
- ✅ Snapshot stok per periode (period_stocks)
- ✅ Validasi stok sebelum pencatatan penggunaan

### 3. **Manajemen Pembelian**
- ✅ Input pembelian item dengan harga satuan
- ✅ Multi-item purchases dalam satu transaksi
- ✅ Tracking supplier dan tanggal pembelian
- ✅ Perhitungan subtotal dan total otomatis
- ✅ Dua tab input: Item existing dan Item baru
- ✅ Edit pembelian dengan perubahan saldo otomatis
- ✅ Hapus pembelian dengan pengembalian saldo
- ✅ Riwayat pembelian per item
- ✅ Filter berdasarkan tanggal

### 4. **Manajemen Penggunaan/Pemakaian**
- ✅ Pencatatan penggunaan item dengan detail keterangan
- ✅ Multi-item usage dalam satu transaksi
- ✅ Validasi stok ketersediaan sebelum mencatat penggunaan
- ✅ Tracking departemen/bagian yang menggunakan (used_for field)
- ✅ Riwayat penggunaan per item dengan detail kategori
- ✅ Filter penggunaan berdasarkan kategori dan tanggal

### 5. **Manajemen Saldo (Balance)**
- ✅ Pencatatan saldo awal sistem
- ✅ Update saldo otomatis saat pembelian
- ✅ Pengembalian saldo saat edit/hapus pembelian
- ✅ Tambahan saldo saat input pemasukan (income)
- ✅ Validasi saldo minimum sebelum pembelian
- ✅ Tracking historis perubahan saldo

### 6. **Manajemen Pemasukan (Income)**
- ✅ Input pemasukan/revenue dengan jumlah dan sumber
- ✅ Update saldo otomatis saat input pemasukan
- ✅ Filter pemasukan berdasarkan tanggal
- ✅ Tracking sumber pemasukan
- ✅ Riwayat pemasukan harian

### 7. **Manajemen Periode**
- ✅ Buat periode pengelolaan baru (per tahun)
- ✅ Set periode aktif untuk operasional
- ✅ Close periode dengan snapshot stok otomatis
- ✅ Pemindahan stok ke periode berikutnya
- ✅ Arsip periode lama
- ✅ Laporan per periode spesifik

<<<<<<< HEAD
### 8. **Laporan & Export**
- ✅ **Laporan Data Barang** - Detail stok per item dengan filter kategori
- ✅ **Laporan Pembelian** - Data transaksi pembelian dengan detail item
- ✅ **Laporan Penggunaan** - Data transaksi penggunaan dengan kategori
- ✅ **Laporan Mutasi Saldo** - Tracking pemasukan, pengeluaran, dan saldo berjalan
- ✅ **Laporan Mutasi Barang** - Detail perubahan stok per item
- ✅ Export ke PDF dengan format profesional
- ✅ Filter laporan berdasarkan periode, kategori, dan rentang tanggal
- ✅ Print directly dari sistem
- ✅ Header dan footer profesional di setiap laporan
=======
### 6. **Laporan & Export**
- ✅ Laporan Mutasi Stok (detail pembelian, penggunaan, stok)
- ✅ Export ke PDF
- ✅ Filter laporan berdasarkan periode & kategori
>>>>>>> 6d3e499e9a4ed2d24249c628df2a1a4b3ec1b8a0

### 9. **Dashboard & Analytics**
- ✅ Widget jumlah item
- ✅ Widget jumlah kategori
- ✅ Widget role user yang login
- ✅ Widget jumlah user (admin only)
- ✅ Stats transaksi pembelian (daily, total)
- ✅ Stats transaksi penggunaan (daily, total, item terpopuler)
- ✅ Stats pemasukan (daily, total)
- ✅ Stats pengeluaran (total pembelian)
- ✅ Stats saldo keseluruhan
- ✅ Widget latest users (admin only)

<<<<<<< HEAD
### 10. **Master Data Management**
- ✅ Manajemen Kategori Item
- ✅ Manajemen Satuan Item (Item Type)
- ✅ Manajemen User dengan role (Admin/Staff)
- ✅ Edit user dan password
- ✅ Hapus user dari sistem

### 11. **Keamanan & Access Control**
- ✅ Authentication login dengan email & password
- ✅ Role-based Access Control (RBAC) - Admin & Staff
- ✅ Fitur admin-only untuk user management
- ✅ Fitur staff untuk input pembelian & penggunaan
- ✅ Audit trail (created_by, created_at, updated_at)
- ✅ Password hashing dengan bcrypt
- ✅ Session management
=======
### 9. **Manajemen Master Data**
- ✅ Kategori Item (Category)
- ✅ Satuan Item (Item Type)
- ✅ User & Role Management
- ✅ Audit Trail (siapa membuat/mengubah data)
>>>>>>> 6d3e499e9a4ed2d24249c628df2a1a4b3ec1b8a0

---

## 🛠️ Teknologi yang Digunakan

| Teknologi | Versi | Fungsi |
|-----------|-------|--------|
| **PHP** | 8.2+ | Server-side language |
| **Laravel** | 11.0 | Web framework |
| **MySQL** | 5.7+ | Database management |
| **Filament** | 3.3 | Admin panel & UI components |
| **DomPDF** | 3.1 | Generate PDF reports |
| **Vite** | Latest | Frontend build tool |
| **Tailwind CSS** | 3.x | CSS framework |
| **Alpine JS** | Latest | JavaScript framework |
| **Composer** | Latest | PHP package manager |

### Development Tools
- Laravel Tinker - Interactive shell
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
npm install
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

### 7. Build Frontend Assets
```bash
npm run build
```

### 8. Jalankan Aplikasi
```bash
php artisan serve
```

Akses di: `http://localhost:8000`

**Default Admin Account:**
- Email: `admin@example.com`
- Password: `12345`

---

## 🗄️ Struktur Database

### Tabel Utama

#### **users**
- Menyimpan data pengguna sistem
- Fields: id, name, email, password, role (admin/staff), created_at, updated_at

#### **categories**
- Kategori item (Elektronik, Furniture, ATK, dll)
- Fields: id, name, created_at, updated_at

#### **item_types**
- Satuan item (Pcs, Box, Set, Meter, dll)
- Fields: id, name, created_at, updated_at

#### **periods**
- Periode pengelolaan (Tahun 2025, 2026, dll)
- Fields: id, year, is_closed, closed_at, created_at, updated_at

#### **items**
- Data item/inventaris
- Fields: id, name, category_id, item_type_id, initial_stock, price, initial_period_id, created_by, created_at, updated_at

#### **purchases**
- Transaksi pembelian
- Fields: id, note, purchase_date, total_amount, created_by, created_at, updated_at

#### **purchase_items**
- Detail item dalam pembelian
- Fields: id, purchase_id, item_id, qty, unit_price, subtotal, supplier, created_at, updated_at

#### **usages**
- Transaksi penggunaan/pemakaian
- Fields: id, item_id, qty, used_for, note, created_by, created_at, updated_at

#### **period_stocks**
- Snapshot stok per periode (untuk tracking history)
- Fields: id, period_id, item_id, initial_stock, final_stock, price, created_at, updated_at

---

## 📖 Panduan Penggunaan

### Alur Umum Penggunaan

#### 1. **Setup Awal**
```
Login dengan Admin → Input Master Data (Kategori, Satuan, User) 
→ Input Item Awal dengan Stok Awal → Set Saldo Awal
```
#### 2. **Pembelian**
```
Pembelian memiliki 2 Tab yaitu Tab untuk pembelian dengan harga yang sama (membeli lagi)
Dan tab untuk pembelian dengan harga yang berbeda,di tab ini user akan mendaftarkan terlebih dahulu item yang akan dibeli,setelah mendaftarkan user dapat melakukan pembelian seperti di tab pertama
```
#### 3. **Usage**
```
Usage atau penggunaan, User dapat mencatat penggunaan barang yang akan digunakan oleh orang lain,user juga dapat mencetak bukti permohonan penggunaan barang
```

#### 4. **Laporan**
```
Buka Laporan (Barang/Pembelian/Penggunaan/Saldo) 
→ Filter sesuai kebutuhan → Export PDF/Print
```

#### 5. **Penutupan Periode**
```
Tutup Periode Lama → Sistem buat snapshot stok → Periode baru otomatis dibuat
```
#### 6. **Laporan Mutasi Item**
```
User dapat melihat mutasi Item, Item apa saja yang dibeli, digunakan, dan sisa item yang tersedia melalui sidebar di bagian paling bawah 
```
---

### Contoh Workflow Lengkap

**Scenario:** Mengelola inventaris kantor untuk tahun 2025


#### 1. **Input Kategori & Satuan**
- Masuk ke: Master Data → Kategori
- Input: Elektronik, Furniture, ATK, dll
- Masuk ke: Master Data → Satuan
- Input: Pcs, Box, Set, Meter, dll

#### 2. **Input Item dengan Stok Awal**
- Navigasi ke: Master Data → Item
- Klik "Tambah Item"
- Isi: Nama, Kategori, Satuan, Stok Awal, Harga
- Bisa tambah multiple item sekaligus
- Simpan

#### 3. **Input Pembelian Item**
- Navigasi ke: Transaksi → Pembelian
- Klik "Tambah Pembelian"
- Tab 1: Input item existing
  - Pilih item, qty, harga satuan
  - Subtotal otomatis terhitung
- Tab 2: Buat item baru langsung
  - Input nama, kategori, satuan, qty, harga
  - Item baru langsung ditambahkan ke stok awal
- Total pembelian otomatis terhitung
- Simpan → Saldo berkurang otomatis

#### 4. **Input Penggunaan Item**
- Navigasi ke: Transaksi → Penggunaan
- Klik "Tambah Penggunaan"
- Tambah item dalam repeater:
  - Pilih item, qty yang digunakan
  - Isi keterangan penggunaan (used_for)
- Sistem validasi stok ketersediaan
- Simpan → Stok otomatis berkurang

#### 5. **Tutup Periode (End of Year)**
- Navigasi ke: Periode
- Klik "Tutup Periode" di bagian tabel
- Sistem otomatis:
  - Create snapshot stok final
  - Create periode baru dengan stok awal = stok akhir periode sebelumnya
  - Update item ke periode baru

---

## 📊 Entitas & Relationship

```
User (1) ──────→ (Many) Items [created_by]
User (1) ──────→ (Many) Purchases [created_by]
User (1) ──────→ (Many) Usages [created_by]

Category (1) ──────→ (Many) Items
ItemType (1) ──────→ (Many) Items
Period (1) ──────→ (Many) Purchases
Period (1) ──────→ (Many) PeriodStocks

Item (1) ──────→ (Many) PurchaseItems
Item (1) ──────→ (Many) Usages
Item (1) ──────→ (Many) PeriodStocks

Purchase (1) ──────→ (Many) PurchaseItems
```

---

## 🔑 Fitur Keamanan

- ✅ Authentication & Authorization dengan email/password
- ✅ Role-based Access Control (RBAC) - Admin & Staff
- ✅ Password hashing dengan bcrypt
- ✅ Audit trail (created_by, created_at, updated_at)
- ✅ Session management
- ✅ CSRF protection
- ✅ Validasi input data di setiap form
- ✅ Authorization checks untuk resource access
- ✅ Soft delete support (ready for implementation)

---

## 🐛 Troubleshooting

### Error: "Saldo belum dibuat!"
**Solusi:** Pastikan ada data saldo di Transaksi → Saldo. Buat saldo baru jika belum ada.

### Error: "Stok tidak mencukupi" saat input penggunaan
**Solusi:** Periksa stok ketersediaan item. Pastikan pembelian sudah dicatat untuk menambah stok.

### Stok tidak terupdate
**Solusi:** 
- Pastikan pembelian/penggunaan sudah di-submit
- Periksa periode aktif di Master Data → Periode
- Refresh halaman browser

### Laporan tidak menampilkan data
**Solusi:**
- Pastikan ada data untuk periode yang dipilih
- Periksa filter tanggal/kategori
- Refresh halaman dan coba lagi

### Import/Export error
**Solusi:**
- Pastikan format file Excel sesuai template
- Periksa permission folder storage
- Pastikan disk space cukup

---

## 📞 Support & Kontribusi

Untuk pertanyaan, bug report, atau kontribusi, silakan hubungi tim development atau buat issue di repository.

---

## 📄 Lisensi

MIT License - Bebas digunakan untuk keperluan komersial maupun non-komersial.

---

## 📅 Changelog

- **v1.0.0** (Januari 2026) - Initial Release
  - ✅ Manajemen Item, Pembelian, Penggunaan
  - ✅ Tracking Stok Real-Time
  - ✅ Manajemen Saldo & Pemasukan
  - ✅ Laporan Mutasi Stok & Saldo (PDF)
  - ✅ Dashboard & Analytics
  - ✅ Manajemen Periode dengan Snapshot
  - ✅ Role-based Access Control (Admin/Staff)
  - ✅ Master Data Management

---

**Dikembangkan oleh syafiqbuana menggunakan Laravel & Filament**