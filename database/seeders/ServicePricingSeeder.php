<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Branch;
use App\Models\ServicePricing;
use App\Enums\VehicleType;
use Illuminate\Database\Seeder;

class ServicePricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = Service::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $vehicleTypes = VehicleType::cases();

        foreach ($branches as $branch) {
            foreach ($services as $service) {
                foreach ($vehicleTypes as $vehicleType) {
                    // Base price varies by service category and vehicle type
                    $basePrice = match($service->category->value) {
                        'basic' => match($vehicleType->value) {
                            'motor' => 35000,
                            'sedan' => 50000,
                            'hatchback' => 55000,
                            'suv' => 70000,
                            'mpv' => 75000,
                            'truck' => 100000,
                        },
                        'premium' => match($vehicleType->value) {
                            'motor' => 80000,
                            'sedan' => 150000,
                            'hatchback' => 165000,
                            'suv' => 200000,
                            'mpv' => 220000,
                            'truck' => 300000,
                        },
                        'premium_plus' => match($vehicleType->value) {
                            'motor' => 150000,
                            'sedan' => 300000,
                            'hatchback' => 330000,
                            'suv' => 400000,
                            'mpv' => 440000,
                            'truck' => 600000,
                        },
                        'on_demand' => match($vehicleType->value) {
                            'motor' => 60000,
                            'sedan' => 100000,
                            'hatchback' => 110000,
                            'suv' => 140000,
                            'mpv' => 150000,
                            'truck' => 200000,
                        },
                    };

                    // Add branch-specific variation (+/- 10%)
                    $variation = rand(-10, 10) / 100;
                    $price = round($basePrice * (1 + $variation), -3);

                    ServicePricing::create([
                        'service_id' => $service->id,
                        'branch_id' => $branch->id,
                        'vehicle_type' => $vehicleType,
                        'price' => $price,
                        'duration_minutes' => $service->base_duration_minutes,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
