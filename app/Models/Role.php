<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Traits\HasRoles;

class Role extends SpatieRole
{
    use HasFactory, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'is_default',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the permissions associated with the role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_has_permissions',
            'role_id',
            'permission_id'
        );
    }

    /**
     * Get the count of users/employees assigned to this role.
     */
    public function getPersonnelCountAttribute(): int
    {
        // Count users with this role via model_has_roles
        return \App\Models\User::role($this->name)->count();
    }

    /**
     * Get default roles for the car wash system.
     */
    public static function getDefaultRoles(): array
    {
        return [
            'admin_cabang' => [
                'name' => 'admin_cabang',
                'guard_name' => 'web',
                'description' => 'Admin Cabang - Full access to branch operations',
                'is_default' => true,
                'is_active' => true,
            ],
            'supervisor' => [
                'name' => 'supervisor',
                'guard_name' => 'web',
                'description' => 'Supervisor - Manage staff and oversee operations',
                'is_default' => true,
                'is_active' => true,
            ],
            'kasir' => [
                'name' => 'kasir',
                'guard_name' => 'web',
                'description' => 'Kasir - Handle bookings, payments, and customer service',
                'is_default' => true,
                'is_active' => true,
            ],
            'teknisi' => [
                'name' => 'teknisi',
                'guard_name' => 'web',
                'description' => 'Teknisi - Perform washing and detailing services',
                'is_default' => true,
                'is_active' => true,
            ],
        ];
    }

    /**
     * Get default permissions for the car wash system.
     */
    public static function getDefaultPermissions(): array
    {
        return [
            // Booking permissions
            'buat_booking' => ['name' => 'buat_booking', 'guard_name' => 'web', 'group' => 'Booking'],
            'lihat_booking' => ['name' => 'lihat_booking', 'guard_name' => 'web', 'group' => 'Booking'],
            'edit_booking' => ['name' => 'edit_booking', 'guard_name' => 'web', 'group' => 'Booking'],
            'hapus_booking' => ['name' => 'hapus_booking', 'guard_name' => 'web', 'group' => 'Booking', 'is_dangerous' => true],
            'update_status_booking' => ['name' => 'update_status_booking', 'guard_name' => 'web', 'group' => 'Booking'],
            
            // Customer permissions
            'lihat_pelanggan' => ['name' => 'lihat_pelanggan', 'guard_name' => 'web', 'group' => 'Pelanggan'],
            'buat_pelanggan' => ['name' => 'buat_pelanggan', 'guard_name' => 'web', 'group' => 'Pelanggan'],
            'edit_pelanggan' => ['name' => 'edit_pelanggan', 'guard_name' => 'web', 'group' => 'Pelanggan'],
            'hapus_pelanggan' => ['name' => 'hapus_pelanggan', 'guard_name' => 'web', 'group' => 'Pelanggan', 'is_dangerous' => true],
            
            // Service & Pricing permissions
            'lihat_layanan' => ['name' => 'lihat_layanan', 'guard_name' => 'web', 'group' => 'Layanan & Harga'],
            'buat_layanan' => ['name' => 'buat_layanan', 'guard_name' => 'web', 'group' => 'Layanan & Harga'],
            'edit_layanan' => ['name' => 'edit_layanan', 'guard_name' => 'web', 'group' => 'Layanan & Harga'],
            'hapus_layanan' => ['name' => 'hapus_layanan', 'guard_name' => 'web', 'group' => 'Layanan & Harga', 'is_dangerous' => true],
            'edit_harga' => ['name' => 'edit_harga', 'guard_name' => 'web', 'group' => 'Layanan & Harga', 'is_dangerous' => true],
            
            // Employee permissions
            'lihat_karyawan' => ['name' => 'lihat_karyawan', 'guard_name' => 'web', 'group' => 'Karyawan'],
            'buat_karyawan' => ['name' => 'buat_karyawan', 'guard_name' => 'web', 'group' => 'Karyawan'],
            'edit_karyawan' => ['name' => 'edit_karyawan', 'guard_name' => 'web', 'group' => 'Karyawan'],
            'hapus_karyawan' => ['name' => 'hapus_karyawan', 'guard_name' => 'web', 'group' => 'Karyawan', 'is_dangerous' => true],
            'kelola_shift' => ['name' => 'kelola_shift', 'guard_name' => 'web', 'group' => 'Karyawan'],
            
            // Transaction & Payment permissions
            'lihat_transaksi' => ['name' => 'lihat_transaksi', 'guard_name' => 'web', 'group' => 'Transaksi & Pembayaran'],
            'buat_transaksi' => ['name' => 'buat_transaksi', 'guard_name' => 'web', 'group' => 'Transaksi & Pembayaran'],
            'edit_transaksi' => ['name' => 'edit_transaksi', 'guard_name' => 'web', 'group' => 'Transaksi & Pembayaran'],
            'hapus_transaksi' => ['name' => 'hapus_transaksi', 'guard_name' => 'web', 'group' => 'Transaksi & Pembayaran', 'is_dangerous' => true],
            'proses_pembayaran' => ['name' => 'proses_pembayaran', 'guard_name' => 'web', 'group' => 'Transaksi & Pembayaran'],
            
            // Report permissions
            'lihat_laporan' => ['name' => 'lihat_laporan', 'guard_name' => 'web', 'group' => 'Laporan'],
            'ekspor_laporan' => ['name' => 'ekspor_laporan', 'guard_name' => 'web', 'group' => 'Laporan'],
            
            // Settings permissions
            'kelola_pengaturan' => ['name' => 'kelola_pengaturan', 'guard_name' => 'web', 'group' => 'Pengaturan', 'is_dangerous' => true],
            'kelola_role_permission' => ['name' => 'kelola_role_permission', 'guard_name' => 'web', 'group' => 'Pengaturan', 'is_dangerous' => true],
            
            // Branch permissions
            'lihat_cabang' => ['name' => 'lihat_cabang', 'guard_name' => 'web', 'group' => 'Cabang'],
            'kelola_cabang' => ['name' => 'kelola_cabang', 'guard_name' => 'web', 'group' => 'Cabang'],
        ];
    }

    /**
     * Get default role-permission mapping.
     */
    public static function getDefaultRolePermissions(): array
    {
        return [
            'admin_cabang' => [
                // Full access except super dangerous
                'buat_booking', 'lihat_booking', 'edit_booking', 'hapus_booking', 'update_status_booking',
                'lihat_pelanggan', 'buat_pelanggan', 'edit_pelanggan', 'hapus_pelanggan',
                'lihat_layanan', 'buat_layanan', 'edit_layanan', 'hapus_layanan', 'edit_harga',
                'lihat_karyawan', 'buat_karyawan', 'edit_karyawan', 'hapus_karyawan', 'kelola_shift',
                'lihat_transaksi', 'buat_transaksi', 'edit_transaksi', 'hapus_transaksi', 'proses_pembayaran',
                'lihat_laporan', 'ekspor_laporan',
                'kelola_pengaturan', 'kelola_role_permission',
                'lihat_cabang', 'kelola_cabang',
            ],
            'supervisor' => [
                // Management access, no dangerous settings
                'buat_booking', 'lihat_booking', 'edit_booking', 'update_status_booking',
                'lihat_pelanggan', 'buat_pelanggan', 'edit_pelanggan',
                'lihat_layanan', 'edit_layanan',
                'lihat_karyawan', 'buat_karyawan', 'edit_karyawan', 'kelola_shift',
                'lihat_transaksi', 'buat_transaksi', 'edit_transaksi', 'proses_pembayaran',
                'lihat_laporan', 'ekspor_laporan',
                'lihat_cabang',
            ],
            'kasir' => [
                // Booking, customer, transaction, payment
                'buat_booking', 'lihat_booking', 'edit_booking', 'update_status_booking',
                'lihat_pelanggan', 'buat_pelanggan', 'edit_pelanggan',
                'lihat_layanan',
                'lihat_transaksi', 'buat_transaksi', 'edit_transaksi', 'proses_pembayaran',
                'lihat_laporan',
            ],
            'teknisi' => [
                // Limited to viewing bookings and updating status
                'lihat_booking', 'update_status_booking',
                'lihat_layanan',
                'lihat_transaksi',
            ],
        ];
    }
}