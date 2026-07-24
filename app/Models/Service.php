<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'base_duration_minutes',
        'is_active',
        'sort_order',
        'image',
    ];

    protected function casts(): array
    {
        return [
            'category' => \App\Enums\ServiceCategory::class,
            'base_duration_minutes' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function servicePricings(): HasMany
    {
        return $this->hasMany(ServicePricing::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
