<?php

namespace App\Enums;

enum NotificationType: string
{
    case EMAIL = 'email';
    case PUSH = 'push';
    case SMS = 'sms';
    case IN_APP = 'in_app';
}
