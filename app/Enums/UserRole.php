<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN_BRANCH = 'admin_branch';
    case CASHIER = 'cashier';
    case OPERATOR = 'operator';
    case CUSTOMER = 'customer';
}
