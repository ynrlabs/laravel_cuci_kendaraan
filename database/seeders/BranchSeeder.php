<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::create([
            'name' => 'CuciKuy Pusat',
            'slug' => 'cucikuy-pusat',
            'address' => 'Jl. Sudirman No. 123',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
            'postal_code' => '10220',
            'phone' => '021-1234567',
            'email' => 'pusat@cucikuy.com',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'opening_hours' => json_encode([
                'monday' => ['open' => '08:00', 'close' => '20:00', 'closed' => false],
                'tuesday' => ['open' => '08:00', 'close' => '20:00', 'closed' => false],
                'wednesday' => ['open' => '08:00', 'close' => '20:00', 'closed' => false],
                'thursday' => ['open' => '08:00', 'close' => '20:00', 'closed' => false],
                'friday' => ['open' => '08:00', 'close' => '20:00', 'closed' => false],
                'saturday' => ['open' => '08:00', 'close' => '18:00', 'closed' => false],
                'sunday' => ['open' => '09:00', 'close' => '17:00', 'closed' => true],
            ]),
            'is_active' => true,
            'operational_notes' => 'Cabang utama di pusat kota',
        ]);

        Branch::create([
            'name' => 'CuciKuy Kebayoran',
            'slug' => 'cucikuy-kebayoran',
            'address' => 'Jl. Kebayoran Lama No. 45',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12210',
            'phone' => '021-7654321',
            'email' => 'kebayoran@cucikuy.com',
            'latitude' => -6.2345,
            'longitude' => 106.7890,
            'opening_hours' => json_encode([
                'monday' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
                'tuesday' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
                'wednesday' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
                'thursday' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
                'friday' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
                'saturday' => ['open' => '08:00', 'close' => '17:00', 'closed' => false],
                'sunday' => ['open' => '09:00', 'close' => '16:00', 'closed' => true],
            ]),
            'is_active' => true,
            'operational_notes' => 'Cabang di area Kebayoran',
        ]);

        Branch::create([
            'name' => 'CuciKuy BSD',
            'slug' => 'cucikuy-bsd',
            'address' => 'Jl. BSD Boulevard No. 88',
            'city' => 'Tangerang',
            'province' => 'Banten',
            'postal_code' => '15322',
            'phone' => '021-9876543',
            'email' => 'bsd@cucikuy.com',
            'latitude' => -6.3012,
            'longitude' => 106.6523,
            'opening_hours' => json_encode([
                'monday' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
                'tuesday' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
                'wednesday' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
                'thursday' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
                'friday' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
                'saturday' => ['open' => '08:00', 'close' => '17:00', 'closed' => false],
                'sunday' => ['open' => '09:00', 'close' => '16:00', 'closed' => true],
            ]),
            'is_active' => true,
            'operational_notes' => 'Cabang di area BSD City',
        ]);
    }
}
