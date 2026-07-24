<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlotConfiguration extends Model
{
    use HasUuids;

    protected $fillable = [
        'branch_id',
        'date',
        'day_of_week',
        'slot_time',
        'max_capacity',
        'current_booked',
        'is_blocked',
        'block_reason',
        'notes',
        'type', // specific | recurring
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'max_capacity' => 'integer',
            'current_booked' => 'integer',
            'is_blocked' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function isRecurring(): bool
    {
        return $this->type === 'recurring';
    }

    public function isSpecific(): bool
    {
        return $this->type === 'specific';
    }
}
