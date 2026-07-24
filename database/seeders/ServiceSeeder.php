<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Enums\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Basic Services
        Service::create([
            'name' => 'Cuci Biasa',
            'slug' => 'cuci-biasa',
            'description' => 'Cuci eksterior + interior ringan (vacuum, wipe dashboard)',
            'category' => ServiceCategory::BASIC,
            'base_duration_minutes' => 45,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Service::create([
            'name' => 'Cuci Motor',
            'slug' => 'cuci-motor',
            'description' => 'Cuci motor lengkap (body, ban, mesin)',
            'category' => ServiceCategory::BASIC,
            'base_duration_minutes' => 30,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Premium Services
        Service::create([
            'name' => 'Wax',
            'slug' => 'wax',
            'description' => 'Cuci + poles wax untuk perlindungan cat dan kilau maksimal',
            'category' => ServiceCategory::PREMIUM,
            'base_duration_minutes' => 90,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        Service::create([
            'name' => 'Coating',
            'slug' => 'coating',
            'description' => 'Cuci + coating keramik/kuartz untuk perlindungan lama',
            'category' => ServiceCategory::PREMIUM,
            'base_duration_minutes' => 120,
            'is_active' => true,
            'sort_order' => 4,
        ]);

        // Premium Plus Services
        Service::create([
            'name' => 'Detailing Interior',
            'slug' => 'detailing-interior',
            'description' => 'Deep clean interior: seats, carpet, dashboard, AC vent, deodorizer',
            'category' => ServiceCategory::PREMIUM_PLUS,
            'base_duration_minutes' => 180,
            'is_active' => true,
            'sort_order' => 5,
        ]);

        Service::create([
            'name' => 'Detailing Full',
            'slug' => 'detailing-full',
            'description' => 'Full detailing: eksterior (wash, clay, polish, wax) + interior deep clean + engine bay',
            'category' => ServiceCategory::PREMIUM_PLUS,
            'base_duration_minutes' => 300,
            'is_active' => true,
            'sort_order' => 6,
        ]);

        // On-Demand Services
        Service::create([
            'name' => 'Home Service',
            'slug' => 'home-service',
            'description' => 'Layanan cuci di tempat (rumah/office) - area coverage tertentu',
            'category' => ServiceCategory::ON_DEMAND,
            'base_duration_minutes' => 60,
            'is_active' => true,
            'sort_order' => 7,
        ]);
    }
}
