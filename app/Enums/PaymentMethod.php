<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case QRIS = 'qris';
    case GOPAY = 'gopay';
    case OVO = 'ovo';
    case DANA = 'dana';
    case SHOPEEPAY = 'shopeepay';
    case VA_BCA = 'va_bca';
    case VA_BRI = 'va_bri';
    case VA_MANDIRI = 'va_mandiri';
    case VA_BNI = 'va_bni';
    case CREDIT_CARD = 'credit_card';
    case CASH = 'cash';
}
