<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Role berhasil dibuat')
            ->body('Role baru telah ditambahkan beserta izin yang dipilih.');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Perubahan')
                ->submit('save')
                ->icon('heroicon-o-check-circle')
                ->color('primary'),
            Action::make('reset')
                ->label('Reset Default')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => $this->resetForm())
                ->requiresConfirmation()
                ->modalHeading('Reset ke Default')
                ->modalDescription('Apakah Anda yakin ingin mengembalikan form ke nilai default? Semua perubahan yang belum disimpan akan hilang.')
                ->modalSubmitActionLabel('Ya, Reset'),
        ];
    }
}