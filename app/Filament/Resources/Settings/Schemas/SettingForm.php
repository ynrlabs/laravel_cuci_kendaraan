<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('SettingsTabs')
                    ->tabs([
                        Tab::make('General Info')
                            ->label('Informasi Umum')
                            ->icon('heroicon-o-information-circle')
                            ->schema(self::generalInfoSchema()),

                        Tab::make('Payment Gateway')
                            ->label('Payment Gateway')
                            ->icon('heroicon-o-credit-card')
                            ->schema(self::paymentGatewaySchema()),

                        Tab::make('Operational Hours')
                            ->label('Jam Operasional')
                            ->icon('heroicon-o-clock')
                            ->schema(self::operationalHoursSchema()),

                        Tab::make('Tax & Billing')
                            ->label('Pajak & Penagihan')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema(self::taxBillingSchema()),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected static function generalInfoSchema(): array
    {
        return [
            Section::make('Informasi Cabang')
                ->schema([
                    TextInput::make('branch_name')
                        ->label('Nama Cabang')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Nama cabang yang ditampilkan di aplikasi customer'),

                    TextInput::make('store_id')
                        ->label('Store ID')
                        ->disabled()
                        ->helperText('ID unik cabang (otomatis digenerate)')
                        ->dehydrated(false)
                        ->extraAttributes([
                            'class' => 'font-mono bg-gray-100',
                        ]),

                    Textarea::make('address')
                        ->label('Alamat Lengkap')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),

                    TextInput::make('phone')
                        ->label('Nomor Telepon')
                        ->tel()
                        ->maxLength(20)
                        ->placeholder('021-12345678'),

                    TextInput::make('email')
                        ->label('Email Cabang')
                        ->email()
                        ->maxLength(255)
                        ->placeholder('cabang@cucikuy.com'),
                ])
                ->columns(3),

            Section::make('Informasi Tambahan')
                ->schema([
                    TextInput::make('city')
                        ->label('Kota')
                        ->required(),

                    TextInput::make('province')
                        ->label('Provinsi')
                        ->required(),

                    TextInput::make('postal_code')
                        ->label('Kode Pos')
                        ->maxLength(10),

                    Placeholder::make('store_id_info')
                        ->label('Store ID')
                        ->content(fn ($get) => $get('store_id') ?? 'CK-' . Str::upper(Str::random(6)))
                        ->extraAttributes([
                            'class' => 'font-mono text-lg font-bold text-primary-600 bg-primary-50 px-4 py-2 rounded-lg border border-primary-200',
                        ])
                        ->columnSpanFull(),
                ])
                ->columns(3)
                ->collapsible(),
        ];
    }

    protected static function paymentGatewaySchema(): array
    {
        return [
            Section::make('Midtrans Configuration')
                ->description('Konfigurasi Midtrans untuk pembayaran QRIS, E-Wallet, Virtual Account, dan Credit Card')
                ->schema([
                    TextInput::make('midtrans_server_key')
                        ->label('Server Key')
                        ->password()
                        ->maxLength(255)
                        ->helperText('Server Key dari dashboard Midtrans (format: SB-Mid-server-xxx)'),

                    TextInput::make('midtrans_client_key')
                        ->label('Client Key')
                        ->maxLength(255)
                        ->helperText('Client Key dari dashboard Midtrans (format: SB-Mid-client-xxx)'),

                    Select::make('midtrans_environment')
                        ->label('Environment')
                        ->options([
                            'sandbox' => 'Sandbox (Development)',
                            'production' => 'Production (Live)',
                        ])
                        ->default('sandbox')
                        ->required(),

                    TextInput::make('midtrans_merchant_id')
                        ->label('Merchant ID')
                        ->maxLength(255)
                        ->helperText('Merchant ID dari dashboard Midtrans (opsional)'),
                ])
                ->columns(2)
                ->collapsible(),

            Section::make('Xendit Configuration')
                ->description('Konfigurasi Xendit sebagai payment gateway alternatif/failover')
                ->schema([
                    TextInput::make('xendit_api_key')
                        ->label('API Key')
                        ->password()
                        ->maxLength(255)
                        ->helperText('API Key dari dashboard Xendit (format: xnd_development_xxx atau xnd_production_xxx)'),

                    Select::make('xendit_environment')
                        ->label('Environment')
                        ->options([
                            'development' => 'Development (Sandbox)',
                            'production' => 'Production (Live)',
                        ])
                        ->default('development')
                        ->required(),

                    Toggle::make('xendit_enabled')
                        ->label('Aktifkan Xendit')
                        ->default(false)
                        ->helperText('Aktifkan Xendit sebagai gateway tambahan'),
                ])
                ->columns(2)
                ->collapsible(),

            Section::make('Payment General Settings')
                ->schema([
                    Toggle::make('payment_auto_capture')
                        ->label('Auto Capture Payment')
                        ->default(true)
                        ->helperText('Otomatis capture pembayaran setelah success (Midtrans)'),

                    Select::make('default_payment_gateway')
                        ->label('Default Payment Gateway')
                        ->options([
                            'midtrans' => 'Midtrans',
                            'xendit' => 'Xendit',
                        ])
                        ->default('midtrans')
                        ->required(),
                ])
                ->columns(2)
                ->collapsible(),
        ];
    }

    protected static function operationalHoursSchema(): array
    {
        $days = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        ];

        $schema = [];

        foreach ($days as $key => $label) {
            $schema[] = Section::make($label)
                ->schema([
                    Toggle::make("operational_hours.{$key}.is_open")
                        ->label('Buka')
                        ->default(true)
                        ->inline(false),

                    TextInput::make("operational_hours.{$key}.open_time")
                        ->label('Jam Buka')
                        ->placeholder('08:00')
                        ->mask('99:99')
                        ->visible(fn ($get) => $get("operational_hours.{$key}.is_open")),

                    TextInput::make("operational_hours.{$key}.close_time")
                        ->label('Jam Tutup')
                        ->placeholder('20:00')
                        ->mask('99:99')
                        ->visible(fn ($get) => $get("operational_hours.{$key}.is_open")),

                    TextInput::make("operational_hours.{$key}.break_start")
                        ->label('Istirahat Mulai')
                        ->placeholder('12:00')
                        ->mask('99:99')
                        ->visible(fn ($get) => $get("operational_hours.{$key}.is_open"))
                        ->helperText('Opsional: jam istirahat (misal: 12:00-13:00)'),

                    TextInput::make("operational_hours.{$key}.break_end")
                        ->label('Istirahat Selesai')
                        ->placeholder('13:00')
                        ->mask('99:99')
                        ->visible(fn ($get) => $get("operational_hours.{$key}.is_open")),
                ])
                ->columns(3)
                ->collapsible();
        }

        return $schema;
    }

    protected static function taxBillingSchema(): array
    {
        return [
            Section::make('Pajak & Biaya')
                ->schema([
                    TextInput::make('tax_rate')
                        ->label('Tax Rate (%)')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(11.00)
                        ->required()
                        ->helperText('Persentase PPN (default: 11%)'),

                    TextInput::make('booking_cutoff_hours')
                        ->label('Booking Cutoff (Jam)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(24)
                        ->default(2)
                        ->required()
                        ->helperText('Minimum jam sebelum slot booking bisa dipesan (default: 2 jam)'),

                    TextInput::make('payment_expired_minutes')
                        ->label('Payment Expired (Menit)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(1440)
                        ->default(30)
                        ->required()
                        ->helperText('Waktu expired pembayaran online (default: 30 menit)'),

                    TextInput::make('auto_cancel_hours')
                        ->label('Auto Cancel (Jam)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(48)
                        ->default(2)
                        ->required()
                        ->helperText('Auto cancel booking jika tidak dibayar dalam X jam (default: 2 jam)'),

                    TextInput::make('max_photos_per_booking')
                        ->label('Max Foto per Booking')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(10)
                        ->default(3)
                        ->required(),

                    TextInput::make('min_rating_for_promo')
                        ->label('Min Rating untuk Promo')
                        ->numeric()
                        ->step(0.1)
                        ->minValue(0)
                        ->maxValue(5)
                        ->default(4.0)
                        ->required(),
                ])
                ->columns(3),

            Section::make('Fitur & Opsi')
                ->schema([
                    Toggle::make('enable_rating')
                        ->label('Aktifkan Rating & Review')
                        ->default(true)
                        ->helperText('Izinkan customer memberikan rating setelah selesai'),

                    Toggle::make('enable_chat')
                        ->label('Aktifkan Chat Customer-Operator')
                        ->default(false)
                        ->helperText('Aktifkan fitur chat real-time antara customer dan operator'),

                    Toggle::make('maintenance_mode')
                        ->label('Maintenance Mode')
                        ->default(false)
                        ->helperText('Aktifkan mode maintenance (nonaktifkan booking baru)'),

                    Toggle::make('enable_loyalty')
                        ->label('Aktifkan Loyalty Program')
                        ->default(false)
                        ->helperText('Aktifkan sistem poin loyalitas (akan rilis v2)'),

                    TextInput::make('terms_url')
                        ->label('Terms & Conditions URL')
                        ->url()
                        ->maxLength(500)
                        ->placeholder('https://cucikuy.com/terms'),

                    TextInput::make('privacy_url')
                        ->label('Privacy Policy URL')
                        ->url()
                        ->maxLength(500)
                        ->placeholder('https://cucikuy.com/privacy'),

                    TextInput::make('support_whatsapp')
                        ->label('Support WhatsApp')
                        ->tel()
                        ->maxLength(20)
                        ->placeholder('6281234567890'),

                    TextInput::make('support_email')
                        ->label('Support Email')
                        ->email()
                        ->maxLength(255)
                        ->placeholder('support@cucikuy.com'),
                ])
                ->columns(2)
                ->collapsible(),

            Section::make('Aplikasi')
                ->schema([
                    TextInput::make('app_name')
                        ->label('Nama Aplikasi')
                        ->maxLength(100)
                        ->default('CuciKuy')
                        ->required(),

                    TextInput::make('currency')
                        ->label('Mata Uang')
                        ->maxLength(3)
                        ->default('IDR')
                        ->required()
                        ->helperText('Kode mata uang ISO 4217'),

                    Select::make('timezone')
                        ->label('Zona Waktu')
                        ->options([
                            'Asia/Jakarta' => 'Asia/Jakarta (WIB)',
                            'Asia/Makassar' => 'Asia/Makassar (WITA)',
                            'Asia/Jayapura' => 'Asia/Jayapura (WIT)',
                        ])
                        ->default('Asia/Jakarta')
                        ->required(),

                    Select::make('locale')
                        ->label('Bahasa Default')
                        ->options([
                            'id' => 'Indonesia',
                            'en' => 'English',
                        ])
                        ->default('id')
                        ->required(),
                ])
                ->columns(2)
                ->collapsible(),
        ];
    }
}