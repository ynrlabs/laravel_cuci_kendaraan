<?php

use App\Jobs\AutoCancelExpiredBooking;
use App\Jobs\ReconcilePaymentStatus;
use App\Jobs\SendBookingReminder;
use App\Jobs\SendReviewReminder;
use App\Models\Booking;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Commands
|--------------------------------------------------------------------------
|
| Here you may define all of your scheduled commands that will be executed
| by the Laravel scheduler. These are used for Phase 5 job queues.
|
*/

// Run every 5 minutes - Auto cancel expired bookings (unpaid > cutoff hours)
Schedule::call(function () {
    $cutoffHours = config('app.booking_cutoff_hours', 2);
    $expiredBookings = Booking::whereIn('status', ['pending', 'waiting_payment'])
        ->where('created_at', '<=', now()->subHours($cutoffHours))
        ->whereDoesntHave('payment', function ($query) {
            $query->where('status', 'paid');
        })
        ->get();
    
    foreach ($expiredBookings as $booking) {
        AutoCancelExpiredBooking::dispatch($booking);
    }
})->everyFiveMinutes()->name('auto-cancel-expired-bookings')->description('Auto cancel expired unpaid bookings every 5 minutes');

// Run daily at 9 AM - Send booking reminders for tomorrow's bookings
Schedule::call(function () {
    $tomorrow = now()->addDay()->format('Y-m-d');
    $bookings = Booking::where('status', 'confirmed')
        ->whereHas('slot', function ($query) use ($tomorrow) {
            $query->whereDate('date', $tomorrow);
        })
        ->get();
    
    foreach ($bookings as $booking) {
        SendBookingReminder::dispatch($booking);
    }
})->dailyAt('09:00')->timezone('Asia/Jakarta')->name('send-booking-reminders')->description('Send booking reminders for tomorrow at 9 AM daily');

// Run every hour - Reconcile payment status with Midtrans
Schedule::job(new ReconcilePaymentStatus())->hourly()->name('reconcile-payment-status')->description('Reconcile payment status with Midtrans every hour');

// Run 1 hour after booking completion - Send review reminder
// This is triggered via event listener when booking status changes to completed
// But we also have a fallback cron that runs every hour to catch any missed
Schedule::call(function () {
    $oneHourAgo = now()->subHour();
    $completedBookings = Booking::where('status', 'completed')
        ->where('completed_at', '>=', $oneHourAgo)
        ->where('completed_at', '<=', now())
        ->whereDoesntHave('review')
        ->get();
    
    foreach ($completedBookings as $booking) {
        SendReviewReminder::dispatch($booking);
    }
})->hourly()->name('send-review-reminders')->description('Send review reminders 1 hour after completion');

// Clean up old notifications (older than 90 days) - run weekly on Sunday at 2 AM
Schedule::call(function () {
    \App\Models\Notification::where('created_at', '<', now()->subDays(90))->delete();
})->weeklyOn(0, '02:00')->timezone('Asia/Jakarta')->name('cleanup-old-notifications')->description('Clean up notifications older than 90 days');

// Generate daily report for admins - run daily at 6 AM
Schedule::call(function () {
    // This would typically generate a report and email it to admins
    // Implementation would be in a dedicated job
    \Log::info('Daily report generation scheduled');
})->dailyAt('06:00')->timezone('Asia/Jakarta')->name('daily-report')->description('Generate daily admin report');