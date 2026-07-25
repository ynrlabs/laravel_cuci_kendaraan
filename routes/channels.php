<?php

use App\Models\Booking;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Private channel for individual booking status updates (customer & kasir)
Broadcast::channel('booking.{booking_id}', function (User $user, int $booking_id) {
    $booking = Booking::find($booking_id);
    
    if (!$booking) {
        return false;
    }
    
    // Customer can listen to their own booking
    if ($booking->user_id === $user->id) {
        return true;
    }
    
    // Kasir/Operator/Admin can listen if they belong to the branch
    if ($user->hasAnyRole(['kasir', 'operator', 'admin', 'owner']) && 
        $user->branch_id === $booking->branch_id) {
        return true;
    }
    
    // Owner can listen to all bookings in their branches
    if ($user->hasRole('owner') && $user->branches()->where('branch_id', $booking->branch_id)->exists()) {
        return true;
    }
    
    return false;
});

// Private channel for real-time queue updates (kasir/operator)
Broadcast::channel('branch.{branch_id}.queue', function (User $user, int $branch_id) {
    // Only kasir, operator, admin, owner of this branch can listen
    if ($user->hasAnyRole(['kasir', 'operator', 'admin']) && $user->branch_id === $branch_id) {
        return true;
    }
    
    // Owner can listen to all their branches
    if ($user->hasRole('owner') && $user->branches()->where('branch_id', $branch_id)->exists()) {
        return true;
    }
    
    return false;
});

// Private channel for real-time stats (admin/owner)
Broadcast::channel('branch.{branch_id}.stats', function (User $user, int $branch_id) {
    // Only admin, owner can listen to stats
    if ($user->hasAnyRole(['admin', 'owner']) && $user->branch_id === $branch_id) {
        return true;
    }
    
    // Owner can listen to all their branches
    if ($user->hasRole('owner') && $user->branches()->where('branch_id', $branch_id)->exists()) {
        return true;
    }
    
    return false;
});

// Private channel for user notifications
Broadcast::channel('App.Models.User.{id}', function (User $user, int $id) {
    return (int) $user->id === (int) $id;
});
