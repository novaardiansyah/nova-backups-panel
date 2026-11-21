<?php

namespace App\Enums;

enum BackupScheduleIntervalUnit: string
{
    case Minutes = 'minutes';
    case Hours = 'hours';
    case Days = 'days';
    case Weeks = 'weeks';
    case Months = 'months';

    public function label(): string
    {
        return match ($this) {
            self::Minutes => 'Minutes',
            self::Hours => 'Hours',
            self::Days => 'Days',
            self::Weeks => 'Weeks',
            self::Months => 'Months',
        };
    }
}
