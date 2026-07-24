<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class FinancialReport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.financial-report';
}
