<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Queue extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationLabel = 'Queue';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.queue';
}
