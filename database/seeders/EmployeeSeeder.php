<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use App\Enums\EmployeePosition;
use App\Enums\SalaryType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = Branch::where('is_active', true)->get();
        
        $i = 1;
        foreach ($branches as $branch) {
            // Create users for employees
            $supervisorUser = User::create([
                'name' => "Supervisor {$branch->name}",
                'email' => "supervisor.{$branch->slug}@cucikuy.com",
                'phone' => '0812345679' . str_pad($i++, 3, '0', STR_PAD_LEFT),
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
            
            $supervisorUser->userRoles()->create([
                'role' => 'operator',
                'branch_id' => $branch->id,
            ]);

            $cashierUser = User::create([
                'name' => "Kasir {$branch->name}",
                'email' => "kasir.{$branch->slug}@cucikuy.com",
                'phone' => '0812345679' . str_pad($i++, 3, '0', STR_PAD_LEFT),
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
            
            $cashierUser->userRoles()->create([
                'role' => 'cashier',
                'branch_id' => $branch->id,
            ]);

            $washer1User = User::create([
                'name' => "Pencuci 1 {$branch->name}",
                'email' => "pencuci1.{$branch->slug}@cucikuy.com",
                'phone' => '0812345679' . str_pad($i++, 3, '0', STR_PAD_LEFT),
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
            
            $washer1User->userRoles()->create([
                'role' => 'operator',
                'branch_id' => $branch->id,
            ]);

            $washer2User = User::create([
                'name' => "Pencuci 2 {$branch->name}",
                'email' => "pencuci2.{$branch->slug}@cucikuy.com",
                'phone' => '0812345679' . str_pad($i++, 3, '0', STR_PAD_LEFT),
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
            
            $washer2User->userRoles()->create([
                'role' => 'operator',
                'branch_id' => $branch->id,
            ]);

            // Create employees
            Employee::create([
                'user_id' => $supervisorUser->id,
                'branch_id' => $branch->id,
                'position' => EmployeePosition::SUPERVISOR,
                'phone' => $supervisorUser->phone,
                'hire_date' => now()->subMonths(12),
                'salary_type' => SalaryType::MONTHLY,
                'base_salary' => 5000000,
                'commission_rate' => 5.00,
                'is_active' => true,
                'skills' => json_encode(['wax', 'coating', 'detailing', 'management']),
            ]);

            Employee::create([
                'user_id' => $cashierUser->id,
                'branch_id' => $branch->id,
                'position' => EmployeePosition::CASHIER,
                'phone' => $cashierUser->phone,
                'hire_date' => now()->subMonths(6),
                'salary_type' => SalaryType::MONTHLY,
                'base_salary' => 3500000,
                'commission_rate' => 2.00,
                'is_active' => true,
                'skills' => json_encode(['cashier', 'customer_service']),
            ]);

            Employee::create([
                'user_id' => $washer1User->id,
                'branch_id' => $branch->id,
                'position' => EmployeePosition::WASHER,
                'phone' => $washer1User->phone,
                'hire_date' => now()->subMonths(3),
                'salary_type' => SalaryType::PER_WASH,
                'base_salary' => 0,
                'commission_rate' => 15.00,
                'is_active' => true,
                'skills' => json_encode(['basic_wash', 'wax', 'interior']),
            ]);

            Employee::create([
                'user_id' => $washer2User->id,
                'branch_id' => $branch->id,
                'position' => EmployeePosition::WASHER,
                'phone' => $washer2User->phone,
                'hire_date' => now()->subMonths(1),
                'salary_type' => SalaryType::PER_WASH,
                'base_salary' => 0,
                'commission_rate' => 15.00,
                'is_active' => true,
                'skills' => json_encode(['basic_wash', 'motorcycle', 'tire_cleaning']),
            ]);
        }
    }
}
