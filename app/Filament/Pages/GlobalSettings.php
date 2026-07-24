<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class GlobalSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.global-settings';
}
