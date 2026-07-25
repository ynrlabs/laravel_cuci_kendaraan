<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'group' => 'general',
                'key' => 'app_name',
                'value' => 'CuciKuy',
                'type' => 'string',
            ],
            [
                'group' => 'general',
                'key' => 'currency',
                'value' => 'IDR',
                'type' => 'string',
            ],
            [
                'group' => 'general',
                'key' => 'timezone',
                'value' => 'Asia/Jakarta',
                'type' => 'string',
            ],
            [
                'group' => 'general',
                'key' => 'locale',
                'value' => 'id',
                'type' => 'string',
            ],
            [
                'group' => 'general',
                'key' => 'terms_url',
                'value' => '',
                'type' => 'string',
            ],
            [
                'group' => 'general',
                'key' => 'privacy_url',
                'value' => '',
                'type' => 'string',
            ],
            [
                'group' => 'general',
                'key' => 'support_whatsapp',
                'value' => '',
                'type' => 'string',
            ],
            [
                'group' => 'general',
                'key' => 'support_email',
                'value' => '',
                'type' => 'string',
            ],
            [
                'group' => 'general',
                'key' => 'maintenance_mode',
                'value' => 'false',
                'type' => 'boolean',
            ],
            [
                'group' => 'general',
                'key' => 'enable_loyalty',
                'value' => 'false',
                'type' => 'boolean',
            ],

            // Payment Gateway Settings - Midtrans
            [
                'group' => 'payment',
                'key' => 'midtrans_server_key',
                'value' => '',
                'type' => 'string',
            ],
            [
                'group' => 'payment',
                'key' => 'midtrans_client_key',
                'value' => '',
                'type' => 'string',
            ],
            [
                'group' => 'payment',
                'key' => 'midtrans_environment',
                'value' => 'sandbox',
                'type' => 'string',
            ],
            [
                'group' => 'payment',
                'key' => 'midtrans_merchant_id',
                'value' => '',
                'type' => 'string',
            ],

            // Payment Gateway Settings - Xendit
            [
                'group' => 'payment',
                'key' => 'xendit_api_key',
                'value' => '',
                'type' => 'string',
            ],
            [
                'group' => 'payment',
                'key' => 'xendit_environment',
                'value' => 'development',
                'type' => 'string',
            ],
            [
                'group' => 'payment',
                'key' => 'xendit_enabled',
                'value' => 'false',
                'type' => 'boolean',
            ],

            // Payment General Settings
            [
                'group' => 'payment',
                'key' => 'payment_auto_capture',
                'value' => 'true',
                'type' => 'boolean',
            ],
            [
                'group' => 'payment',
                'key' => 'default_payment_gateway',
                'value' => 'midtrans',
                'type' => 'string',
            ],

            // Operational Hours (JSON)
            [
                'group' => 'operational',
                'key' => 'operational_hours',
                'value' => json_encode([
                    'monday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '20:00', 'break_start' => '12:00', 'break_end' => '13:00'],
                    'tuesday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '20:00', 'break_start' => '12:00', 'break_end' => '13:00'],
                    'wednesday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '20:00', 'break_start' => '12:00', 'break_end' => '13:00'],
                    'thursday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '20:00', 'break_start' => '12:00', 'break_end' => '13:00'],
                    'friday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '20:00', 'break_start' => '12:00', 'break_end' => '13:00'],
                    'saturday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '20:00', 'break_start' => '12:00', 'break_end' => '13:00'],
                    'sunday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '20:00', 'break_start' => '12:00', 'break_end' => '13:00'],
                ]),
                'type' => 'json',
            ],

            // Tax & Billing Settings
            [
                'group' => 'tax',
                'key' => 'tax_rate',
                'value' => '11.00',
                'type' => 'decimal',
            ],
            [
                'group' => 'tax',
                'key' => 'booking_cutoff_hours',
                'value' => '2',
                'type' => 'integer',
            ],
            [
                'group' => 'tax',
                'key' => 'payment_expired_minutes',
                'value' => '30',
                'type' => 'integer',
            ],
            [
                'group' => 'tax',
                'key' => 'auto_cancel_hours',
                'value' => '2',
                'type' => 'integer',
            ],
            [
                'group' => 'tax',
                'key' => 'max_photos_per_booking',
                'value' => '3',
                'type' => 'integer',
            ],
            [
                'group' => 'tax',
                'key' => 'min_rating_for_promo',
                'value' => '4.0',
                'type' => 'decimal',
            ],
            [
                'group' => 'tax',
                'key' => 'enable_rating',
                'value' => 'true',
                'type' => 'boolean',
            ],
            [
                'group' => 'tax',
                'key' => 'enable_chat',
                'value' => 'false',
                'type' => 'boolean',
            ],

            // Notification Settings
            [
                'group' => 'notification',
                'key' => 'email_booking_confirmation',
                'value' => 'true',
                'type' => 'boolean',
            ],
            [
                'group' => 'notification',
                'key' => 'email_payment_success',
                'value' => 'true',
                'type' => 'boolean',
            ],
            [
                'group' => 'notification',
                'key' => 'email_booking_reminder',
                'value' => 'true',
                'type' => 'boolean',
            ],
            [
                'group' => 'notification',
                'key' => 'email_booking_completed',
                'value' => 'true',
                'type' => 'boolean',
            ],
            [
                'group' => 'notification',
                'key' => 'email_booking_cancelled',
                'value' => 'true',
                'type' => 'boolean',
            ],
            [
                'group' => 'notification',
                'key' => 'push_booking_status',
                'value' => 'true',
                'type' => 'boolean',
            ],
            [
                'group' => 'notification',
                'key' => 'push_payment_reminder',
                'value' => 'true',
                'type' => 'boolean',
            ],
            [
                'group' => 'notification',
                'key' => 'push_review_reminder',
                'value' => 'true',
                'type' => 'boolean',
            ],
            [
                'group' => 'notification',
                'key' => 'sms_otp',
                'value' => 'false',
                'type' => 'boolean',
            ],
            [
                'group' => 'notification',
                'key' => 'sms_booking_reminder',
                'value' => 'false',
                'type' => 'boolean',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Settings seeded successfully!');
    }
}