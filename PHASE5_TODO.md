# Phase 5 - Real-Time & Notifications Implementation Plan

## 5.1 Laravel Reverb (WebSocket)
- [x] Install + configure Laravel Reverb (already installed in composer.json)
- [x] Configure Reverb in .env and config/reverb.php (already configured)
- [x] Create private channel `booking.{booking_id}` — status update per booking
- [x] Create channel `branch.{branch_id}.queue` — real-time queue for kasir
- [x] Create channel `branch.{branch_id}.stats` — real-time stats for admin
- [x] Update routes/channels.php with all channel definitions
- [ ] Test WebSocket connection from React (kasir) and Flutter (customer)

## 5.2 Events & Listeners
- [x] Event: `BookingCreated` → listener: update slot count, notify kasir
- [x] Event: `BookingConfirmed` → listener: push notif customer, broadcast status
- [x] Event: `BookingStatusUpdated` → listener: push FCM notif, broadcast channel, log status
- [x] Event: `PaymentReceived` → listener: update booking, trigger BookingConfirmed
- [x] Event: `BookingCancelled` → listener: restore slot, push notif, refund if needed
- [x] Register all events/listeners in EventServiceProvider

## 5.3 Push Notification (FCM)
- [x] Install `laravel-notification-channels/fcm` package
- [x] Create `BookingStatusNotification` (via fcm, database, mail)
- [x] Create `PaymentSuccessNotification` (via fcm, database, mail)
- [x] Create `BookingStatusEmailNotification` (mail only)
- [x] Create `PaymentReceiptEmailNotification` (mail only)
- [x] Create `BookingReminderNotification` (via fcm, database, mail)
- [x] Create `ReviewReminderNotification` (via fcm, database, mail)
- [x] Create `RefundNotification` (via fcm, database, mail)
- [x] Queue all notifications via Laravel Horizon (all implement ShouldQueue)
- [ ] Test notifications on Android and iOS

## 5.4 Email Notification
- [x] Setup Mailgun / SMTP (configured in .env with mailhog for local)
- [x] Email template: Booking Confirmation (via BookingStatusNotification)
- [x] Email template: Payment Success (via PaymentSuccessNotification)
- [x] Email template: Booking Completed (via BookingStatusEmailNotification)
- [x] Email template: Cancellation (via BookingStatusEmailNotification)
- [x] Email template: Reminder H-1 (via BookingReminderNotification)
- [x] Email template: Invoice PDF (via PaymentReceiptEmailNotification)
- [x] Email template: Review Reminder (via ReviewReminderNotification)
- [x] Email template: Refund Confirmation (via RefundNotification)

## 5.5 Job Queue
- [x] Job: `SendBookingReminder` — cron H-1 before slot (scheduled daily at 9 AM)
- [x] Job: `AutoCancelExpiredBooking` — cron every 5 min, cancel booking unpaid > cutoff
- [x] Job: `SendReviewReminder` — trigger 1 hour after completed (scheduled hourly + event-driven)
- [x] Job: `ReconcilePaymentStatus` — cron every hour, sync status from Midtrans
- [x] Job: `ProcessRefund` — handle refund processing for cancelled paid bookings
- [x] Schedule all jobs in routes/console.php
- [ ] Monitor queue via Laravel Horizon dashboard