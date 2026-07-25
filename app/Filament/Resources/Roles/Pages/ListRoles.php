<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Role')
                ->icon('heroicon-o-plus'),
            
            Action::make('seed_default_roles')
                ->label('Seed Role Default')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Seed Role Default')
                ->modalDescription('Ini akan membuat 4 role default (Admin Cabang, Supervisor, Kasir, Teknisi) dengan izin standar. Role yang sudah ada tidak akan ditimpa. Lanjutkan?')
                ->modalSubmitActionLabel('Ya, Seed')
                ->modalCancelActionLabel('Batal')
                ->action(fn () => $this->seedDefaultRoles()),
        ];
    }

    protected function seedDefaultRoles(): void
    {
        $defaultRoles = Role::getDefaultRoles();
        $defaultPermissions = Role::getDefaultPermissions();
        $defaultRolePermissions = Role::getDefaultRolePermissions();
        
        foreach ($defaultRoles as $roleKey => $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => $roleData['guard_name']],
                $roleData
            );
            
            // Create permissions if they don't exist
            foreach ($defaultPermissions as $permKey => $permData) {
                Permission::firstOrCreate(
                    ['name' => $permData['name'], 'guard_name' => $permData['guard_name']],
                    $permData
                );
            }
            
            // Sync permissions
            if (isset($defaultRolePermissions[$roleKey])) {
                $permissions = Permission::whereIn('name', $defaultRolePermissions[$roleKey])->get();
                $role->syncPermissions($permissions);
            }
        }
        
        \Filament\Notifications\Notification::make()
            ->title('Role default berhasil dibuat')
            ->body('4 role default dengan izin standar telah dibuat/diperbarui.')
            ->success()
            ->send();
    }
}