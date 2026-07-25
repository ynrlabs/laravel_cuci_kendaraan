<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'group',
        'description',
        'is_dangerous',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_dangerous' => 'boolean',
        ];
    }



    /**
     * Scope a query to only include dangerous permissions.
     */
    public function scopeDangerous($query)
    {
        return $query->where('is_dangerous', true);
    }

    /**
     * Scope a query to only include safe permissions.
     */
    public function scopeSafe($query)
    {
        return $query->where('is_dangerous', false);
    }

    /**
     * Scope a query to filter by group.
     */
    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Get all permission groups.
     */
    public static function getGroups(): array
    {
        return [
            'Booking' => 'Booking',
            'Pelanggan' => 'Pelanggan',
            'Layanan & Harga' => 'Layanan & Harga',
            'Karyawan' => 'Karyawan',
            'Transaksi & Pembayaran' => 'Transaksi & Pembayaran',
            'Laporan' => 'Laporan',
            'Pengaturan' => 'Pengaturan',
            'Cabang' => 'Cabang',
        ];
    }
}