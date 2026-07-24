<?php

namespace App\Enums;

enum PaymentGateway: string
{
    case MIDTRANS = 'midtrans';
    case XENDIT = 'xendit';
    case MANUAL = 'manual';
}
