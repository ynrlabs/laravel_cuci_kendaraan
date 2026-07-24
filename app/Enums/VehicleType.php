<?php

namespace App\Enums;

enum VehicleType: string
{
    case SEDAN = 'sedan';
    case SUV = 'suv';
    case MPV = 'mpv';
    case HATCHBACK = 'hatchback';
    case MOTOR = 'motor';
    case TRUCK = 'truck';
}
