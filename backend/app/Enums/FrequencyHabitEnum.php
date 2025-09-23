<?php

namespace App\Enums;

enum FrequencyHabitEnum: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';

    public static function getLabels(): array
    {
        return [
            self::DAILY->value => 'Diária',
            self::WEEKLY->value => 'Semanal',
        ];
    }
}
