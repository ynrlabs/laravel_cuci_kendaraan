<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Models\Permission;
use App\Models\Role;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Role')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Role')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('contoh: admin_cabang, supervisor, kasir, teknisi')
                            ->helperText('Gunakan snake_case, contoh: admin_cabang'),
                        
                        TextInput::make('guard_name')
                            ->label('Guard')
                            ->default('web')
                            ->required()
                            ->maxLength(50)
                            ->hidden(),
                        
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->placeholder('Deskripsi role ini...')
                            ->columnSpanFull(),
                        
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->required(),
                        
                        Toggle::make('is_default')
                            ->label('Default')
                            ->default(false)
                            ->helperText('Role default akan dibuat otomatis saat seeding'),
                    ])->columns(2),

                Section::make('Konfigurasi Izin (Permission)')
                    ->description('Centang izin yang ingin diberikan ke role ini. Izin berbahaya ditandai dengan warna merah.')
                    ->schema([
                        Hidden::make('permission_groups')
                            ->default(fn () => Permission::getGroups()),
                        
                        self::getPermissionCheckboxList(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(false),
                
                Section::make('Aksi Berbahaya')
                    ->description('Izin berikut berpotensi berbahaya dan dapat menyebabkan kehilangan data. Berikan dengan hati-hati.')
                    ->schema([
                        self::getDangerousPermissionsWarning(),
                    ])
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed()
                    ->icon('heroicon-o-exclamation-triangle')
                    ->iconColor('danger'),
            ])
            ->columns(1);
    }

    protected static function getPermissionCheckboxList(): CheckboxList
    {
        $permissions = Permission::query()
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group');

        $options = [];
        foreach ($permissions as $group => $groupPermissions) {
            foreach ($groupPermissions as $permission) {
                $label = self::formatPermissionLabel($permission->name);
                $options[$permission->name] = $label . ($permission->is_dangerous ? ' ⚠️' : '');
            }
        }

        return CheckboxList::make('permissions')
            ->label('Izin per Kategori')
            ->options($options)
            ->columns(3)
            ->searchable()
            ->helperText('Pilih izin untuk role ini. Izin dengan ⚠️ adalah aksi berbahaya.')
            ->getOptionLabelUsing(fn (string $value): string => self::formatPermissionLabel($value))
            ->getOptionColorUsing(function (string $value): ?string {
                $permission = Permission::where('name', $value)->first();
                return $permission?->is_dangerous ? 'danger' : null;
            })
            ->getOptionWeightUsing(function (string $value): ?string {
                $permission = Permission::where('name', $value)->first();
                return $permission?->is_dangerous ? 'bold' : 'normal';
            });
    }

    protected static function getDangerousPermissionsWarning(): \Filament\Forms\Components\Placeholder
    {
        return \Filament\Forms\Components\Placeholder::make('dangerous_warning')
            ->label('Peringatan')
            ->content(
                '<div class="space-y-2">
                    <p class="font-medium text-red-700 dark:text-red-300">⚠️ Aksi Berbahaya Terdeteksi</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Role ini memiliki akses ke izin berbahaya berikut:
                    </p>
                    <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400" id="dangerous-permissions-list">
                        <li>Memuat...</li>
                    </ul>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                        Pastikan hanya memberikan akses ini ke personel yang dipercaya dan terlatih.
                    </p>
                </div>'
            )
            ->extraAttributes([
                'class' => 'p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg',
                'x-data' => <<<'JS'
{
    dangerousPermissions: [],
    init() {
        this.$watch("$wire.permissions", (value) => {
            this.updateDangerousList(value);
        });
    },
    updateDangerousList(permissions) {
        const dangerous = [
            "hapus_booking", "hapus_pelanggan", "hapus_layanan", 
            "edit_harga", "hapus_karyawan", "hapus_transaksi",
            "kelola_pengaturan", "kelola_role_permission"
        ];
        this.dangerousPermissions = permissions.filter(p => dangerous.includes(p));
        const list = document.getElementById("dangerous-permissions-list");
        if (list) {
            if (this.dangerousPermissions.length > 0) {
                list.innerHTML = this.dangerousPermissions.map(p => 
                    `<li>${this.formatLabel(p)}</li>`
                ).join("");
            } else {
                list.innerHTML = "<li class=\"text-green-600\">Tidak ada izin berbahaya yang dipilih</li>";
            }
        }
    },
    formatLabel(name) {
        return name.split("_").map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(" ");
    }
}
JS
            ]);
    }

    protected static function formatPermissionLabel(string $name): string
    {
        $labels = [
            'buat_booking' => 'Buat Booking',
            'lihat_booking' => 'Lihat Booking',
            'edit_booking' => 'Edit Booking',
            'hapus_booking' => 'Hapus Booking',
            'update_status_booking' => 'Update Status Booking',
            
            'lihat_pelanggan' => 'Lihat Pelanggan',
            'buat_pelanggan' => 'Buat Pelanggan',
            'edit_pelanggan' => 'Edit Pelanggan',
            'hapus_pelanggan' => 'Hapus Pelanggan',
            
            'lihat_layanan' => 'Lihat Layanan',
            'buat_layanan' => 'Buat Layanan',
            'edit_layanan' => 'Edit Layanan',
            'hapus_layanan' => 'Hapus Layanan',
            'edit_harga' => 'Edit Harga',
            
            'lihat_karyawan' => 'Lihat Karyawan',
            'buat_karyawan' => 'Buat Karyawan',
            'edit_karyawan' => 'Edit Karyawan',
            'hapus_karyawan' => 'Hapus Karyawan',
            'kelola_shift' => 'Kelola Shift',
            
            'lihat_transaksi' => 'Lihat Transaksi',
            'buat_transaksi' => 'Buat Transaksi',
            'edit_transaksi' => 'Edit Transaksi',
            'hapus_transaksi' => 'Hapus Transaksi',
            'proses_pembayaran' => 'Proses Pembayaran',
            
            'lihat_laporan' => 'Lihat Laporan',
            'ekspor_laporan' => 'Ekspor Laporan',
            
            'kelola_pengaturan' => 'Kelola Pengaturan',
            'kelola_role_permission' => 'Kelola Role & Permission',
            
            'lihat_cabang' => 'Lihat Cabang',
            'kelola_cabang' => 'Kelola Cabang',
        ];

        return $labels[$name] ?? ucfirst(str_replace('_', ' ', $name));
    }
}