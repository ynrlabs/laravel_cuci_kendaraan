<?php

namespace Database\Seeders;

use App\Models\AddOn;
use Illuminate\Database\Seeder;

class AddOnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AddOn::create([
            'name' => 'Parfum Interior',
            'description' => 'Pembersih dan pengharum interior mobil',
            'price' => 25000,
            'duration_minutes' => 10,
            'is_active' => true,
        ]);

        AddOn::create([
            'name' => 'Pembersih Ban & Velg',
            'description' => 'Cuci bersih ban dan poles velg',
            'price' => 30000,
            'duration_minutes' => 15,
            'is_active' => true,
        ]);

        AddOn::create([
            'name' => 'Anti Jamur Kaca',
            'description' => 'Pelapis anti jamur untuk kaca depan & samping',
            'price' => 50000,
            'duration_minutes' => 20,
            'is_active' => true,
        ]);

        AddOn::create([
            'name' => 'Poles Dashboard',
            'description' => 'Poles dan perlindungan dashboard',
            'price' => 35000,
            'duration_minutes' => 10,
            'is_active' => true,
        ]);

        AddOn::create([
            'name' => 'Vacuum Deep Clean',
            'description' => 'Vacuum mendalam karpet & sit',
            'price' => 40000,
            'duration_minutes' => 20,
            'is_active' => true,
        ]);

        AddOn::create([
            'name' => 'Ozone Sterilisasi',
            'description' => 'Sterilisasi interior dengan ozone generator',
            'price' => 75000,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);
    }
}
