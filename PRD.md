# PRD — CuciKuy (Sistem Manajemen Cuci Kendaraan)

> **Versi Dokumen:** 1.0
> **Tanggal:** 22 Juli 2026
> **Status:** Draf

---

## Daftar Isi

1. [ ] [Ringkasan Eksekutif](#1-ringkasan-eksekutif)
2. [ ] [Tujuan &amp; Ruang Lingkup](#2-tujuan--ruang-lingkup)
3. [ ] [Tech Stack &amp; Arsitektur](#3-tech-stack--arsitektur)
4. [ ] [Pengguna &amp; Peran](#4-pengguna--peran)
5. [ ] [Modul A — Aplikasi Customer (Flutter)](#5-modul-a--aplikasi-customer-flutter)
6. [ ] [Modul B — Aplikasi Kasir/Operator (Web/Mobile)](#6-modul-b--aplikasi-kasiroperator-webmobile)
7. [ ] [Modul C — Dashboard Admin (FilamentPHP)](#7-modul-c--dashboard-admin-filamentphp)
8. [ ] [Desain Database (ERD)](#8-desain-database-erd)
9. [ ] [API Design &amp; Integrasi](#9-api-design--integrasi)
1. [ ] [Integrasi Payment Gateway](#10-integrasi-payment-gateway)
1. [ ] [Notifikasi &amp; Real-Time](#11-notifikasi--real-time)
1. [ ] [Keamanan &amp; Autentikasi](#12-keamanan--autentikasi)
1. [ ] [Non-Fungsiinal](#13-non-fungsiinal)
1. [ ] [Milestone &amp; Timeline](#14-milestone--timeline)
1. [ ] [Risiko &amp; Mitigasi](#15-risiko--mitigasi)
1. [ ] [Glossary](#16-glossary)

---

## 1. Ringkasan Eksekutif

**CuciKuy** adalah platform digital end-to-end untuk bisnis cuci kendaraan (mobil & motor). Sistem ini terdiri dari 3 komponen utama:

| Komponen                          | Platform                      | Target Pengguna             |
| --------------------------------- | ----------------------------- | --------------------------- |
| **Aplikasi Customer**       | Flutter (iOS & Android)       | Pelanggan akhir             |
| **Aplikasi Kasir/Operator** | Web (Inertia.js) + PWA Mobile | Kasir & pencuci di lapangan |
| **Dashboard Admin**         | Web (FilamentPHP)             | Pemilik & manajer bisnis    |

Sistem memungkinkan pelanggan melakukan booking online, pembayaran digital, dan memantau status pencucian secara real-time. Di sisi internal, kasir dan admin memiliki kontrol penuh atas operasional harian, keuangan, dan manajemen sumber daya.

---

## 2. Tujuan & Ruang Lingkup

### 2.1 Tujuan

- **Meningkatkan konversi booking** dengan proses pemesanan yang cepat (< 2 menit).
- **Mengurangi no-show** melalui sistem reminder dan deposit digital.
- **Mengoptimalkan utilisasi slot** waktu pencucian per hari.
- **Menyediakan transparansi keuangan** real-time bagi pemilik bisnis.
- **Memudahkan manajemen operasional** (jadwal karyawan, kuota layanan, dll).

### 2.2 Ruang Lingkup

#### In-Scope

- Multi-cabang / multi-branch support.
- Pemesanan booking online dengan pemilihan slot waktu.
- 4 jenis layanan: Cuci Biasa, Wax, Detailing, Home Service/Panggilan.
- Integrasi pembayaran: QRIS, E-Wallet, Transfer Bank (via Midtrans/Xendit).
- Pembayaran tunai (on-the-spot) untuk walk-in customer.
- Status pengerjaan real-time dengan notifikasi push.
- Laporan keuangan & statistik (harian, mingguan, bulanan).
- Manajemen karyawan, harga, layanan, dan kuota slot.
- QR Code booking untuk verifikasi di kasir.

#### Out-of-Scope (v1)

- Sistem point/poin loyalitas (di v2).
- Marketplace mitra cuci third-party.
- Integrasi dengan sistem akuntansi eksternal (Jurnal, Zoho).
- Aplikasi mobile terpisah untuk kasir (akan menggunakan PWA).
- Sistem franchise multi-tenant.

---

## 3. Tech Stack & Arsitektur

### 3.1 Tech Stack

| Layer                        | Teknologi                      | Keterangan                              |
| ---------------------------- | ------------------------------ | --------------------------------------- |
| **Backend API**        | Laravel 11+ (PHP 8.2+)         | REST API + WebSocket (Laravel Reverb)   |
| **Admin Dashboard**    | FilamentPHP 3+                 | CRUD admin panel, laporan, manajemen    |
| **Web Kasir/Operator** | Inertia.js + React             | SPA responsive, bisa diakses mobile     |
| **Mobile Customer**    | Flutter 3+                     | Cross-platform (iOS & Android)          |
| **Database**           | MySQL 8.0+                     | Primary data store                      |
| **Cache**              | Redis                          | Session, cache, queue broker            |
| **Queue**              | Laravel Horizon                | Async jobs (email, notifikasi, laporan) |
| **WebSocket**          | Laravel Reverb                 | Real-time status updates                |
| **Storage**            | Local / S3-compatible          | Foto kendaraan, bukti pembayaran        |
| **Payment**            | Midtrans / Xendit              | QRIS, E-Wallet, Virtual Account         |
| **Push Notification**  | Firebase Cloud Messaging (FCM) | Push notification mobile                |
| **Email**              | Mailgun / SMTP                 | Email notifikasi & receipt              |
| **SMS**                | Twilio / Nexmo (opsional)      | OTP & reminder                          |
| **Containerization**   | Docker + Docker Compose        | Development environment                 |
| **CI/CD**              | GitHub Actions                 | Automated testing & deployment          |
| **Monitoring**         | Laravel Telescope + Sentry     | Error tracking & performance            |

### 3.2 Arsitektur Sistem

```
+-----------------------------------------------------------+
|                     CLIENT LAYER                           |
|                                                             |
|  +--------------+  +--------------+  +--------------+      |
|  |  Flutter App  |  |  React SPA   |  | FilamentPHP  |      |
|  |  (Customer)   |  |  (Kasir)     |  |  (Admin)     |      |
|  +------+-------+  +------+-------+  +------+-------+      |
|         |                 |                 |               |
+---------+-----------------+-----------------+---------------+
          |                 |                 |
          V                 V                 V
+-----------------------------------------------------------+
|                      API LAYER                              |
|                                                             |
|  +----------------------------------------------------+    |
|  |              Laravel 11 REST API                     |    |
|  |  +---------+  +----------+  +---------------+      |    |
|  |  | Auth    |  | Booking  |  | Payment       |      |    |
|  |  | Module  |  | Module   |  | Module        |      |    |
|  |  +---------+  +----------+  +---------------+      |    |
|  |  +---------+  +----------+  +---------------+      |    |
|  |  | Order   |  | Report   |  | Notification  |      |    |
|  |  | Module  |  | Module   |  | Module        |      |    |
|  |  +---------+  +----------+  +---------------+      |    |
|  +----------------------------------------------------+    |
|                                                             |
|  +---------------------+  +--------------------------+     |
|  |  Laravel Reverb      |  |  Laravel Horizon          |     |
|  |  (WebSocket)         |  |  (Queue Worker)           |     |
|  +---------------------+  +--------------------------+     |
+-----------------------------------------------------------+
          |                 |                 |
          V                 V                 V
+-----------------------------------------------------------+
|                      DATA LAYER                             |
|                                                             |
|  +----------+  +----------+  +------------------------+   |
|  |  MySQL   |  |  Redis   |  |  Storage (S3/Local)    |   |
|  |  8.0+    |  |  7.0+   |  |  Files & Media         |   |
|  +----------+  +----------+  +------------------------+   |
+-----------------------------------------------------------+
```

### 3.3 Struktur Repository

```
laravel_cuci_kendaraan/
+-- app/
|   +-- Filament/               # FilamentPHP admin resources
|   |   +-- Resources/
|   |   +-- Pages/
|   |   +-- Widgets/
|   +-- Http/
|   |   +-- Controllers/        # API Controllers
|   |   +-- Middleware/
|   |   +-- Requests/           # Form Request validation
|   +-- Models/                 # Eloquent Models
|   +-- Services/               # Business logic layer
|   |   +-- BookingService.php
|   |   +-- PaymentService.php
|   |   +-- SlotService.php
|   |   +-- ReportService.php
|   |   +-- NotificationService.php
|   +-- Jobs/                   # Queue jobs
|   +-- Events/                 # Broadcasting events
|   +-- Listeners/              # Event listeners
|   +-- Notifications/          # Notification classes
|   +-- Enums/                  # PHP 8.2 Enums
+-- database/
|   +-- migrations/
|   +-- seeders/
|   +-- factories/
+-- routes/
|   +-- api.php                 # API routes (Flutter)
|   +-- web.php                 # Web routes (Kasir + Admin)
|   +-- channels.php            # Broadcast channels
+-- resources/
|   +-- js/                     # Inertia.js React components
|       +-- Pages/
|       +-- Components/
|       +-- Layouts/
+-- config/
+-- docker/                     # Docker configurations
+-- .github/                    # CI/CD workflows
+-- mobile/                     # Flutter app (separate)
|   +-- cuci_kuy_app/
+-- tests/
|   +-- Unit/
|   +-- Feature/
+-- docs/                       # Documentation
```

---

## 4. Pengguna & Peran

### 4.1 Daftar Peran

| Role                       | Akses                 | Keterangan                                         |
| -------------------------- | --------------------- | -------------------------------------------------- |
| **Super Admin**      | Full akses            | Pemilik platform, bisa manage semua cabang         |
| **Admin Cabang**     | Akses cabang tertentu | Manager cabang, kelola operasional & laporan       |
| **Kasir**            | Transaksi & booking   | Melayani walk-in, scan QR, proses pembayaran tunai |
| **Operator/Pencuci** | Update status         | Melakukan pencucian, update status pengerjaan      |
| **Customer**         | Booking & bayar       | Pelanggan yang melakukan pemesanan                 |

### 4.2 Hak Akses Detail (RBAC)

#### Super Admin

- Kelola semua cabang (CRUD)
- Kelola semua user & role
- Pengaturan global harga & layanan
- Laporan keuangan semua cabang
- Dashboard konsolidasi
- Kelola karyawan lintas cabang

#### Admin Cabang

- Kelola cabang sendiri
- Kelola karyawan di cabangnya
- Kelola harga & layanan cabang
- Laporan keuangan cabang sendiri
- Tidak akses cabang lain

#### Kasir

- Buat booking walk-in
- Scan QR code booking
- Proses pembayaran tunai
- Lihat daftar booking hari ini
- Tidak ubah harga
- Tidak akses laporan keuangan

#### Operator/Pencuci

- Lihat antrian tugas
- Update status pengerjaan
- Upload foto sebelum/sesudah
- Tidak akses transaksi keuangan

#### Customer

- Booking online
- Pembayaran digital
- Lihat status pengerjaan
- Lihat riwayat transaksi
- Tidak akses data operator

---

## 5. Modul A — Aplikasi Customer (Flutter)

### 5.1 Flow Utama Customer

```
Buka App -> Pilih Cabang -> Pilih Layanan -> Pilih Slot
     -> Isi Detail -> Review Booking -> Bayar Online -> Selesai & Notif
```

### 5.2 Halaman & Fitur

#### 5.2.1 Onboarding & Auth

| Halaman                    | Fitur                  | Keterangan                   |
| -------------------------- | ---------------------- | ---------------------------- |
| **Splash Screen**    | Logo, loading animasi  | Brand CuciKuy                |
| **Onboarding**       | 3 slide intro          | Fitur utama aplikasi         |
| **Login**            | Email/phone + password | Social login (Google, Apple) |
| **Register**         | Form registrasi        | Name, email, phone, password |
| **OTP Verification** | Verifikasi nomor HP    | Via SMS (Twilio)             |
| **Lupa Password**    | Reset via email        | Link reset password          |
| **Profil**           | Edit profil            | Avatar, nama, email, telepon |

#### 5.2.2 Home / Beranda

- **Banner promosi** (carousel, bisa diatur admin).
- **Quick action buttons**: Booking Sekarang, Home Service, Riwayat.
- **Cabang terdekat** (berdasarkan GPS).
- **Layanan populer** (top 3).
- **Notifikasi badge** (unread count).

#### 5.2.3 Pilih Cabang

- **Daftar cabang** dengan info:
  - Nama cabang & alamat.
  - Jam operasional (open/close indicator).
  - Rating & jumlah review.
  - Jarak dari lokasi user.
  - Slot tersisa hari ini.
- **Pencarian** cabang (nama / area).
- **Filter**: Terdekat, Rating Tertinggi, Buka Sekarang.
- **Peta integrasi** (Google Maps / OpenStreetMap).

#### 5.2.4 Pilih Layanan

| Layanan                | Deskripsi                                      | Estimasi Durasi | Tipe      |
| ---------------------- | ---------------------------------------------- | --------------- | --------- |
| **Cuci Biasa**   | Cuci eksterior + interior ringan               | 30-45 menit     | Reguler   |
| **Wax**          | Cuci + poles wax                               | 60-90 menit     | Premium   |
| **Detailing**    | Cuci detail + interior deep clean + engine bay | 120-180 menit   | Premium+  |
| **Home Service** | Layanan di lokasi pelanggan                    | 45-90 menit     | On-demand |

Setiap layanan memiliki:

- **Deskripsi lengkap** + foto before_after.
- **Pilihan jenis kendaraan**: Sedan, SUV, MPV, Hatchback, Motor, Truk Ringan.
- **Harga dinamis** berdasarkan jenis kendaraan & cabang.
- **Estimasi durasi** berdasarkan jenis kendaraan.
- **Add-on opsional**: Parfum interior, Pembersih ban, Anti jamur kaca, dll.

#### 5.2.5 Pilih Slot Waktu

- **Kalender picker** (tidak bisa booking tanggal yang sudah lewat).
- **Daftar slot waktu** per cabang:
  - Ditampilkan sebagai grid/card (e.g., 08:00, 08:30, 09:00, ...).
  - Status slot: Tersedia (hijau), Hampir Penuh (kuning), Penuh (merah).
  - **Kuota per slot** ditentukan admin (misal: 3 mobil per slot 30 menit).
- **Cut-off time**: Booking minimal 2 jam sebelum waktu slot (konfigurable admin).
- **Home Service**: Pilihan rentang waktu lebih luas (2 jam block).

#### 5.2.6 Ringkasan Booking & Konfirmasi

- **Detail booking**:
  - Cabang, layanan, jenis kendaraan.
  - Tanggal & jam slot.
  - Harga layanan + add-on (jika ada).
  - Total harga.
- **Catatan khusus** (textarea, optional).
- **Upload foto kendaraan** (optional, max 3 foto).
- **Kode promo** (jika ada).
- **Metode pembayaran**: QRIS, E-Wallet, Transfer Bank, Tunai di Tempat.
- **Total harus dibayar** + breakdown harga.

#### 5.2.7 Pembayaran Online

- **Integrasi Midtrans/Xendit**:
  - QRIS (scan dari e-wallet apapun).
  - GoPay, OVO, DANA, ShopeePay.
  - Virtual Account (BCA, BRI, Mandiri, BNI).
  - Credit Card (optional, bisa diaktifkan admin).
- **Status pembayaran**: Pending -> Paid -> Refund (jika dibatalkan).
- **Expired time**: Pembayaran harus selesai dalam 30 menit (konfigurable).
- **Auto-cancel**: Jika tidak bayar, booking otomatis dibatalkan.

#### 5.2.8 Status Pengerjaan Real-Time

**Status Flow:**

```
Menunggu Pembayaran
       |
       v
Dibatalkan (timeout / manual)         Dibayar
                                          |
                                          v
                                  Dikonfirmasi (Admin/Kasir)
                                          |
                                          v
                                  Dalam Antrian
                                          |
                                          v
                                  Sedang Dicuci
                                          |
                                          v
                                  Pengeringan
                                          |
                                          v
                                  Selesai
                                          |
                                          v
                                  Dinilai
```

Setiap perubahan status mengirim **push notification** ke customer.

- **Detail status**: Timeline view dengan timestamp.
- **Foto progress**: Operator bisa upload foto saat pencucian dimulai & selesai.
- **Estimasi selesai**: Countdown timer berdasarkan progress.
- **Chat dengan operator** (optional, via WebSocket).

#### 5.2.9 Riwayat & Rating

- **Daftar riwayat booking**: Filter by status, tanggal, layanan.
- **Detail transaksi**: Invoice digital, bukti bayar, status.
- **Rating & Review**: Bintang 1-5 + komentar + foto (max 3).
- **Repeat booking**: Booking ulang layanan yang sama dengan 1 tap.
- **Download invoice** (PDF).

#### 5.2.10 Menu Lainnya

- **Notifikasi**: Daftar semua notifikasi (in-app).
- **Promo & Voucher**: Daftar promo aktif.
- **FAQ / Bantuan**: Pertanyaan umum & kontak support.
- **Tentang Aplikasi**: Versi, terms & conditions, privacy policy.
- **Pengaturan**: Bahasa, notifikasi on/off, dark mode.

### 5.3 Spesifikasi Teknis Flutter

- **Minimum SDK**: Flutter 3.22+ / Dart 3.4+.
- **State Management**: Riverpod (recommended) atau BLoC.
- **HTTP Client**: Dio with interceptors (token refresh, logging).
- **Local Storage**: Hive / SharedPreferences.
- **Navigation**: GoRouter (declarative routing).
- **Maps**: google_maps_flutter / flutter_map.
- **Camera**: image_picker.
- **Push Notification**: firebase_messaging.
- **Deep Linking**: flutter_deeplinkin / go_router.
- **Platform**: iOS 15+ & Android 10+ (API 29+).

---

## 6. Modul B — Aplikasi Kasir/Operator (Web/Mobile)

### 6.1 Platform

- **Web**: Aplikasi SPA yang diakses via browser (laptop/desktop).
- **Mobile**: Progressive Web App (PWA) yang bisa diakses via HP operator.
- **Framework**: Inertia.js + React.
- **UI Library**: Shadcn/ui (atau Ant Design / MUI).
- **Responsive**: Layout adaptif untuk desktop & mobile.

### 6.2 Flow Utama Kasir

```
Login -> Dashboard Hari Ini -> Scan / Cari -> Proses Transaksi
     -> Bayar Tunai -> Mulai Cuci -> Upload Foto -> Update Selesai
```

### 6.3 Halaman & Fitur

#### 6.3.1 Login Kasir

- Login via email + password.
- Pilihan cabang (jika kasir diizinkan di banyak cabang).
- Quick PIN login (opsional, untuk akses cepat).

#### 6.3.2 Dashboard Hari Ini

- **Ringkasan hari ini**:
  - Total booking hari ini (online + walk-in).
  - Total pendapatan hari ini.
  - Booking menunggu, sedang dikerjakan, selesai.
- **Antrian booking** (kanban board atau list):
  - Kolom: Antrian -> Sedang Dikerjakan -> Selesai.
  - Drag & drop untuk update status (desktop).
  - Color coding berdasarkan layanan & urgency.
- **Quick action**:
  - Tombol "Scan QR" untuk booking customer.
  - Tombol "Walk-in Booking" untuk pelanggan datang langsung.
- **Notifikasi** booking baru masuk (sound + visual).

#### 6.3.3 Scan QR Booking

- **QR Scanner** (gunakan kamera HP/laptop).
- Setelah scan, tampilkan:
  - Detail booking customer (nama, layanan, kendaraan, slot).
  - Status pembayaran (lunas/tunai di tempat).
- **Quick action setelah scan**:
  - Konfirmasi & mulai pengerjaan.
  - Catat pembayaran tunai (jika walk-in).

#### 6.3.4 Walk-in Booking (Tanpa Booking Online)

- Form booking manual:
  - Nama customer (atau "Walk-in Customer").
  - No. HP (optional untuk walk-in).
  - Pilih layanan.
  - Pilih jenis kendaraan.
  - Pilih warna kendaraan (untuk identifikasi).
  - Nomor polisi (optional).
  - Catatan khusus.
- **Pembayaran tunai** langsung di kasir.
- Generate QR code untuk tracking (jika customer mau pantau).

#### 6.3.5 Manajemen Status Pengerjaan

**Untuk Kasir (meja):**

| Action                       | Keterangan                                            |
| ---------------------------- | ----------------------------------------------------- |
| **Terima**             | Set booking baru dari online -> status "Dikonfirmasi" |
| **Arahkan ke Pencuci** | Assign operator ke booking tertentu                   |
| **Selesai**            | Tandai pengerjaan selesai (setelah verifikasi)        |

**Untuk Operator/Pencuci (PWA mobile):**

| Action                     | Keterangan                                         |
| -------------------------- | -------------------------------------------------- |
| **Mulai Cuci**       | Status -> "Sedang Dicuci", mulai timer             |
| **Foto Saat Proses** | Upload foto progress (wajib untuk quality control) |
| **Selesai Cuci**     | Status -> "Pengeringan" atau "Selesai"             |
| **Tandai Selesai**   | Upload foto final, status -> "Selesai"             |

- **Timer otomatis**: Mulai saat "Mulai Cuci" -> hitung durasi aktual vs estimasi.
- **Notifikasi ke customer** setiap perubahan status.
- **Operator dashboard**: Hanya lihat tugas yang di-assign ke dia.

#### 6.3.6 Detail Booking / Transaksi

- **Info customer**: Nama, no. HP, riwayat booking.
- **Info kendaraan**: Jenis, warna, plat nomor.
- **Info layanan**: Jenis layanan, add-on, harga.
- **Info pembayaran**: Metode, status, bukti bayar.
- **Timeline status**: History semua perubahan status.
- **Foto**: Sebelum, proses, sesudah.
- **Aksi**: Edit catatan, update status, cetak struk.

### 6.4 Spesifikasi Teknis Kasir Web

- **Framework**: Laravel 11 + Inertia.js + React.
- **UI**: Tailwind CSS + Shadcn/ui components.
- **QR Scanner**: html5-qrcode library.
- **Real-time**: Laravel Reverb (WebSocket) untuk status updates.
- **PWA**: Service Worker untuk offline capability.
- **Cetak**: Browser print API untuk struk thermal (80mm/58mm).

---

## 7. Modul C — Dashboard Admin (FilamentPHP)

### 7.1 Platform

- **Framework**: FilamentPHP 3+ (built on top of Laravel).
- **UI**: FilamentPHP default theme (customize sesuai brand).
- **Akses**: Hanya via web browser (desktop preferred).

### 7.2 Halaman & Fitur

#### 7.2.1 Dashboard Utama

**Widgets:**

| Widget              | Isi                               | Tipe           |
| ------------------- | --------------------------------- | -------------- |
| **Stat Card** | Total booking hari ini            | Number         |
| **Stat Card** | Total pendapatan hari ini         | Currency       |
| **Stat Card** | Booking aktif (sedang dikerjakan) | Number         |
| **Stat Card** | Rata-rata rating bulan ini        | Decimal        |
| **Chart**     | Pendapatan 7 hari terakhir        | Line Chart     |
| **Chart**     | Booking per layanan (pie)         | Doughnut Chart |
| **Chart**     | Booking per jam (hari ini)        | Bar Chart      |
| **Tabel**     | 5 booking terbaru                 | Table          |
| **Tabel**     | 5 customer teratas (by visit)     | Table          |
| **Alert**     | Booking menunggu konfirmasi       | Alert          |

#### 7.2.2 Manajemen Cabang

**Resource: Branch**

| Field             | Tipe    | Keterangan          |
| ----------------- | ------- | ------------------- |
| id                | UUID    | Primary key         |
| name              | String  | Nama cabang         |
| slug              | String  | URL-friendly name   |
| address           | Text    | Alamat lengkap      |
| city              | String  | Kota                |
| province          | String  | Provinsi            |
| postal_code       | String  | Kode pos            |
| phone             | String  | No. telepon         |
| email             | String  | Email cabang        |
| latitude          | Decimal | Koordinat GPS       |
| longitude         | Decimal | Koordinat GPS       |
| opening_hours     | JSON    | Jam buka per hari   |
| is_active         | Boolean | Status aktif        |
| cover_image       | File    | Foto cabang         |
| operational_notes | Text    | Catatan operasional |

**Fitur:**

- CRUD cabang.
- Set jam operasional per hari (differensial per hari).
- Aktif/nonaktifkan cabang.
- Set kuota slot per cabang.
- Lihat statistik per cabang.

#### 7.2.3 Manajemen Layanan & Harga

**Resource: Service**

| Field                 | Tipe    | Keterangan                          |
| --------------------- | ------- | ----------------------------------- |
| id                    | UUID    | Primary key                         |
| name                  | String  | Nama layanan                        |
| slug                  | String  | URL-friendly                        |
| description           | Text    | Deskripsi layanan                   |
| category              | Enum    | Basic, Premium, Premium+, On-demand |
| base_duration_minutes | Integer | Estimasi durasi dasar (menit)       |
| is_active             | Boolean | Status aktif                        |
| sort_order            | Integer | Urutan tampilan                     |
| image                 | File    | Foto layanan                        |

**Resource: ServicePricing**

| Field            | Tipe    | Keterangan                              |
| ---------------- | ------- | --------------------------------------- |
| id               | UUID    | Primary key                             |
| service_id       | FK      | Relasi ke service                       |
| branch_id        | FK      | Relasi ke branch                        |
| vehicle_type     | Enum    | Sedan, SUV, MPV, Hatchback, Motor, Truk |
| price            | Decimal | Harga layanan                           |
| duration_minutes | Integer | Durasi untuk jenis kendaraan ini        |
| is_active        | Boolean | Status aktif                            |

**Resource: AddOn**

| Field            | Tipe    | Keterangan      |
| ---------------- | ------- | --------------- |
| id               | UUID    | Primary key     |
| name             | String  | Nama add-on     |
| description      | Text    | Deskripsi       |
| price            | Decimal | Harga add-on    |
| duration_minutes | Integer | Tambahan durasi |
| is_active        | Boolean | Status aktif    |

**Fitur:**

- CRUD layanan dengan harga per jenis kendaraan & per cabang.
- Harga bisa berbeda per cabang.
- CRUD add-on (opsional tambahan).
- Atur durasi estimasi per kombinasi layanan & kendaraan.
- Aktif/nonaktifkan layanan.
- Atur urutan tampilan.

#### 7.2.4 Manajemen Slot Waktu

**Resource: SlotConfiguration**

| Field          | Tipe    | Keterangan                      |
| -------------- | ------- | ------------------------------- |
| id             | UUID    | Primary key                     |
| branch_id      | FK      | Relasi ke branch                |
| date           | Date    | Tanggal (atau repeated pattern) |
| slot_time      | Time    | Waktu slot (08:00, 08:30, ...)  |
| max_capacity   | Integer | Maks booking per slot           |
| current_booked | Integer | Sudah terbooking                |
| is_blocked     | Boolean | Diblokir (libur, maintenance)   |
| notes          | String  | Catatan                         |

**Fitur:**

- Atur slot per hari per cabang (template).
- Set recurring slot (misal: Senin-Jumat 08:00-17:00, Sabtu 08:00-15:00).
- Bulk block slot (misal: hari libur nasional).
- Override slot tertentu (misal: Ramadhan hours).
- Visual capacity indicator (chart).

#### 7.2.5 Manajemen Karyawan

**Resource: Employee**

| Field           | Tipe    | Keterangan                 |
| --------------- | ------- | -------------------------- |
| id              | UUID    | Primary key                |
| user_id         | FK      | Relasi ke user             |
| branch_id       | FK      | Relasi ke branch           |
| position        | Enum    | Kasir, Pencuci, Supervisor |
| phone           | String  | No. telepon                |
| hire_date       | Date    | Tanggal masuk              |
| salary_type     | Enum    | Bulanan, Per Cuci, Per Jam |
| base_salary     | Decimal | Gaji pokok                 |
| commission_rate | Decimal | Rate komisi (%)            |
| is_active       | Boolean | Status aktif               |
| avatar          | File    | Foto profil                |
| skills          | JSON    | [Wax, Detailing, dll]      |
| schedule        | JSON    | Jadwal kerja mingguan      |

**Resource: Shift**

| Field       | Tipe | Keterangan                           |
| ----------- | ---- | ------------------------------------ |
| id          | UUID | Primary key                          |
| employee_id | FK   | Relasi ke employee                   |
| date        | Date | Tanggal shift                        |
| start_time  | Time | Jam mulai                            |
| end_time    | Time | Jam selesai                          |
| status      | Enum | Scheduled, Active, Completed, Absent |

**Fitur:**

- CRUD karyawan dengan role.
- Assign karyawan ke cabang.
- Kelola jadwal shift per karyawan.
- Lihat workload karyawan per hari.
- Komisi otomatis berdasarkan jumlah pencucian.

#### 7.2.6 Manajemen Pelanggan

**Resource: Customer**

| Field            | Tipe      | Keterangan                     |
| ---------------- | --------- | ------------------------------ |
| id               | UUID      | Primary key                    |
| name             | String    | Nama lengkap                   |
| email            | String    | Email                          |
| phone            | String    | No. HP                         |
| total_visits     | Integer   | Total kunjungan                |
| total_spent      | Decimal   | Total pengeluaran              |
| loyalty_tier     | Enum      | Bronze, Silver, Gold, Platinum |
| preferred_branch | FK        | Cabang favorit                 |
| notes            | String    | Catatan khusus                 |
| created_at       | Timestamp | Tanggal daftar                 |

**Fitur:**

- Lihat daftar customer dengan pencarian & filter.
- Detail customer: Riwayat booking, total spending.
- Assign tier loyalty (otomatis berdasarkan spending).
- Catatan khusus per customer.
- Export data customer (CSV/Excel).
- Blokir customer (jika bermasalah).

#### 7.2.7 Manajemen Booking

**Resource: Booking**

| Field                | Tipe      | Keterangan                                |
| -------------------- | --------- | ----------------------------------------- |
| id                   | UUID      | Primary key                               |
| booking_code         | String    | Kode unik booking (e.g., CK-20260722-001) |
| customer_id          | FK        | Relasi ke customer                        |
| branch_id            | FK        | Relasi ke branch                          |
| service_id           | FK        | Relasi ke service                         |
| vehicle_type         | Enum      | Jenis kendaraan                           |
| vehicle_color        | String    | Warna kendaraan                           |
| vehicle_plate        | String    | Nomor polisi                              |
| booking_date         | Date      | Tanggal booking                           |
| slot_time            | Time      | Jam slot                                  |
| status               | Enum      | Status pengerjaan                         |
| payment_method       | Enum      | Metode bayar                              |
| payment_status       | Enum      | Status bayar                              |
| subtotal             | Decimal   | Subtotal                                  |
| discount             | Decimal   | Diskon                                    |
| tax                  | Decimal   | Pajak                                     |
| total                | Decimal   | Total bayar                               |
| notes                | Text      | Catatan                                   |
| assigned_employee_id | FK        | Pencuci yang ditugaskan                   |
| started_at           | Timestamp | Mulai dicuci                              |
| finished_at          | Timestamp | Selesai                                   |
| cancelled_at         | Timestamp | Dibatalkan                                |
| cancel_reason        | Text      | Alasan pembatalan                         |
| created_at           | Timestamp | Waktu booking dibuat                      |

**Fitur:**

- Daftar semua booking dengan filter: tanggal, status, cabang, layanan.
- Detail booking: Full info, timeline, foto, pembayaran.
- Update status manual (override operator).
- Cancel booking + catatan alasan.
- Reschedule booking.
- Assign ulang operator.
- Export laporan booking.

#### 7.2.8 Laporan Keuangan

**Halaman: Reports > Financial**

| Laporan                        | Detail                        | Output        |
| ------------------------------ | ----------------------------- | ------------- |
| **Pendapatan Harian**    | Total per cabang, per layanan | Tabel + Chart |
| **Pendapatan Mingguan**  | Ringkasan 7 hari              | Tabel + Chart |
| **Pendapatan Bulanan**   | Ringkasan 30 hari             | Tabel + Chart |
| **Laporan per Layanan**  | Breakdown per jenis layanan   | Tabel         |
| **Laporan per Cabang**   | Bandingkan cabang             | Tabel + Chart |
| **Laporan Metode Bayar** | QRIS vs E-Wallet vs Tunai     | Pie Chart     |
| **Laporan Refund**       | Daftar refund                 | Tabel         |
| **Cashflow**             | Pemasukan vs pengeluaran      | Chart         |
| **Export**               | PDF & Excel                   | Download      |

**Widget Dashboard Tambahan:**

- Revenue trend (7, 30, 90 hari).
- Top performing branch.
- Busiest time slots.
- Customer acquisition trend.
- Average order value.

#### 7.2.9 Laporan Statistik

| Statistik                      | Keterangan                           |
| ------------------------------ | ------------------------------------ |
| **Booking Rate**         | Jumlah booking per hari/minggu/bulan |
| **Utilization Rate**     | Slot terisi vs total slot tersedia   |
| **Average Duration**     | Durasi aktual vs estimasi            |
| **Customer Retention**   | Repeat customer rate                 |
| **Service Popularity**   | Ranking layanan paling dipesan       |
| **Peak Hours**           | Jam tersibuk                         |
| **Revenue per Employee** | Pendapatan per karyawan              |
| **NPS / Rating**         | Rata-rata rating customer            |

#### 7.2.10 Pengaturan Global

**Resource: Settings**

| Setting                 | Tipe    | Default | Keterangan                      |
| ----------------------- | ------- | ------- | ------------------------------- |
| app_name                | String  | CuciKuy | Nama aplikasi                   |
| currency                | String  | IDR     | Mata uang                       |
| tax_rate                | Decimal | 11.00   | Pajak PPn (%)                   |
| booking_cutoff_hours    | Integer | 2       | Minimum jam sebelum slot        |
| payment_expired_minutes | Integer | 30      | Waktu habis pembayaran          |
| auto_cancel_hours       | Integer | 2       | Auto-cancel jika tidak bayar    |
| max_photos_per_booking  | Integer | 3       | Maks foto per booking           |
| min_rating_for_promo    | Decimal | 4.0     | Rating minimum untuk promo      |
| enable_rating           | Boolean | true    | Aktifkan rating                 |
| enable_chat             | Boolean | false   | Aktifkan chat customer-operator |
| maintenance_mode        | Boolean | false   | Mode maintenance                |
| terms_url               | String  | -       | Link terms & conditions         |
| privacy_url             | String  | -       | Link privacy policy             |
| support_whatsapp        | String  | -       | No. WA support                  |
| support_email           | String  | -       | Email support                   |

**Payment Gateway Settings:**

- Midtrans: Server key, client key, merchant ID, environment (sandbox/production).
- Xendit: API key, environment.

**Notification Settings:**

- Email template configuration.
- Push notification settings.
- SMS settings.

---

## 8. Desain Database (ERD)

### 8.1 Daftar Tabel

#### users

| Field             | Tipe      | Keterangan                   |
| ----------------- | --------- | ---------------------------- |
| id                | UUID      | Primary key                  |
| name              | String    | Nama lengkap                 |
| email             | String    | Email (unique)               |
| phone             | String    | No. HP (unique, nullable)    |
| password          | String    | Hashed password              |
| avatar            | String    | Foto profil (nullable)       |
| email_verified_at | Timestamp | Verifikasi email (nullable)  |
| phone_verified_at | Timestamp | Verifikasi HP (nullable)     |
| is_active         | Boolean   | Status aktif (default: true) |
| last_login_at     | Timestamp | Login terakhir (nullable)    |
| remember_token    | String    | Remember token               |
| created_at        | Timestamp | Waktu dibuat                 |
| updated_at        | Timestamp | Waktu update                 |

#### user_roles

| Field      | Tipe           | Keterangan                                   |
| ---------- | -------------- | -------------------------------------------- |
| id         | UUID           | Primary key                                  |
| user_id    | FK -> users    | Relasi ke user                               |
| role       | Enum           | super_admin, admin_branch, cashier, operator |
| branch_id  | FK -> branches | Relasi ke cabang (nullable)                  |
| created_at | Timestamp      | Waktu dibuat                                 |

#### branches

| Field             | Tipe      | Keterangan                     |
| ----------------- | --------- | ------------------------------ |
| id                | UUID      | Primary key                    |
| name              | String    | Nama cabang                    |
| slug              | String    | URL-friendly (unique)          |
| address           | Text      | Alamat lengkap                 |
| city              | String    | Kota                           |
| province          | String    | Provinsi                       |
| postal_code       | String    | Kode pos                       |
| phone             | String    | No. telepon                    |
| email             | String    | Email cabang (nullable)        |
| latitude          | Decimal   | Koordinat GPS                  |
| longitude         | Decimal   | Koordinat GPS                  |
| opening_hours     | JSON      | Jam buka per hari              |
| is_active         | Boolean   | Status aktif (default: true)   |
| cover_image       | String    | Foto cabang (nullable)         |
| operational_notes | Text      | Catatan operasional (nullable) |
| created_at        | Timestamp | Waktu dibuat                   |
| updated_at        | Timestamp | Waktu update                   |

#### services

| Field                 | Tipe      | Keterangan                              |
| --------------------- | --------- | --------------------------------------- |
| id                    | UUID      | Primary key                             |
| name                  | String    | Nama layanan                            |
| slug                  | String    | URL-friendly (unique)                   |
| description           | Text      | Deskripsi layanan                       |
| category              | Enum      | basic, premium, premium_plus, on_demand |
| base_duration_minutes | Integer   | Estimasi durasi dasar                   |
| is_active             | Boolean   | Status aktif (default: true)            |
| sort_order            | Integer   | Urutan tampilan                         |
| image                 | String    | Foto layanan (nullable)                 |
| created_at            | Timestamp | Waktu dibuat                            |
| updated_at            | Timestamp | Waktu update                            |

#### service_pricings

| Field            | Tipe           | Keterangan                               |
| ---------------- | -------------- | ---------------------------------------- |
| id               | UUID           | Primary key                              |
| service_id       | FK -> services | Relasi ke layanan                        |
| branch_id        | FK -> branches | Relasi ke cabang                         |
| vehicle_type     | Enum           | sedan, suv, mpv, hatchback, motor, truck |
| price            | Decimal(12,2)  | Harga layanan                            |
| duration_minutes | Integer        | Durasi untuk jenis kendaraan             |
| is_active        | Boolean        | Status aktif (default: true)             |
| created_at       | Timestamp      | Waktu dibuat                             |
| updated_at       | Timestamp      | Waktu update                             |
| UNIQUE           | -              | (service_id, branch_id, vehicle_type)    |

#### add_ons

| Field            | Tipe          | Keterangan                   |
| ---------------- | ------------- | ---------------------------- |
| id               | UUID          | Primary key                  |
| name             | String        | Nama add-on                  |
| description      | Text          | Deskripsi (nullable)         |
| price            | Decimal(10,2) | Harga add-on                 |
| duration_minutes | Integer       | Tambahan durasi (default: 0) |
| is_active        | Boolean       | Status aktif (default: true) |
| created_at       | Timestamp     | Waktu dibuat                 |
| updated_at       | Timestamp     | Waktu update                 |

#### booking_add_ons (pivot)

| Field      | Tipe           | Keterangan         |
| ---------- | -------------- | ------------------ |
| id         | UUID           | Primary key        |
| booking_id | FK -> bookings | Relasi ke booking  |
| add_on_id  | FK -> add_ons  | Relasi ke add-on   |
| price      | Decimal(10,2)  | Harga saat booking |
| created_at | Timestamp      | Waktu dibuat       |

#### slot_configurations

| Field        | Tipe           | Keterangan                                                          |
| ------------ | -------------- | ------------------------------------------------------------------- |
| id           | UUID           | Primary key                                                         |
| branch_id    | FK -> branches | Relasi ke cabang                                                    |
| date         | Date           | Tanggal (nullable)                                                  |
| day_of_week  | Enum           | monday..sunday (nullable)                                           |
| slot_time    | Time           | Waktu slot                                                          |
| max_capacity | Integer        | Maks booking per slot                                               |
| is_blocked   | Boolean        | Status blokir (default: false)                                      |
| block_reason | String         | Alasan blokir (nullable)                                            |
| notes        | String         | Catatan (nullable)                                                  |
| created_at   | Timestamp      | Waktu dibuat                                                        |
| updated_at   | Timestamp      | Waktu update                                                        |
| UNIQUE       | -              | (branch_id, date, slot_time) or (branch_id, day_of_week, slot_time) |

#### employees

| Field           | Tipe           | Keterangan                   |
| --------------- | -------------- | ---------------------------- |
| id              | UUID           | Primary key                  |
| user_id         | FK -> users    | Relasi ke user               |
| branch_id       | FK -> branches | Relasi ke cabang             |
| position        | Enum           | cashier, washer, supervisor  |
| phone           | String         | No. telepon                  |
| hire_date       | Date           | Tanggal masuk                |
| salary_type     | Enum           | monthly, per_wash, hourly    |
| base_salary     | Decimal(12,2)  | Gaji pokok                   |
| commission_rate | Decimal(5,2)   | Rate komisi                  |
| is_active       | Boolean        | Status aktif (default: true) |
| avatar          | String         | Foto profil (nullable)       |
| skills          | JSON           | Skill pencucian              |
| created_at      | Timestamp      | Waktu dibuat                 |
| updated_at      | Timestamp      | Waktu update                 |

#### shifts

| Field       | Tipe            | Keterangan                           |
| ----------- | --------------- | ------------------------------------ |
| id          | UUID            | Primary key                          |
| employee_id | FK -> employees | Relasi ke karyawan                   |
| date        | Date            | Tanggal shift                        |
| start_time  | Time            | Jam mulai                            |
| end_time    | Time            | Jam selesai                          |
| status      | Enum            | scheduled, active, completed, absent |
| created_at  | Timestamp       | Waktu dibuat                         |
| updated_at  | Timestamp       | Waktu update                         |

#### customers (profile extended dari users)

| Field               | Tipe           | Keterangan                                       |
| ------------------- | -------------- | ------------------------------------------------ |
| id                  | UUID           | Primary key                                      |
| user_id             | FK -> users    | Relasi ke user                                   |
| total_visits        | Integer        | Total kunjungan (default: 0)                     |
| total_spent         | Decimal(14,2)  | Total pengeluaran (default: 0)                   |
| loyalty_tier        | Enum           | bronze, silver, gold, platinum (default: bronze) |
| preferred_branch_id | FK -> branches | Cabang favorit (nullable)                        |
| notes               | Text           | Catatan khusus (nullable)                        |
| created_at          | Timestamp      | Waktu dibuat                                     |
| updated_at          | Timestamp      | Waktu update                                     |

#### bookings

| Field                | Tipe            | Keterangan                                                                               |
| -------------------- | --------------- | ---------------------------------------------------------------------------------------- |
| id                   | UUID            | Primary key                                                                              |
| booking_code         | String          | Kode unik (unique)                                                                       |
| customer_id          | FK -> customers | Relasi ke customer                                                                       |
| branch_id            | FK -> branches  | Relasi ke cabang                                                                         |
| service_id           | FK -> services  | Relasi ke layanan                                                                        |
| assigned_employee_id | FK -> employees | Pencuci (nullable)                                                                       |
| vehicle_type         | Enum            | Jenis kendaraan                                                                          |
| vehicle_color        | String          | Warna kendaraan (nullable)                                                               |
| vehicle_plate        | String          | Nomor polisi (nullable)                                                                  |
| vehicle_photos       | JSON            | Foto kendaraan (nullable)                                                                |
| booking_date         | Date            | Tanggal booking                                                                          |
| slot_time            | Time            | Jam slot                                                                                 |
| status               | Enum            | pending_payment, confirmed, in_queue, washing, drying, completed, cancelled, no_show     |
| payment_method       | Enum            | qris, gopay, ovo, dana, shopeepay, va_bca, va_bri, va_mandiri, va_bni, credit_card, cash |
| payment_status       | Enum            | unpaid, paid, refund_pending, refunded                                                   |
| payment_reference    | String          | Referensi pembayaran (nullable)                                                          |
| subtotal             | Decimal(12,2)   | Subtotal                                                                                 |
| discount             | Decimal(12,2)   | Diskon (default: 0)                                                                      |
| tax                  | Decimal(12,2)   | Pajak (default: 0)                                                                       |
| total                | Decimal(12,2)   | Total bayar                                                                              |
| notes                | Text            | Catatan (nullable)                                                                       |
| is_walk_in           | Boolean         | Walk-in booking (default: false)                                                         |
| rating               | Tinyint         | Rating 1-5 (nullable)                                                                    |
| review               | Text            | Review (nullable)                                                                        |
| review_photos        | JSON            | Foto review (nullable)                                                                   |
| reviewed_at          | Timestamp       | Waktu review (nullable)                                                                  |
| started_at           | Timestamp       | Mulai dicuci (nullable)                                                                  |
| finished_at          | Timestamp       | Selesai (nullable)                                                                       |
| cancelled_at         | Timestamp       | Dibatalkan (nullable)                                                                    |
| cancel_reason        | String          | Alasan pembatalan (nullable)                                                             |
| cancelled_by         | FK -> users     | Dibatalkan oleh (nullable)                                                               |
| created_at           | Timestamp       | Waktu dibuat                                                                             |
| updated_at           | Timestamp       | Waktu update                                                                             |

#### booking_status_logs

| Field       | Tipe           | Keterangan                   |
| ----------- | -------------- | ---------------------------- |
| id          | UUID           | Primary key                  |
| booking_id  | FK -> bookings | Relasi ke booking            |
| from_status | String         | Status sebelumnya (nullable) |
| to_status   | String         | Status baru                  |
| changed_by  | FK -> users    | Diubah oleh (nullable)       |
| notes       | String         | Catatan (nullable)           |
| photos      | JSON           | Foto (nullable)              |
| created_at  | Timestamp      | Waktu dibuat                 |

#### payments

| Field              | Tipe           | Keterangan                         |
| ------------------ | -------------- | ---------------------------------- |
| id                 | UUID           | Primary key                        |
| booking_id         | FK -> bookings | Relasi ke booking                  |
| gateway            | Enum           | midtrans, xendit, manual           |
| gateway_payment_id | String         | ID pembayaran gateway (nullable)   |
| gateway_order_id   | String         | ID order gateway (nullable)        |
| method             | String         | Metode pembayaran                  |
| amount             | Decimal(12,2)  | Jumlah pembayaran                  |
| status             | Enum           | pending, success, failed, refunded |
| paid_at            | Timestamp      | Waktu bayar (nullable)             |
| expired_at         | Timestamp      | Waktu expired (nullable)           |
| raw_response       | JSON           | Response mentah (nullable)         |
| created_at         | Timestamp      | Waktu dibuat                       |
| updated_at         | Timestamp      | Waktu update                       |

#### promos

| Field               | Tipe          | Keterangan                   |
| ------------------- | ------------- | ---------------------------- |
| id                  | UUID          | Primary key                  |
| code                | String        | Kode promo (unique)          |
| name                | String        | Nama promo                   |
| description         | Text          | Deskripsi (nullable)         |
| discount_type       | Enum          | percentage, fixed            |
| discount_value      | Decimal(10,2) | Nilai diskon                 |
| min_order           | Decimal(10,2) | Minimum order (default: 0)   |
| max_discount        | Decimal(10,2) | Maks diskon (nullable)       |
| start_date          | Date          | Tanggal mulai                |
| end_date            | Date          | Tanggal berakhir             |
| usage_limit         | Integer       | Batas penggunaan (nullable)  |
| used_count          | Integer       | Sudah digunakan (default: 0) |
| is_active           | Boolean       | Status aktif (default: true) |
| applicable_services | JSON          | Layanan berlaku (nullable)   |
| applicable_branches | JSON          | Cabang berlaku (nullable)    |
| created_at          | Timestamp     | Waktu dibuat                 |
| updated_at          | Timestamp     | Waktu update                 |

#### promo_usages

| Field           | Tipe            | Keterangan         |
| --------------- | --------------- | ------------------ |
| id              | UUID            | Primary key        |
| promo_id        | FK -> promos    | Relasi ke promo    |
| booking_id      | FK -> bookings  | Relasi ke booking  |
| customer_id     | FK -> customers | Relasi ke customer |
| discount_amount | Decimal(10,2)   | Jumlah diskon      |
| created_at      | Timestamp       | Waktu dibuat       |

#### notifications_log

| Field      | Tipe        | Keterangan               |
| ---------- | ----------- | ------------------------ |
| id         | UUID        | Primary key              |
| user_id    | FK -> users | Relasi ke user           |
| type       | Enum        | email, push, sms, in_app |
| title      | String      | Judul notifikasi         |
| body       | Text        | Isi notifikasi           |
| data       | JSON        | Data tambahan (nullable) |
| sent_at    | Timestamp   | Waktu kirim (nullable)   |
| read_at    | Timestamp   | Waktu baca (nullable)    |
| created_at | Timestamp   | Waktu dibuat             |

#### activity_logs

| Field        | Tipe        | Keterangan                |
| ------------ | ----------- | ------------------------- |
| id           | UUID        | Primary key               |
| user_id      | FK -> users | Relasi ke user (nullable) |
| action       | String      | Aksi yang dilakukan       |
| subject_type | String      | Tipe subjek               |
| subject_id   | UUID        | ID subjek                 |
| old_values   | JSON        | Nilai lama (nullable)     |
| new_values   | JSON        | Nilai baru (nullable)     |
| ip_address   | String      | Alamat IP                 |
| user_agent   | String      | User agent                |
| created_at   | Timestamp   | Waktu dibuat              |

#### settings

| Field      | Tipe      | Keterangan                                        |
| ---------- | --------- | ------------------------------------------------- |
| id         | UUID      | Primary key                                       |
| group      | String    | Kelompok setting (general, payment, notification) |
| key        | String    | Nama setting (unique)                             |
| value      | Text      | Nilai setting (nullable)                          |
| type       | Enum      | string, integer, decimal, boolean, json           |
| created_at | Timestamp | Waktu dibuat                                      |
| updated_at | Timestamp | Waktu update                                      |

### 8.2 Relasi Utama

```
users ──────── 1:1 ──────── customers
users ──────── 1:N ──────── user_roles
users ──────── 1:N ──────── employees
branches ───── 1:N ──────── user_roles
branches ───── 1:N ──────── service_pricings
branches ───── 1:N ──────── slot_configurations
branches ───── 1:N ──────── employees
branches ───── 1:N ──────── bookings
services ───── 1:N ──────── service_pricings
services ───── 1:N ──────── bookings
customers ──── 1:N ──────── bookings
bookings ───── 1:N ──────── booking_add_ons
bookings ───── 1:N ──────── booking_status_logs
bookings ───── 1:1 ──────── payments
employees ──── 1:N ──────── shifts
employees ──── 1:N ──────── bookings (assigned)
promos ─────── 1:N ──────── promo_usages
```

### 8.3 Index & Performance

```sql
-- Performance-critical indexes
CREATE INDEX idx_bookings_date_branch ON bookings(booking_date, branch_id);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_bookings_customer ON bookings(customer_id);
CREATE INDEX idx_bookings_code ON bookings(booking_code);
CREATE INDEX idx_bookings_slot ON bookings(booking_date, slot_time, branch_id);

CREATE INDEX idx_slot_config_branch_date ON slot_configurations(branch_id, date);
CREATE INDEX idx_service_pricing_branch ON service_pricings(branch_id, service_id);

CREATE INDEX idx_payments_booking ON payments(booking_id);
CREATE INDEX idx_payments_gateway ON payments(gateway_payment_id);
CREATE INDEX idx_payments_status ON payments(status);

CREATE INDEX idx_notifications_user ON notifications_log(user_id, read_at);
CREATE INDEX idx_activity_logs_subject ON activity_logs(subject_type, subject_id);
```

---

## 9. API Design & Integrasi

### 9.1 API Authentication

- **Customer (Flutter)**: Laravel Sanctum (token-based).
- **Kasir/Operator (Inertia)**: Session-based (built-in Laravel auth).
- **Admin (Filament)**: Session-based (built-in Filament auth).
- **API Rate Limiting**: 60 requests/minute per token.

### 9.2 API Endpoints

#### Auth

```
POST   /api/v1/auth/register          # Register customer
POST   /api/v1/auth/login             # Login customer
POST   /api/v1/auth/logout            # Logout
POST   /api/v1/auth/forgot-password   # Request reset password
POST   /api/v1/auth/reset-password    # Reset password
POST   /api/v1/auth/verify-otp        # Verifikasi OTP
POST   /api/v1/auth/social-login      # Login via Google/Apple
GET    /api/v1/auth/me                # Profil user saat ini
PUT    /api/v1/auth/me                # Update profil
```

#### Branches

```
GET    /api/v1/branches               # Daftar cabang (with location filter)
GET    /api/v1/branches/{id}          # Detail cabang
GET    /api/v1/branches/{id}/services # Layanan di cabang ini
GET    /api/v1/branches/{id}/slots    # Slot tersedia (filter by date)
```

#### Services

```
GET    /api/v1/services               # Daftar semua layanan aktif
GET    /api/v1/services/{id}          # Detail layanan + pricing per kendaraan
```

#### Bookings

```
POST   /api/v1/bookings               # Buat booking baru
GET    /api/v1/bookings               # Daftar booking customer
GET    /api/v1/bookings/{id}          # Detail booking
PUT    /api/v1/bookings/{id}/cancel   # Batalkan booking
GET    /api/v1/bookings/{code}/track  # Tracking status (by booking code)
POST   /api/v1/bookings/{id}/review   # Beri rating & review
```

#### Payments

```
POST   /api/v1/payments               # Initiate payment (create Midtrans/Xendit transaction)
GET    /api/v1/payments/{id}/status   # Cek status pembayaran
POST   /api/v1/payments/callback      # Webhook dari payment gateway
POST   /api/v1/payments/{id}/retry    # Retry pembayaran
```

#### Promos

```
POST   /api/v1/promos/apply           # Apply kode promo
GET    /api/v1/promos/available       # Daftar promo tersedia
```

#### Notifications

```
GET    /api/v1/notifications           # Daftar notifikasi
PUT    /api/v1/notifications/{id}/read # Tandai sudah dibaca
PUT    /api/v1/notifications/read-all  # Tandai semua sudah dibaca
POST   /api/v1/notifications/fcm-token # Update FCM token
```

#### Reviews

```
GET    /api/v1/branches/{id}/reviews  # Reviews cabang
```

### 9.3 Kasir/Operator Routes (Web)

```
GET    /kasir                          # Dashboard kasir
GET    /kasir/queue                    # Antrian booking
GET    /kasir/scan                     # Halaman scan QR
GET    /kasir/booking/{code}           # Detail booking
POST   /kasir/walk-in                  # Buat walk-in booking
PUT    /kasir/booking/{id}/status      # Update status
POST   /kasir/booking/{id}/assign      # Assign operator
POST   /kasir/booking/{id}/payment     # Catat pembayaran tunai

GET    /operator/dashboard             # Dashboard operator
GET    /operator/tasks                 # Tugas hari ini
PUT    /operator/task/{id}/start       # Mulai pengerjaan
PUT    /operator/task/{id}/progress    # Update progress + foto
PUT    /operator/task/{id}/complete    # Selesaikan pengerjaan
```

### 9.4 Admin Routes (FilamentPHP)

```
/admin/dashboard                       # Admin dashboard
/admin/branches                        # Manajemen cabang
/admin/services                        # Manajemen layanan
/admin/service-pricings                # Manajemen harga
/admin/add-ons                         # Manajemen add-on
/admin/slots                           # Manajemen slot
/admin/bookings                        # Manajemen booking
/admin/customers                       # Manajemen pelanggan
/admin/employees                       # Manajemen karyawan
/admin/shifts                          # Manajemen shift
/admin/promos                          # Manajemen promo
/admin/reports/financial               # Laporan keuangan
/admin/reports/statistics              # Laporan statistik
/admin/settings                        # Pengaturan
/admin/payments                        # Log pembayaran
/admin/notifications                   # Log notifikasi
```

### 9.5 WebSocket Channels

```
booking.{booking_id}       # Status update per booking
branch.{branch_id}.queue   # Antrian booking per cabang (kasir)
branch.{branch_id}.stats   # Stats real-time per cabang (admin)
```

---

## 10. Integrasi Payment Gateway

### 10.1 Midtrans Integration

**Flow Pembayaran:**

```
Customer                    Laravel API                Midtrans
   |                            |                         |
   |--- Pilih metode bayar --->|                         |
   |                            |-- Create Transaction -->|
   |                            |<-- snap_token ----------|
   |<-- Snap Token -------------|                         |
   |                            |                         |
   |--- Bayar via Midtrans -------------------------------->|
   |<-- Status --------------------------------------------|
   |                            |                         |
   |                            |<-- Webhook (POST) ------|
   |                            |-- Verify & Update DB -->|
   |<-- Push Notif (Success) ---|                         |
```

**Metode yang Didukung:**

| Metode      | Kode Midtrans | Keterangan                |
| ----------- | ------------- | ------------------------- |
| QRIS        | qris          | Scan dari e-wallet apapun |
| GoPay       | gopay         | GoPay customer            |
| OVO         | ovo           | OVO customer              |
| ShopeePay   | shopeepay     | ShopeePay customer        |
| VA BCA      | bca_va        | Virtual Account BCA       |
| VA BRI      | bri_va        | Virtual Account BRI       |
| VA Mandiri  | mandiri_va    | Virtual Account Mandiri   |
| VA BNI      | bni_va        | Virtual Account BNI       |
| VA Permata  | permata_va    | Virtual Account Permata   |
| Credit Card | credit_card   | Kartu kredit/debit        |

**Payment Service Implementation:**

```php
// app/Services/PaymentService.php
class PaymentService
{
    public function createTransaction(Booking $booking): array
    {
        $payload = [
            'transaction_details' => [
                'order_id' => $booking->booking_code,
                'gross_amount' => $booking->total,
            ],
            'customer_details' => [
                'first_name' => $booking->customer->user->name,
                'email' => $booking->customer->user->email,
                'phone' => $booking->customer->user->phone,
            ],
            'callbacks' => [
                'finish' => config('app.url') . '/payment/finish',
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s T'),
                'unit' => 'minute',
                'duration' => config('payment.expired_minutes', 30),
            ],
        ];

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'gateway' => 'midtrans',
            'method' => $booking->payment_method,
            'amount' => $booking->total,
            'status' => 'pending',
            'expired_at' => now()->addMinutes(config('payment.expired_minutes', 30)),
        ]);

        $transaction = Midtrans::createTransaction($payload);

        $payment->update([
            'gateway_order_id' => $transaction->order_id,
            'gateway_payment_id' => $transaction->payment_id ?? null,
        ]);

        return [
            'snap_token' => $transaction->snap_token,
            'redirect_url' => $transaction->redirect_url,
        ];
    }

    public function handleWebhook(array $payload): void
    {
        $order_id = $payload['order_id'];
        $transaction_status = $payload['transaction_status'];

        $booking = Booking::where('booking_code', $order_id)->firstOrFail();
        $payment = $booking->payment;

        $statusMap = [
            'capture' => 'success',
            'settlement' => 'success',
            'pending' => 'pending',
            'deny' => 'failed',
            'expire' => 'failed',
            'cancel' => 'failed',
        ];

        $payment->update([
            'status' => $statusMap[$transaction_status] ?? 'pending',
            'raw_response' => $payload,
            'paid_at' => in_array($transaction_status, ['capture', 'settlement'])
                ? now() : null,
        ]);

        if (in_array($transaction_status, ['capture', 'settlement'])) {
            $booking->update(['payment_status' => 'paid']);
            event(new BookingConfirmed($booking));
        }
    }
}
```

### 10.2 Xendit Alternative

- Xendit sebagai fallback atau pilihan alternatif.
- Mendukung QRIS, VA, E-Wallet.
- Webhook endpoint berbeda, tapi struktur callback Mirip.
- Service layer abstraksi untuk menangani kedua gateway.

### 10.3 Pembayaran Tunai (Walk-in)

- Kasir memproses pembayaran tunai secara manual.
- Status pembayaran diupdate langsung oleh kasir.
- Bukti pembayaran = struk cetak dari aplikasi kasir.

---

## 11. Notifikasi & Real-Time

### 11.1 Tipe Notifikasi

| Event                 | Channel               | Penerima    | Keterangan                   |
| --------------------- | --------------------- | ----------- | ---------------------------- |
| Booking baru          | Push + In-App         | Admin/Kasir | Booking dari customer online |
| Pembayaran berhasil   | Push + Email          | Customer    | Konfirmasi pembayaran        |
| Booking dikonfirmasi  | Push + In-App         | Customer    | Kasir/Admin konfirm          |
| Status: Sedang Dicuci | Push + In-App         | Customer    | Operator update status       |
| Status: Selesai       | Push + Email + In-App | Customer    | Pengerjaan selesai           |
| Booking dibatalkan    | Push + Email + In-App | Customer    | Pembatalan                   |
| Reminder H-1          | Push + Email          | Customer    | Pengingat booking besok      |
| Review reminder       | Push + In-App         | Customer    | Minta rating setelah selesai |
| Pembayaran expired    | Push + In-App         | Customer    | Pembayaran tidak selesai     |
| Slot hampir penuh     | In-App                | Customer    | Slot tersisa sedikit         |

### 11.2 Implementation

```php
// Notification via FCM
class BookingStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Booking $booking,
        private string $newStatus
    ) {}

    public function via($notifiable): array
    {
        return ['fcm', 'database', 'mail'];
    }

    public function toArray($notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'status' => $this->newStatus,
            'message' => $this->getStatusMessage(),
        ];
    }

    public function toFcm($notifiable): array
    {
        return [
            'notification' => [
                'title' => 'Status Booking Diperbarui',
                'body' => $this->getStatusMessage(),
            ],
            'data' => [
                'booking_code' => $this->booking->booking_code,
                'status' => $this->newStatus,
                'screen' => 'booking_detail',
            ],
        ];
    }
}
```

### 11.3 WebSocket (Laravel Reverb)

```javascript
// Kasir listens to queue updates
Echo.channel(`branch.${branchId}.queue`)
    .listen('.booking.new', (e) => {
        // Update queue in real-time
        showNotification('Booking baru diterima!');
        refreshQueue();
    });

// Customer listens to own booking
Echo.private(`booking.${bookingId}`)
    .listen('.status.updated', (e) => {
        updateStatusTimeline(e.status);
        showNotification(e.message);
    });
```

---

## 12. Keamanan & Autentikasi

### 12.1 Autentikasi

| Komponen               | Metode                  | Keterangan                 |
| ---------------------- | ----------------------- | -------------------------- |
| **Customer App** | Laravel Sanctum (Token) | Bearer token, auto-refresh |
| **Kasir Web**    | Session + CSRF          | Cookie-based, Inertia.js   |
| **Admin Web**    | Session + Filament Auth | FilamentPHP built-in       |
| **API**          | Rate limiting           | 60 req/min/token           |

### 12.2 Otorisasi

- **RBAC** (Role-Based Access Control) dengan 5 peran.
- **Middleware per role**: `role:admin_branch`, `role:cashier`, `role:operator`.
- **Branch scoping**: Admin cabang hanya bisa akses data cabangnya.
- **API policies**: Policy classes untuk authorization logic.

### 12.3 Keamanan Data

- **Password hashing**: bcrypt (default Laravel).
- **JWT/Sanctum tokens**: Expiry + refresh mechanism.
- **Input validation**: Form Request classes untuk semua input.
- **SQL injection**: Eloquent ORM (parameterized queries).
- **XSS**: Blade/React auto-escaping.
- **CSRF**: Token verification untuk semua web routes.
- **Rate limiting**: Throttle middleware di API routes.
- **Encryption**: Sensitive data (payment tokens) encrypted at rest.
- **Audit trail**: ActivityLog model untuk semua perubahan data.

### 12.4 Payment Security

- **Webhook verification**: Signature verification dari Midtrans/Xendit.
- **No card storage**: Tidak menyimpan data kartu kredit.
- **Idempotency**: Order ID unik untuk setiap transaksi.
- **Expiration handling**: Auto-cancel untuk pembayaran expired.

---

## 13. Non-Fungsiinal

### 13.1 Performance

| Metrik                       | Target        | Keterangan                 |
| ---------------------------- | ------------- | -------------------------- |
| **API Response Time**  | < 200ms (p95) | Untuk semua endpoint utama |
| **Page Load Time**     | < 2 detik     | Dashboard admin            |
| **Flutter App Launch** | < 3 detik     | Cold start                 |
| **WebSocket Latency**  | < 500ms       | Status update              |
| **Database Query**     | < 50ms (p95)  | Single query               |
| **Concurrent Users**   | 500+          | Per cabang                 |
| **Uptime**             | 99.9%         | Monthly                    |

### 13.2 Scalability

- **Horizontal scaling**: Stateless API servers (behind load balancer).
- **Database**: Read replicas untuk laporan, write primary.
- **Queue workers**: Scale horizontally via Horizon.
- **Caching**: Redis untuk slot availability, pricing, branch data.
- **CDN**: Untuk aset statis (images, CSS, JS).
- **Search**: Laravel Scout + Meilisearch untuk pencarian (v2).

### 13.3 Reliability

- **Database backups**: Daily automated + point-in-time recovery.
- **Queue retry**: 3 retries dengan exponential backoff.
- **Payment reconciliation**: Cron job untuk reconciliasi status pembayaran.
- **Health checks**: `/health` endpoint untuk monitoring.
- **Graceful degradation**: Cash-only mode jika gateway down.

### 13.4 Monitoring & Observability

- **Laravel Telescope**: Development debugging.
- **Sentry**: Error tracking & alerting.
- **Log channels**: Stack (daily), Slack (critical), database (audit).
- **Metrics**: Response time, error rate, queue depth, DB connections.

### 13.5 Compliance

- **PDP (Pelindungan Data Pribadi)**: Sesuai UU PDP Indonesia.
- **Terms & Conditions**: Halaman T&C yang bisa diedit admin.
- **Privacy Policy**: Halaman privasi yang bisa diedit admin.
- **Data retention**: Data transaksi disimpan minimal 5 tahun.

---

## 14. Milestone & Timeline

### Phase 1: Foundation (Minggu 1-3)

| Task                        | Durasi | Keterangan               |
| --------------------------- | ------ | ------------------------ |
| Setup Laravel project       | 2 hari | Docker, env, CI/CD       |
| Database migration & seeder | 3 hari | Semua tabel + data dummy |
| Authentication system       | 3 hari | Sanctum + RBAC           |
| FilamentPHP admin panel     | 5 hari | Setup + semua resource   |
| Basic API endpoints         | 5 hari | Auth + Branch + Service  |

### Phase 2: Booking System (Minggu 4-6)

| Task                           | Durasi | Keterangan                    |
| ------------------------------ | ------ | ----------------------------- |
| Slot management                | 3 hari | Config + availability logic   |
| Booking flow (API)             | 5 hari | Create, list, cancel          |
| Payment integration (Midtrans) | 5 hari | Midtrans sandbox              |
| Payment webhook handling       | 2 hari | Callback + verification       |
| Kasir web app (Inertia.js)     | 5 hari | Dashboard + walk-in + scan QR |

### Phase 3: Real-Time & Notifications (Minggu 7-8)

| Task                     | Durasi | Keterangan                    |
| ------------------------ | ------ | ----------------------------- |
| Laravel Reverb setup     | 2 hari | WebSocket server              |
| Real-time status updates | 3 hari | Booking status broadcasting   |
| Push notification (FCM)  | 3 hari | Firebase setup + notification |
| Email notifications      | 2 hari | Transactional email           |

### Phase 4: Mobile App (Minggu 9-13)

| Task                           | Durasi | Keterangan                  |
| ------------------------------ | ------ | --------------------------- |
| Flutter project setup          | 1 hari | Structure + dependency      |
| Auth screens (login, register) | 3 hari | Complete auth flow          |
| Home & branch selection        | 3 hari | GPS, search, filter         |
| Booking flow screens           | 5 hari | Select service, slot, pay   |
| Payment integration            | 3 hari | Midtrans SDK integration    |
| Status tracking screen         | 3 hari | Real-time status + timeline |
| History & profile              | 2 hari | History, rating, profile    |
| Push notification setup        | 2 hari | FCM integration             |
| Testing & polish               | 3 hari | UI/UX refinement            |

### Phase 5: Reporting & Polish (Minggu 14-16)

| Task                     | Durasi | Keterangan                  |
| ------------------------ | ------ | --------------------------- |
| Financial reports        | 5 hari | Charts, tables, export      |
| Statistics dashboard     | 3 hari | Widget + analytics          |
| Performance optimization | 3 hari | Caching, query optimization |
| Security audit           | 2 hari | Penetration testing         |
| UAT & bug fixing         | 4 hari | User acceptance testing     |
| Deployment               | 2 hari | Production setup            |

**Total estimasi: ~16 minggu (4 bulan)** untuk tim 2-3 developer.

---

## 15. Risiko & Mitigasi

| #  | Risiko                            | Probabilitas | Dampak | Mitigasi                                         |
| -- | --------------------------------- | ------------ | ------ | ------------------------------------------------ |
| 1  | Payment gateway downtime          | Rendah       | Tinggi | Multi-gateway (Midtrans + Xendit), fallback cash |
| 2  | Slot double-booking               | Rendah       | Tinggi | Database-level locking, optimistic concurrency   |
| 3  | Customer no-show tinggi           | Sedang       | Sedang | Deposit minimum, cancel fee policy               |
| 4  | Performa app menurun di jam sibuk | Sedang       | Sedang | Caching agresif, queue-based processing          |
| 5  | Karyawan tidak update status      | Sedang       | Sedang | UI simplifikasi, reminder notifikasi             |
| 6  | Kebocoran data customer           | Rendah       | Tinggi | Encryption, RBAC, audit trail, compliance        |
| 7  | Scope creep                       | Tinggi       | Sedang | Strict MVP definition, phased delivery           |
| 8  | Perubahan kebijakan pembayaran    | Rendah       | Sedang | Abstraksi payment layer, mudah ganti gateway     |
| 9  | Bug di Flutter (cross-platform)   | Sedang       | Sedang | Unit test, integration test, CI/CD               |
| 10 | Integrasi maps bermasalah         | Rendah       | Rendah | Fallback ke list view tanpa peta                 |

---

## 16. Glossary

| Istilah                    | Keterangan                                               |
| -------------------------- | -------------------------------------------------------- |
| **Booking**          | Pemesanan jasa cuci kendaraan oleh customer              |
| **Walk-in**          | Customer yang datang langsung tanpa booking online       |
| **Slot**             | Waktu tersedia untuk mulai pencucian (e.g., 08:00-08:30) |
| **Operator/Pencuci** | Karyawan yang melakukan pencucian kendaraan              |
| **Home Service**     | Layanan pencucian di lokasi pelanggan                    |
| **QRIS**             | Quick Response Indonesia Standard (metode bayar QR)      |
| **VA**               | Virtual Account (metode bayar transfer bank)             |
| **Snap Token**       | Token Midtrans untuk pembayaran                          |
| **FCM**              | Firebase Cloud Messaging (push notification)             |
| **PWA**              | Progressive Web App                                      |
| **Inertia.js**       | Bridge antara Laravel backend & React/Vue frontend       |
| **FilamentPHP**      | Admin panel builder untuk Laravel                        |
| **RBAC**             | Role-Based Access Control                                |
| **NPS**              | Net Promoter Score                                       |
| **UAT**              | User Acceptance Testing                                  |

---

> **Catatan:** Dokumen ini bersifat living document. Setiap perubahan requirements harus diupdate di sini dan di-tandai dengan versi baru.
>
> **Author:** Product Team
> **Last Updated:** 22 Juli 2026
