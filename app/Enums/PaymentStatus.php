<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case UNPAID = 'unpaid';
    case PAID = 'paid';
    case REFUND_PENDING = 'refund_pending';
    case REFUNDED = 'refunded';
}
