<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use App\Models\Customer;
use App\Enums\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run BranchSeeder first so branches exist for admins/customer preferred branch
        $this->call(BranchSeeder::class);

        $branches = Branch::where('is_active', true)->get();

        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@cucikuy.com',
            'phone' => '081234567800',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        
        $superAdmin->userRoles()->create([
            'role' => UserRole::SUPER_ADMIN,
            'branch_id' => null,
        ]);

        // Create Admin Branch users
        $idx = 1;
        foreach ($branches as $branch) {
            $adminUser = User::create([
                'name' => "Admin {$branch->name}",
                'email' => "admin.{$branch->slug}@cucikuy.com",
                'phone' => '0812345678' . str_pad($idx++, 2, '0', STR_PAD_LEFT),
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
            
            $adminUser->userRoles()->create([
                'role' => UserRole::ADMIN_BRANCH,
                'branch_id' => $branch->id,
            ]);
        }

        // Create test customer
        $customerUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567899',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        
        $customerUser->userRoles()->create([
            'role' => UserRole::CUSTOMER,
            'branch_id' => null,
        ]);

        Customer::create([
            'user_id' => $customerUser->id,
            'total_visits' => 0,
            'total_spent' => 0,
            'loyalty_tier' => 'bronze',
            'preferred_branch_id' => $branches->first()->id ?? null,
        ]);

        // Run remaining seeders
        $this->call([
            RolePermissionSeeder::class,
            ServiceSeeder::class,
            ServicePricingSeeder::class,
            AddOnSeeder::class,
            EmployeeSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
