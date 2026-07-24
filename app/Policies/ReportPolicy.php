<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReportPolicy
{
    /**
     * Determine whether the user can view financial reports.
     */
    public function viewFinancial(User $user): bool
    {
        // Super admin can view all financial reports
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Admin branch can view financial reports for their branch
        if ($user->hasRole('admin_branch')) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can view statistics reports.
     */
    public function viewStatistics(User $user): bool
    {
        // Super admin can view all statistics
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Admin branch can view statistics for their branch
        if ($user->hasRole('admin_branch')) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can export reports.
     */
    public function export(User $user): bool
    {
        // Super admin and admin_branch can export reports
        return $user->hasRole('super_admin') || $user->hasRole('admin_branch');
    }
}
