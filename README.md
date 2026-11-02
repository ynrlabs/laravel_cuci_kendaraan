# CuciKuy - Sistem Manajemen Cuci Kendaraan Terintegrasi

CuciKuy adalah platform manajemen operasional cuci kendaraan berbasis multi-cabang yang dirancang untuk mengelola seluruh siklus layanan cuci mobil dan motor. Sistem ini mengintegrasikan pemesanan online, penanganan pelanggan langsung (walk-in), antrean visual model Kanban di tingkat kasir, pemantauan pengerjaan oleh operator cuci, hingga administrasi eksekutif dan analitik performa cabang melalui dashboard terpadu.

---

## Daftar Isi

- [Arsitektur & Konsep Sistem](#arsitektur--konsep-sistem)
- [Fitur Utama](#fitur-utama)
- [Tech Stack](#tech-stack)
- [Prasyarat Sistem](#prasyarat-sistem)
- [Panduan Instalasi](#panduan-instalasi)
- [Akun Pengujian & Demo](#akun-pengujian--demo)
- [Perintah Operasional](#perintah-operasional)
- [Struktur Direktori](#struktur-direktori)
- [Pengujian & Kualitas Kode](#pengujian--kualitas-kode)
- [Keamanan & Lisensi](#keamanan--lisensi)

---

## Arsitektur & Konsep Sistem

Sistem menerapkan arsitektur *hybrid* yang menggabungkan backend monolitik berbasis Laravel dengan dua lapisan presentasi utama:

1. **Kasir & Operator Front-End**: Dibangun menggunakan Inertia.js dan React 19 untuk pengalaman pengguna berbasis Single-Page Application (SPA) yang interaktif, cepat, dan terhubung langsung ke WebSocket server (Laravel Reverb) guna sinkronisasi antrian secara instan tanpa memuat ulang halaman.
2. **Back-Office & Executive Panel**: Dikelola melalui Filament Admin Panel untuk manajemen data master (cabang, layanan, harga dinamis, inventaris, karyawan, dan pengaturan global) serta audit analitik.
3. **Multi-Branch Data Scoping**: Pemisahan data antar cabang ditegakkan pada middleware `branch.scope` dan Eloquent query builder. Pengguna dengan peran admin cabang, kasir, atau operator hanya dapat melihat dan memodifikasi data cabang mereka masing-masing, sedangkan super admin memiliki visibilitas global di seluruh cabang.

---

## Fitur Utama

### 1. Manajemen Antrean Kanban & POS Kasir
- Papan Kanban antrean cuci interaktif dengan kolom status: Menunggu, Dicuci, Pengeringan/Finishing, Selesai, dan Diambil.
- Pembuatan booking walk-in cepat untuk pelanggan langsung di lokasi.
- Validasi kode booking pelanggan berbasis pemindai QR Code (`html5-qrcode`).
- Proses *checkout* kasir dengan kalkulasi otomatis layanan utama, add-on tambahan, promo diskon, serta opsi cetak struk pembayaran.

### 2. Dashboard Pengerjaan Operator & Teknisi
- Tampilan daftar tugas cuci khusus operator di area cuci (*wash bay*).
- Penghitung durasi pengerjaan (*timer*) untuk memantau waktu standar pencucian.
- Dokumentasi foto progres dan foto hasil akhir pengerjaan sebelum kendaraan diserahkan ke kasir.

### 3. Panel Administrasi & Pelaporan (Filament)
- Manajemen multi-cabang dengan kuota harian dan konfigurasi slot pencucian per jam.
- Pengaturan harga dinamis berdasarkan kategori kendaraan (Motor, Mobil Kecil, Sedan, SUV, dsb).
- Grafik statistik pendapatan real-time, tingkat okupansi slot, dan tren kepuasan pelanggan.
- Ekspor laporan transaksi dan rekapitulasi performa finansial cabang ke format spreadsheet.

### 4. Role-Based Access Control (RBAC) Granular
- Kontrol hak akses berbasis peran menggunakan `spatie/laravel-permission` yang dikonfigurasi langsung dari antarmuka admin.
- Pembatasan izin aksi berbahaya (misalnya: penghapusan transaksi atau modifikasi skema harga) dengan konfirmasi keamanan.

### 5. Komunikasi Real-Time & Otomasi Pekerjaan
- Siaran pembaruan status booking melalui WebSocket private channel `booking.{id}` dan channel publik cabang `branch.{id}.queue`.
- Penjadwalan otomatis via Laravel Scheduler:
  - Pembatalan otomatis booking yang belum dibayar melewati batas waktu toleransi (*cutoff time*).
  - Pengiriman email dan push notification pengingat H-1 jadwal cuci.
  - Pengiriman permintaan ulasan pelanggan setelah pengerjaan selesai.
  - Sinkronisasi dan rekonsiliasi status gateway pembayaran.

---

## Tech Stack

### Backend
- **Framework**: Laravel 11 / 13 (PHP 8.3+)
- **API & Autentikasi**: Laravel Sanctum
- **Admin Panel**: Filament v3 / v5
- **Hak Akses & Otorisasi**: Spatie Laravel-Permission
- **Monitoring & Debugging**: Laravel Horizon, Laravel Telescope, Sentry

### Frontend & Real-Time
- **Layer Komunikasi**: Inertia.js v3
- **Library UI**: React 19, Tailwind CSS v4, Radix UI, Lucide Icons
- **WebSocket Server**: Laravel Reverb
- **Client Real-Time**: Laravel Echo, Pusher JS
- **Utilitas**: html5-qrcode, Ziggy JS

### Basis Data, Antrean, & Notifikasi
- **Database Utama**: MySQL 8.0+ / MariaDB / PostgreSQL / SQLite
- **Cache & Message Broker**: Redis (Predis) / Database Driver
- **Notifikasi Multi-Channel**: Firebase Cloud Messaging (FCM), SMTP Email, Database Notification

---

## Prasyarat Sistem

Sebelum memulai instalasi, pastikan lingkungan pengembangan telah memenuhi persyaratan berikut:

- PHP >= 8.3 dengan ekstensi aktif:
  - `pdo_mysql` (atau `pdo_sqlite` / `pdo_pgsql`)
  - `mbstring`
  - `openssl`
  - `tokenizer`
  - `xml`
  - `curl`
  - `bcmath`
  - `fileinfo`
- Composer >= 2.x
- Node.js >= 20.x & npm >= 10.x
- MySQL >= 8.0 atau MariaDB >= 10.4
- Redis Server (opsional untuk lingkungan lokal, dianjurkan untuk production)

---

## Panduan Instalasi

### 1. Kloning Repositori
```bash
git clone https://github.com/username/cucikuy.git
cd cucikuy
```

### 2. Pemasangan Dependensi PHP
```bash
composer install
```

### 3. Pemasangan Dependensi JavaScript
```bash
npm install
```

### 4. Konfigurasi Environment
Salin file konfigurasi contoh dan buat file `.env` baru:
```bash
cp .env.example .env
```

Buka file `.env` lalu sesuaikan kredensial basis data Anda:
```env
APP_NAME=CuciKuy
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cucikuy
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=database
CACHE_STORE=file
```

### 5. Pembuatan Kunci Aplikasi
```bash
php artisan key:generate
```

### 6. Eksekusi Migrasi & Data Seeder
Jalankan migrasi skema tabel beserta data awal (cabang, layanan, hak akses, dan akun pengujian):
```bash
php artisan migrate --seed
```

### 7. Kompilasi Aset Frontend
Untuk lingkungan pengembangan dengan Hot-Module Replacement (HMR):
```bash
npm run dev
```

Atau untuk build produksi:
```bash
npm run build
```

---

## Akun Pengujian & Demo

Setelah proses migrasi dan seeding selesai, sistem menyediakan akun default untuk keperluan verifikasi fitur:

| Peran (Role) | Alamat Email | Kata Sandi | Akses Portal |
|---|---|---|---|
| Super Admin | `admin@cucikuy.com` | `admin123` | Panel Admin (`/admin`) & Kasir |
| Super Admin (Cadangan) | `superadmin@cucikuy.com` | `password123` | Panel Admin (`/admin`) |
| Admin Cabang Pusat | `admin.cucikuy-pusat@cucikuy.com` | `password123` | Panel Admin Cabang (`/admin`) |
| Kasir Cabang Pusat | `kasir.cucikuy-pusat@cucikuy.com` | `password123` | POS Kasir & Kanban (`/kasir/dashboard`) |
| Supervisor / Operator | `supervisor.cucikuy-pusat@cucikuy.com` | `password123` | Dashboard Operator (`/kasir/operator`) |
| Pelanggan (Customer) | `budi@example.com` | `password123` | Portal Pelanggan |

> Catatan Keamanan: Seluruh akun pengujian di atas hanya ditujukan untuk lingkungan pengembangan lokal. Pastikan untuk mengganti kata sandi atau menghapus seeder pengujian sebelum mendeploy sistem ke lingkungan produksi.

---

## Perintah Operasional

Untuk menjalankan seluruh ekosistem layanan CuciKuy di lingkungan lokal:

### Menjalankan Server Web Laravel
```bash
php artisan serve
```
Aplikasi kasir dapat diakses di `http://localhost:8000/login` dan panel administrasi di `http://localhost:8000/admin`.

### Menjalankan Antrean Background Job
```bash
php artisan queue:listen --tries=1 --timeout=0
```
Jika menggunakan Laravel Horizon (berbasis Redis):
```bash
php artisan horizon
```

### Menjalankan WebSocket Server (Laravel Reverb)
```bash
php artisan reverb:start --debug
```

### Menjalankan Seluruh Worker Bersamaan (Mode Konkuren)
Tersedia skrip shortcut untuk menjalankan server, antrean, log tail, dan Vite secara bersamaan:
```bash
composer run dev
```

---

## Struktur Direktori

Berikut gambaran modul dan folder utama dalam proyek:

```text
├── app/
│   ├── Enums/                 # Definisi Enum (Status Booking, Posisi Karyawan, Role, dll.)
│   ├── Filament/              # Sumber daya, halaman, dan widget admin panel
│   │   ├── Pages/             # Halaman kustom (misal: GlobalSettings)
│   │   ├── Resources/         # CRUD Filament (Bookings, Branches, Roles, Services, dll.)
│   │   └── Widgets/           # Widget statistik dan analitik
│   ├── Http/
│   │   ├── Controllers/       # Controller web, autentikasi, dan API v1
│   │   └── Middleware/        # Middleware scoping cabang (BranchScope) & RBAC (CheckRole)
│   ├── Jobs/                  # Background jobs (AutoCancelBooking, SendReminder, dll.)
│   ├── Models/                # Eloquent models dan relasi entitas
│   ├── Notifications/         # Notifikasi sistem (FCM, Email Mailable, Database)
│   └── Policies/              # Otorisasi hak akses model
├── config/                    # File konfigurasi Laravel & package pihak ketiga
├── database/
│   ├── migrations/            # Skema basis data terstruktur
│   └── seeders/               # Data awal master dan akun demo
├── resources/
│   ├── js/                    # Sumber kode front-end (React 19 + Inertia.js)
│   │   ├── Components/        # Komponen UI reusable (Button, Input, Modal, Kanban, dll.)
│   │   └── Pages/             # Halaman view Inertia (Kasir, Antrian, Pembayaran, dll.)
│   └── views/                 # Blade templates dan komponen render hook Filament
├── routes/
│   ├── api.php                # Endpoint RESTful API untuk klien eksternal
│   ├── auth.php               # Rute autentikasi web
│   ├── channels.php           # Definisi authorization channel WebSocket Reverb
│   ├── console.php            # Penjadwalan cron scheduler Laravel
│   └── web.php                # Rute aplikasi internal kasir & operator
└── tests/                     # Suite pengujian unit dan integrasi
```

---

## Pengujian & Kualitas Kode

Sistem dilengkapi dengan rangkaian pengujian otomatis dan alat pemformatan kode:

Jalankan test suite menggunakan PHPUnit:
```bash
php artisan test
```

Periksa dan rapikan gaya penulisan kode sesuai standar PSR-12 menggunakan Laravel Pint:
```bash
./vendor/bin/pint --test
```

---

## Keamanan & Lisensi

### Praktik Keamanan Repositori Publik
- File kredensial `.env` dikecualikan dari pelacakan git (`.gitignore`).
- Parameter koneksi pihak ketiga (AWS, FCM, Midtrans, Mailgun) dikonfigurasi melalui variabel lingkungan (*environment variables*).
- Form input eksternal dilindungi oleh validasi skema ketat dan proteksi CSRF.

### Pelaporan Celah Keamanan
Apabila Anda menemukan celah keamanan dalam sistem ini, silakan buat laporan terperinci secara privat melalui menu [GitHub Security Advisories](https://github.com/) pada repositori ini atau hubungi penanggung jawab teknis proyek.

### Lisensi
Perangkat lunak ini dirilis di bawah lisensi terbuka [MIT License](LICENSE).
