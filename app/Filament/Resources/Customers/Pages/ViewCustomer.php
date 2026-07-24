<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Booking;
use App\Models\Payment;
use App\Enums\LoyaltyTier;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Actions\ActionGroup;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\IconPosition;
use Illuminate\Support\Facades\Storage;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn() => $this->getResource()::getUrl('edit', ['record' => $this->record])),

                Action::make('block')
                    ->label(fn() => $this->record->user?->is_active ? 'Block Customer' : 'Unblock Customer')
                    ->icon(fn() => $this->record->user?->is_active ? 'heroicon-o-user-minus' : 'heroicon-o-user-plus')
                    ->color(fn() => $this->record->user?->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn() => $this->record->user?->is_active ? 'Block Customer' : 'Unblock Customer')
                    ->modalDescription(fn() => $this->record->user?->is_active
                        ? 'Are you sure you want to block this customer? They will not be able to make new bookings.'
                        : 'Are you sure you want to unblock this customer? They will be able to make bookings again.')
                    ->action(function () {
                        $this->record->user->update(['is_active' => !$this->record->user->is_active]);
                        $this->refresh();
                    }),

                ExportAction::make('export')
                    ->label('Export Data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exports([
                        \App\Filament\Exports\CustomerExporter::class,
                    ]),
            ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('Customer Details')
                    ->tabs([
                        Tab::make('Profile')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Profile Photo')
                                    ->schema([
                                        Grid::make(1)
                                            ->schema([
                                                ImageEntry::make('user.avatar')
                                                    ->label('')
                                                    ->disk('public')
                                                    ->defaultImageUrl(fn($record) => $record->user?->avatar 
                                                        ? Storage::url($record->user->avatar) 
                                                        : "https://ui-avatars.com/api/?name=" . urlencode($record->user?->name ?? 'Customer') . "&color=FFFFFF&background=3B82F6&size=200")
                                                    ->circular()
                                                    ->size(120),
                                            ])
                                            ->extraAttributes(['class' => 'justify-center']),
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('user.name')
                                                    ->label('Full Name')
                                                    ->weight('bold')
                                                    ->size('lg'),
                                                TextEntry::make('user.email')
                                                    ->label('Email')
                                                    ->icon('heroicon-m-envelope')
                                                    ->copyable()
                                                    ->copyMessage('Email copied!'),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('user.phone')
                                                    ->label('Phone')
                                                    ->icon('heroicon-m-phone')
                                                    ->copyable()
                                                    ->copyMessage('Phone copied!'),
                                                TextEntry::make('user.email_verified_at')
                                                    ->label('Email Verified')
                                                    ->formatStateUsing(fn($state) => $state ? $state->format('d M Y') : 'Not verified')
                                                    ->icon(fn($state) => $state ? 'heroicon-m-check-badge' : 'heroicon-m-x-circle')
                                                    ->color(fn($state) => $state ? 'success' : 'danger'),
                                            ]),
                                    ])
                                    ->collapsible(),

                                Section::make('Loyalty & Status')
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                TextEntry::make('loyalty_tier')
                                                    ->label('Loyalty Tier')
                                                    ->badge()
                                                    ->color(fn($state) => match ($state) {
                                                        LoyaltyTier::PLATINUM->value => Color::Amber,
                                                        LoyaltyTier::GOLD->value => Color::Yellow,
                                                        LoyaltyTier::SILVER->value => Color::Gray,
                                                        default => Color::Amber,
                                                    })
                                                    ->icon(fn($state) => match ($state) {
                                                        LoyaltyTier::PLATINUM->value => 'heroicon-m-star',
                                                        LoyaltyTier::GOLD->value => 'heroicon-m-star',
                                                        LoyaltyTier::SILVER->value => 'heroicon-m-star',
                                                        default => 'heroicon-m-user',
                                                    }),
                                                TextEntry::make('total_visits')
                                                    ->label('Total Visits')
                                                    ->icon('heroicon-m-clipboard-document-check')
                                                    ->weight('bold')
                                                    ->color('primary'),
                                                TextEntry::make('total_spent')
                                                    ->label('Total Spend')
                                                    ->money('IDR')
                                                    ->icon('heroicon-m-currency-dollar')
                                                    ->weight('bold')
                                                    ->color('success'),
                                                TextEntry::make('bookings_count')
                                                    ->label('Total Bookings')
                                                    ->icon('heroicon-m-calendar-days')
                                                    ->weight('bold')
                                                    ->color('info'),
                                            ]),
                                    ])
                                    ->collapsible(),
                            ]),

                        Tab::make('Vehicle & Branch')
                            ->icon('heroicon-o-truck')
                            ->schema([
                                Section::make('Vehicle Information')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('vehicle_plate')
                                                    ->label('License Plate')
                                                    ->badge()
                                                    ->color('primary')
                                                    ->size('lg')
                                                    ->weight('bold')
                                                    ->placeholder('Not registered'),
                                                TextEntry::make('vehicle_type')
                                                    ->label('Vehicle Type')
                                                    ->icon('heroicon-m-truck')
                                                    ->placeholder('Not registered'),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('vehicle_color')
                                                    ->label('Color')
                                                    ->icon('heroicon-m-paint-brush')
                                                    ->placeholder('Not registered'),
                                                TextEntry::make('vehicle_brand')
                                                    ->label('Brand')
                                                    ->icon('heroicon-m-tag')
                                                    ->placeholder('Not registered'),
                                            ]),
                                    ])
                                    ->collapsible(),

                                Section::make('Preferred Branch')
                                    ->schema([
                                        Grid::make(1)
                                            ->schema([
                                                TextEntry::make('preferredBranch.name')
                                                    ->label('Branch Name')
                                                    ->icon('heroicon-m-building-office')
                                                    ->placeholder('No preferred branch'),
                                                TextEntry::make('preferredBranch.address')
                                                    ->label('Address')
                                                    ->icon('heroicon-m-map-pin')
                                                    ->placeholder('Not set'),
                                            ]),
                                    ])
                                    ->collapsible(),
                            ]),

                        Tab::make('Contact & Notes')
                            ->icon('heroicon-o-envelope-open')
                            ->schema([
                                Section::make('Contact Information')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('user.name')
                                                    ->label('Full Name'),
                                                TextEntry::make('user.email')
                                                    ->label('Email')
                                                    ->copyable(),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('user.phone')
                                                    ->label('Phone')
                                                    ->copyable(),
                                                TextEntry::make('user.last_login_at')
                                                    ->label('Last Login')
                                                    ->formatStateUsing(fn($state) => $state ? $state->format('d M Y H:i') : 'Never'),
                                            ]),
                                    ])
                                    ->collapsible(),

                                Section::make('Notes')
                                    ->schema([
                                        TextEntry::make('notes')
                                            ->label('Customer Notes')
                                            ->columnSpanFull()
                                            ->placeholder('No notes')
                                            ->markdown(),
                                    ])
                                    ->collapsible(),
                            ]),

                        Tab::make('Metrics')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Section::make('Key Metrics')
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                TextEntry::make('total_spent')
                                                    ->label('Total Spend')
                                                    ->money('IDR')
                                                    ->icon('heroicon-m-currency-dollar')
                                                    ->weight('bold')
                                                    ->size('lg')
                                                    ->color('success'),
                                                TextEntry::make('reward_points')
                                                    ->label('Reward Points')
                                                    ->icon('heroicon-m-star')
                                                    ->weight('bold')
                                                    ->size('lg')
                                                    ->color('warning')
                                                    ->formatStateUsing(fn($state) => $state ?? 0),
                                                TextEntry::make('avg_duration')
                                                    ->label('Avg. Duration (min)')
                                                    ->icon('heroicon-m-clock')
                                                    ->weight('bold')
                                                    ->size('lg')
                                                    ->color('info')
                                                    ->getStateUsing(fn($record) => $record->bookings()->where('status', 'completed')->avg('duration_minutes') ? round($record->bookings()->where('status', 'completed')->avg('duration_minutes')) : 'N/A'),
                                                TextEntry::make('satisfaction')
                                                    ->label('Satisfaction')
                                                    ->icon('heroicon-m-heart')
                                                    ->weight('bold')
                                                    ->size('lg')
                                                    ->color('danger')
                                                    ->getStateUsing(fn($record) => $record->bookings()->whereNotNull('rating')->avg('rating') ? round($record->bookings()->whereNotNull('rating')->avg('rating'), 1) . '/5' : 'N/A'),
                                            ]),
                                    ])
                                    ->collapsible(),
                            ]),

                        Tab::make('Transaction History')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Section::make('Recent Transactions')
                                    ->schema([
                                        RepeatableEntry::make('recent_bookings')
                                            ->label('')
                                            ->schema([
                                                Grid::make(5)
                                                    ->schema([
                                                        TextEntry::make('booking_date')
                                                            ->label('Date')
                                                            ->formatStateUsing(fn($state) => $state->format('d M Y')),
                                                        TextEntry::make('booking_code')
                                                            ->label('Booking #')
                                                            ->weight('medium')
                                                            ->color('primary'),
                                                        TextEntry::make('service.name')
                                                            ->label('Service')
                                                            ->placeholder('-'),
                                                        TextEntry::make('total')
                                                            ->label('Amount')
                                                            ->money('IDR')
                                                            ->color('success')
                                                            ->weight('bold'),
                                                        TextEntry::make('status')
                                                            ->label('Status')
                                                            ->badge()
                                                            ->color(fn($state) => match ($state) {
                                                                'completed' => 'success',
                                                                'pending' => 'warning',
                                                                'cancelled' => 'danger',
                                                                'in_progress' => 'info',
                                                                default => 'gray',
                                                            }),
                                                    ]),
                                            ])
                                            ->columns(1),
                                    ])
                                    ->collapsible(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
