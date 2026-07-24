<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'branch_id',
        'position',
        'phone',
        'hire_date',
        'salary_type',
        'base_salary',
        'commission_rate',
        'is_active',
        'avatar',
        'skills',
    ];

    protected function casts(): array
    {
        return [
            'position' => \App\Enums\EmployeePosition::class,
            'salary_type' => \App\Enums\SalaryType::class,
            'hire_date' => 'date',
            'base_salary' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'is_active' => 'boolean',
            'skills' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function assignedBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'assigned_employee_id');
    }
}
