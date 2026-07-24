<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookingPolicy
{
    /**
     * Determine whether the user can view any bookings.
     */
    public function viewAny(User $user): bool
    {
        // Super admin and admin_branch can view all bookings
        if ($user->hasRole('super_admin') || $user->hasRole('admin_branch')) {
            return true;
        }
        
        // Cashier and operator can view bookings in their branch
        if ($user->hasRole('cashier') || $user->hasRole('operator')) {
            return true;
        }
        
        // Customers can only view their own bookings
        return false;
    }

    /**
     * Determine whether the user can view the booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        // Super admin can view any booking
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Admin branch can view bookings in their branch
        if ($user->hasRole('admin_branch')) {
            $branchId = $user->getBranchId();
            return $booking->branch_id === $branchId;
        }
        
        // Cashier and operator can view bookings in their branch
        if ($user->hasRole('cashier') || $user->hasRole('operator')) {
            $branchId = $user->getBranchId();
            return $booking->branch_id === $branchId;
        }
        
        // Customer can view their own booking
        if ($booking->customer_id) {
            return $booking->customer->user_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create bookings.
     */
    public function create(User $user): bool
    {
        // Customers can create bookings via API
        // Cashier can create walk-in bookings
        return $user->hasRole('customer') || $user->hasRole('cashier') || $user->hasRole('admin_branch') || $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can update the booking.
     */
    public function update(User $user, Booking $booking): bool
    {
        // Super admin can update any booking
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Admin branch can update bookings in their branch
        if ($user->hasRole('admin_branch')) {
            $branchId = $user->getBranchId();
            return $booking->branch_id === $branchId;
        }
        
        // Cashier can update bookings in their branch (e.g., change status)
        if ($user->hasRole('cashier')) {
            $branchId = $user->getBranchId();
            return $booking->branch_id === $branchId;
        }
        
        return false;
    }

    /**
     * Determine whether the user can cancel the booking.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        // Super admin can cancel any booking
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Admin branch can cancel bookings in their branch
        if ($user->hasRole('admin_branch')) {
            $branchId = $user->getBranchId();
            return $booking->branch_id === $branchId;
        }
        
        // Cashier can cancel walk-in bookings in their branch
        if ($user->hasRole('cashier')) {
            $branchId = $user->getBranchId();
            return $booking->branch_id === $branchId && $booking->is_walk_in;
        }
        
        // Customer can cancel their own booking if not confirmed yet
        if ($booking->customer_id) {
            return $booking->customer->user_id === $user->id 
                && in_array($booking->status, ['pending_payment', 'confirmed']);
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Booking $booking): bool
    {
        // Only super admin can delete bookings
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Booking $booking): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Booking $booking): bool
    {
        return $user->hasRole('super_admin');
    }
}
