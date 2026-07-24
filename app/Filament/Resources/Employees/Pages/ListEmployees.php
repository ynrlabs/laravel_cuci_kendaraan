<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\Employees\Widgets\EmployeeStatsWidget::class,
            \App\Filament\Resources\Employees\Widgets\ShiftScheduleWidget::class,
            \App\Filament\Resources\Employees\Widgets\CommissionRatesWidget::class,
        ];
    }
}
