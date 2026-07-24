<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Enums\EmployeePosition;
use App\Enums\SalaryType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('position')
                    ->options(EmployeePosition::class)
                    ->required()
                    ->native(false),
                TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(20),
                DatePicker::make('hire_date')
                    ->required()
                    ->default(now()),
                Radio::make('salary_type')
                    ->label('Salary Type')
                    ->options(SalaryType::class)
                    ->required()
                    ->inline()
                    ->default(SalaryType::Monthly),
                TextInput::make('base_salary')
                    ->label('Base Salary')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->step(10000)
                    ->default(0),
                TextInput::make('commission_rate')
                    ->label('Commission Rate (%)')
                    ->required()
                    ->numeric()
                    ->step(0.1)
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0)
                    ->suffix('%'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->required()
                    ->default(true),
                FileUpload::make('avatar')
                    ->label('Profile Picture')
                    ->image()
                    ->disk('public')
                    ->directory('employees')
                    ->maxSize(2048)
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('1:1')
                    ->imageResizeTargetWidth('200')
                    ->imageResizeTargetHeight('200')
                    ->columnSpanFull(),
                TextInput::make('skills')
                    ->label('Skills (comma separated)')
                    ->placeholder('Wax, Detailing, Interior Cleaning, Engine Bay')
                    ->helperText('Enter skills separated by commas')
                    ->columnSpanFull(),
            ])->columns(2);
    }
}
