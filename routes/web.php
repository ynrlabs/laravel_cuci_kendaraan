<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return view('portal');
});

Route::middleware(['auth', 'role:cashier,kasir,operator,admin_branch,admin_cabang,super_admin', 'branch.scope'])->group(function () {
    Route::prefix('kasir')->name('kasir.')->group(function () {


        Route::get('/dashboard', function () {
            return Inertia::render('Kasir/Dashboard', [
                'pageTitle' => 'Dashboard',
                'pageDescription' => 'Ringkasan aktivitas cuci kendaraan hari ini',
            ]);
        })->name('dashboard');

        Route::get('/antrian', function () {
            return Inertia::render('Kasir/Antrian/Kanban', [
                'pageTitle' => 'Antrian Kanban',
                'pageDescription' => 'Kelola antrian cuci kendaraan mode Kanban',
            ]);
        })->name('antrian.kanban');

        Route::get('/bookings', function () {
            return Inertia::render('Kasir/Bookings/Index', [
                'pageTitle' => 'Booking',
                'pageDescription' => 'Kelola booking cuci kendaraan',
            ]);
        })->name('bookings.index');

        Route::get('/bookings/create', function () {
            return Inertia::render('Kasir/Bookings/Create', [
                'pageTitle' => 'Booking Baru',
                'pageDescription' => 'Buat booking cuci kendaraan baru',
            ]);
        })->name('bookings.create');

        Route::get('/bookings/walkin', function () {
            return Inertia::render('Kasir/Bookings/WalkInCreate', [
                'pageTitle' => 'Walk-in Booking Baru',
                'pageDescription' => 'Buat booking walk-in pelanggan datangan langsung',
            ]);
        })->name('bookings.walkin');

        Route::post('/bookings/walkin', function () {
            // Handle walk-in booking creation
            return redirect()->route('kasir.antrian.kanban')->with('success', 'Walk-in booking berhasil dibuat');
        })->name('bookings.walkin.store');

        Route::get('/bookings/{booking}', function () {
            return Inertia::render('Kasir/Bookings/Show', [
                'pageTitle' => 'Detail Booking',
                'pageDescription' => 'Lihat detail booking cuci kendaraan',
            ]);
        })->name('bookings.show');

        Route::get('/customers', function () {
            return Inertia::render('Kasir/Customers/Index', [
                'pageTitle' => 'Pelanggan',
                'pageDescription' => 'Kelola data pelanggan cuci kendaraan',
            ]);
        })->name('customers.index');

        Route::get('/vehicles', function () {
            return Inertia::render('Kasir/Vehicles/Index', [
                'pageTitle' => 'Kendaraan',
                'pageDescription' => 'Kelola data kendaraan pelanggan',
            ]);
        })->name('vehicles.index');

        Route::get('/services', function () {
            return Inertia::render('Kasir/Services/Index', [
                'pageTitle' => 'Layanan',
                'pageDescription' => 'Kelola layanan cuci kendaraan',
            ]);
        })->name('services.index');

        Route::get('/payments', function () {
            return Inertia::render('Kasir/Payments/Index', [
                'pageTitle' => 'Pembayaran',
                'pageDescription' => 'Kelola pembayaran dan transaksi',
            ]);
        })->name('payments.index');

        Route::get('/payments/checkout/{booking}', function () {
            return Inertia::render('Kasir/Payments/Checkout', [
                'pageTitle' => 'Checkout & Struk',
                'pageDescription' => 'Proses pembayaran dan cetak struk',
            ]);
        })->name('payments.checkout');

        Route::post('/payments/checkout', function () {
            // Handle checkout
            return redirect()->route('kasir.antrian.kanban')->with('success', 'Pembayaran berhasil');
        })->name('payments.checkout.store');

        Route::get('/bookings/scan', function () {
            return Inertia::render('Kasir/Bookings/ScanQR', [
                'pageTitle' => 'Scan QR Booking',
                'pageDescription' => 'Scan QR code untuk verifikasi booking pelanggan',
            ]);
        })->name('bookings.scan');

        Route::post('/bookings/scan-action', function () {
            // Handle scan actions: start_wash, record_cash
            return redirect()->back()->with('success', 'Aksi berhasil');
        })->name('bookings.scan.action');

        Route::get('/operator', function () {
            return Inertia::render('Kasir/Operator/Dashboard', [
                'pageTitle' => 'Dashboard Operator',
                'pageDescription' => 'Kelola tugas pencucian yang ditugaskan',
            ]);
        })->name('operator.dashboard');

        Route::put('/operator/task/{id}/start', function () {
            // Start washing, start timer
            return response()->json(['success' => true]);
        })->name('operator.task.start');

        Route::put('/operator/task/{id}/progress', function () {
            // Upload progress photo
            return response()->json(['success' => true]);
        })->name('operator.task.progress');

        Route::put('/operator/task/{id}/complete', function () {
            // Upload final photo, mark complete
            return response()->json(['success' => true]);
        })->name('operator.task.complete');

        Route::get('/notifications', function () {
            return Inertia::render('Kasir/Notifications/Index', [
                'pageTitle' => 'Notifikasi',
                'pageDescription' => 'Kelola notifikasi dan pengingat',
            ]);
        })->name('notifications.index');

        Route::get('/reports', function () {
            return Inertia::render('Kasir/Reports/Index', [
                'pageTitle' => 'Laporan',
                'pageDescription' => 'Laporan penjualan dan performa',
            ]);
        })->name('reports.index');

        Route::get('/settings', function () {
            return Inertia::render('Kasir/Settings/Index', [
                'pageTitle' => 'Pengaturan',
                'pageDescription' => 'Konfigurasi aplikasi kasir',
            ]);
        })->name('settings.index');
    });
});

require __DIR__.'/auth.php';
