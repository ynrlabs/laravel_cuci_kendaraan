<?php

namespace App\Enums;

enum ShiftStatus: string
{
    case SCHEDULED = 'scheduled';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case ABSENT = 'absent';
}
