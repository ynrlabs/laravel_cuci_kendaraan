<?php

namespace App\Enums;

enum ServiceCategory: string
{
    case BASIC = 'basic';
    case PREMIUM = 'premium';
    case PREMIUM_PLUS = 'premium_plus';
    case ON_DEMAND = 'on_demand';
}
