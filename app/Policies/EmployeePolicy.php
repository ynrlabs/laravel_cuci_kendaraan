<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmployeePolicy
{
    /**
     * Determine whether the user can view any employees.
     */
    public function viewAny(User $user): bool
    {
        // Super admin can view all employees
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Admin branch can view employees in their branch
        if ($user->hasRole('admin_branch')) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can view the employee.
     */
    public function view(User $user, Employee $employee): bool
    {
        // Super admin can view any employee
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Admin branch can view employees in their branch
        if ($user->hasRole('admin_branch')) {
            $branchId = $user->getBranchId();
            return $employee->branch_id === $branchId;
        }
        
        // Employee can view their own profile
        if ($employee->user_id === $user->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create employees.
     */
    public function create(User $user): bool
    {
        // Super admin and admin_branch can create employees
        return $user->hasRole('super_admin') || $user->hasRole('admin_branch');
    }

    /**
     * Determine whether the user can update the employee.
     */
    public function update(User $user, Employee $employee): bool
    {
        // Super admin can update any employee
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Admin branch can update employees in their branch
        if ($user->hasRole('admin_branch')) {
            $branchId = $user->getBranchId();
            return $employee->branch_id === $branchId;
        }
        
        // Employee can update their own profile (limited fields)
        if ($employee->user_id === $user->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the employee.
     */
    public function delete(User $user, Employee $employee): bool
    {
        // Only super admin can delete employees
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Employee $employee): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Employee $employee): bool
    {
        return $user->hasRole('super_admin');
    }
}
