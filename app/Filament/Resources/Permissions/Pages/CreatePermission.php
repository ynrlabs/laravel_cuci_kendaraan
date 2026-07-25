<?php

namespace App\Filament\Resources\Permissions\Pages;

use App\Filament\Resources\Permissions\PermissionResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Izin berhasil dibuat')
            ->body('Izin baru telah ditambahkan.');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->submit('save')
                ->icon('heroicon-o-check-circle')
                ->color('primary'),
        ];
    }
}