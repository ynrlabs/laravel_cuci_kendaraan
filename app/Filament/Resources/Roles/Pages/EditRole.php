<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Role berhasil diperbarui')
            ->body('Perubahan role dan izin telah disimpan.');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Perubahan')
                ->submit('save')
                ->icon('heroicon-o-check-circle')
                ->color('primary'),
            Action::make('reset_default')
                ->label('Reset ke Default')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Reset ke Default')
                ->modalDescription('Ini akan mengisi form dengan izin default untuk role ini. Perubahan yang belum disimpan akan hilang. Lanjutkan?')
                ->modalSubmitActionLabel('Ya, Reset')
                ->modalCancelActionLabel('Batal')
                ->action(fn () => $this->fillFormWithDefaultPermissions()),
            
            DeleteAction::make()
                ->label('Hapus')
                ->requiresConfirmation()
                ->modalHeading('Hapus Role')
                ->modalDescription('Apakah Anda yakin ingin menghapus role ini? Semua izin yang terkait akan dihapus.')
                ->modalSubmitActionLabel('Ya, Hapus')
                ->modalCancelActionLabel('Batal'),
        ];
    }

    protected function fillFormWithDefaultPermissions(): void
    {
        $roleName = $this->record->name ?? '';
        $defaultPermissions = Role::getDefaultRolePermissions();
        
        if (isset($defaultPermissions[$roleName])) {
            $this->form->fill([
                'permissions' => $defaultPermissions[$roleName],
            ]);
        }
    }
}