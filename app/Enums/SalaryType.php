<?php

namespace App\Enums;

enum SalaryType: string
{
    case MONTHLY = 'monthly';
    case PER_WASH = 'per_wash';
    case HOURLY = 'hourly';
}
