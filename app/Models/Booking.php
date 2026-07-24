<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasUuids;

    protected $fillable = [
        'booking_code',
        'customer_id',
        'branch_id',
        'service_id',
        'assigned_employee_id',
        'vehicle_type',
        'vehicle_color',
        'vehicle_brand',
        'vehicle_plate',
        'vehicle_photos',
        'booking_date',
        'slot_time',
        'status',
        'payment_method',
        'payment_status',
        'payment_reference',
        'subtotal',
        'discount',
        'tax',
        'total',
        'notes',
        'is_walk_in',
        'rating',
        'review',
        'review_photos',
        'reviewed_at',
        'started_at',
        'finished_at',
        'duration_minutes',
        'cancelled_at',
        'cancel_reason',
        'cancelled_by',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'status' => BookingStatus::class,
            'payment_status' => PaymentStatus::class,
            'vehicle_photos' => 'array',
            'review_photos' => 'array',
            'is_walk_in' => 'boolean',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'rating' => 'integer',
            'reviewed_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'duration_minutes' => 'integer',
            'cancelled_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function bookingAddOns(): HasMany
    {
        return $this->hasMany(BookingAddOn::class);
    }

    public function bookingStatusLogs(): HasMany
    {
        return $this->hasMany(BookingStatusLog::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function startWash(): void
    {
        $this->status = \App\Enums\BookingStatus::WASHING;
        $this->started_at = now();
        $this->save();
    }

    public function completeWash(): void
    {
        $this->status = \App\Enums\BookingStatus::COMPLETED;
        $this->finished_at = now();
        $this->payment_status = \App\Enums\PaymentStatus::PAID;
        
        if ($this->started_at) {
            $this->duration_minutes = (int) $this->started_at->diffInMinutes($this->finished_at);
        } else {
            $this->duration_minutes = 30; // fallback default
        }

        $this->save();

        if ($this->customer) {
            $this->customer->increment('total_visits');
            $this->customer->increment('total_spent', $this->total);
            $this->customer->updateLoyaltyTier();
        }
    }

    public function cancel(string $reason, ?string $userId = null): void
    {
        $this->status = \App\Enums\BookingStatus::CANCELLED;
        $this->cancelled_at = now();
        $this->cancel_reason = $reason;
        $this->cancelled_by = $userId;
        $this->save();
    }

    public function reschedule(string $date, string $time): void
    {
        $this->booking_date = $date;
        $this->slot_time = $time;
        $this->status = \App\Enums\BookingStatus::CONFIRMED;
        $this->save();
    }
}
