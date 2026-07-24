<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Booking;

class Customer extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'total_visits',
        'total_spent',
        'loyalty_tier',
        'preferred_branch_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_visits' => 'integer',
            'total_spent' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function preferredBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'preferred_branch_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    protected function vehiclePlate(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->bookings()->latest('booking_date')->value('vehicle_plate'),
        );
    }

    protected function vehicleType(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->bookings()->latest('booking_date')->value('vehicle_type'),
        );
    }

    protected function vehicleColor(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->bookings()->latest('booking_date')->value('vehicle_color'),
        );
    }

    protected function vehicleBrand(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->bookings()->latest('booking_date')->value('vehicle_brand'),
        );
    }

    protected function rewardPoints(): Attribute
    {
        return Attribute::make(
            get: fn() => floor($this->total_spent / 10000), // 1 point per 10,000 IDR spent
        );
    }

    protected function avgDuration(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->bookings()
                ->where('status', 'completed')
                ->whereNotNull('duration_minutes')
                ->avg('duration_minutes'),
        );
    }

    protected function satisfaction(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->bookings()
                ->whereNotNull('rating')
                ->avg('rating'),
        );
    }

    public function updateLoyaltyTier(): void
    {
        $spent = $this->total_spent;
        if ($spent >= 1000000) {
            $this->loyalty_tier = \App\Enums\LoyaltyTier::PLATINUM;
        } elseif ($spent >= 500000) {
            $this->loyalty_tier = \App\Enums\LoyaltyTier::GOLD;
        } elseif ($spent >= 200000) {
            $this->loyalty_tier = \App\Enums\LoyaltyTier::SILVER;
        } else {
            $this->loyalty_tier = \App\Enums\LoyaltyTier::BRONZE;
        }
        $this->save();
    }
}
