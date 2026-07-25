<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\Branch;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use App\Enums\LoyaltyTier;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('user.name')
                                    ->label('Full Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->relationship('user', 'name'),

                                TextInput::make('user.email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->relationship('user', 'email'),

                                TextInput::make('user.phone')
                                    ->label('Phone')
                                    ->tel()
                                    ->required()
                                    ->maxLength(20)
                                    ->relationship('user', 'phone'),

                                Select::make('loyalty_tier')
                                    ->label('Loyalty Tier')
                                    ->options(LoyaltyTier::class)
                                    ->default(LoyaltyTier::BRONZE->value)
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('preferred_branch_id')
                                    ->label('Preferred Branch')
                                    ->relationship('preferredBranch', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Select branch'),

                                TextInput::make('vehicle_plate')
                                    ->label('Vehicle Plate')
                                    ->maxLength(20)
                                    ->placeholder('e.g., B 1234 ABC'),

                                TextInput::make('vehicle_type')
                                    ->label('Vehicle Type')
                                    ->maxLength(50)
                                    ->placeholder('e.g., Sedan, SUV, Motorcycle'),

                                TextInput::make('vehicle_color')
                                    ->label('Vehicle Color')
                                    ->maxLength(30)
                                    ->placeholder('e.g., Black, White, Silver'),
                            ]),
                    ])
                    ->columns(2),

                Section::make('Profile Photo')
                    ->schema([
                        FileUpload::make('user.avatar')
                            ->label('Avatar')
                            ->disk('public')
                            ->directory('customers/avatars')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('200')
                            ->imageResizeTargetHeight('200')
                            ->maxSize(2048)
                            ->relationship('user', 'avatar'),
                    ])
                    ->collapsible(),

                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->placeholder('Internal notes about this customer...'),

                        Toggle::make('user.is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->relationship('user', 'is_active')
                            ->inline(false),
                    ]),
            ]);
    }
}